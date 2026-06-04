<?php

namespace App\Services;

use App\Models\ActivityLogs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;

class LogService
{
    /**
     * Log user authentication activity
     */
    public function logAuth(string $action, string $description, ?User $user = null, array $properties = []): void
    {
        try {
            $user = $user ?? Auth::user();
            
            ActivityLogs::create([
                'user_id' => $user?->id,
                'company_id' => $user?->company_id ?? config('app.current_company_id'),
                'model_type' => User::class,
                'model_id' => $user?->id,
                'action' => $action,
                'description' => $description,
                'properties' => array_merge($properties, [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toDateTimeString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Also log to Laravel log
            LaravelLog::info("[AUTH] {$action}: {$description}", [
                'user_id' => $user?->id,
                'email' => $user?->email,
                'ip' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to log auth activity: ' . $e->getMessage());
        }
    }

    /**
     * Log general activity
     */
    public function log(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        array $properties = []
    ): void {
        try {
            $user = Auth::user();
            
            ActivityLogs::create([
                'user_id' => $user?->id,
                'company_id' => $user?->company_id ?? config('app.current_company_id'),
                'model_type' => $modelType,
                'model_id' => $modelId,
                'action' => $action,
                'description' => $description,
                'properties' => array_merge($properties, [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toDateTimeString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Also log to Laravel log
            LaravelLog::info("[ACTIVITY] {$action}: {$description}", [
                'user_id' => $user?->id,
                'model_type' => $modelType,
                'model_id' => $modelId,
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Log security events
     */
    public function logSecurity(string $event, string $description, array $properties = []): void
    {
        try {
            $user = Auth::user();
            
            ActivityLogs::create([
                'user_id' => $user?->id,
                'company_id' => $user?->company_id ?? config('app.current_company_id'),
                'model_type' => null,
                'model_id' => null,
                'action' => 'security.' . $event,
                'description' => $description,
                'properties' => array_merge($properties, [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toDateTimeString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Also log to Laravel log with WARNING level
            LaravelLog::warning("[SECURITY] {$event}: {$description}", [
                'user_id' => $user?->id,
                'ip' => request()->ip(),
                'properties' => $properties,
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to log security event: ' . $e->getMessage());
        }
    }

    /**
     * Log error/exception
     */
    public function logError(\Exception $exception, string $context = ''): void
    {
        try {
            $user = Auth::user();
            
            ActivityLogs::create([
                'user_id' => $user?->id,
                'company_id' => $user?->company_id ?? config('app.current_company_id'),
                'model_type' => null,
                'model_id' => null,
                'action' => 'error',
                'description' => $context . ': ' . $exception->getMessage(),
                'properties' => [
                    'exception_class' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                    'ip' => request()->ip(),
                    'url' => request()->fullUrl(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Also log to Laravel log
            LaravelLog::error($context . ': ' . $exception->getMessage(), [
                'exception' => $exception,
                'user_id' => $user?->id,
            ]);
        } catch (\Exception $e) {
            LaravelLog::critical('Failed to log error: ' . $e->getMessage());
        }
    }
}
