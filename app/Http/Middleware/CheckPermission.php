<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\PermissionService;
use App\Support\Toast;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Usage: Route::middleware('permission:users,create')->post('/users', [UserController::class, 'store']);
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'view')
    {
        $permissionService = new PermissionService();
        
        if (!$permissionService->can($module, $action)) {
            return $this->handleUnauthorized($request, $module, $action);
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access - Avoid redirect loops
     */
    protected function handleUnauthorized(Request $request, string $module, string $action)
    {
        $message = "You don't have permission to {$action} {$module}.";
        
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Insufficient permissions',
                'message' => $message,
                'required_permission' => [
                    'module' => $module,
                    'action' => $action
                ]
            ], 403);
        }

        // For web requests, redirect to a safe location to avoid loops
        $user = $request->user();
        
        if ($user && $user->company) {
            // Try to redirect to dashboard if user has dashboard permission
            if ($user->hasPermission('dashboard', 'view')) {
                Toast::error($message);
                return redirect()->route('v2.dashboard', ['company' => $user->company->slug]);
            }
            
            // If no dashboard access, show profile page
            Toast::error($message);
            return redirect()->route('v2.profile.edit', ['company' => $user->company->slug]);
        }
        
        // Fallback: abort with 403
        abort(403, $message);
    }
}