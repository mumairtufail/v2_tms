<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\PermissionService;

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
     * Handle unauthorized access - No return type declaration
     */
    protected function handleUnauthorized(Request $request, string $module, string $action)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Insufficient permissions',
                'message' => "You don't have permission to {$action} {$module}",
                'required_permission' => [
                    'module' => $module,
                    'action' => $action
                ]
            ], 403);
        }

        // For web requests, redirect with error
        return redirect()->back()->with('error', "You don't have permission to access this resource.");
    }
}