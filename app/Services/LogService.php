<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;

class LogService
{
    public function __construct(
        protected ActivityLog $activityLog
    ) {}

    public function logAuth(string $action, string $description, ?User $user = null, array $properties = []): void
    {
        try {
            $this->activityLog->logAuth($action, array_merge($properties, [
                'description' => $description,
                'email' => $user?->email ?? ($properties['email'] ?? null),
            ]));

            LaravelLog::info("[AUTH] {$action}: {$description}", [
                'user_id' => $user?->id,
                'email' => $user?->email,
                'ip' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to log auth activity: '.$e->getMessage());
        }
    }

    public function log(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        array $properties = []
    ): void {
        try {
            $this->activityLog->log($action, array_merge($properties, [
                'description' => $description,
                'model_type' => $modelType,
                'model_id' => $modelId,
            ]));

            LaravelLog::info("[ACTIVITY] {$action}: {$description}", [
                'user_id' => Auth::id(),
                'model_type' => $modelType,
                'model_id' => $modelId,
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to log activity: '.$e->getMessage());
        }
    }

    public function logSecurity(string $event, string $description, array $properties = []): void
    {
        try {
            $this->activityLog->log('security.'.$event, array_merge($properties, [
                'category' => 'security',
                'description' => $description,
            ]), false);

            LaravelLog::warning("[SECURITY] {$event}: {$description}", [
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
                'properties' => $properties,
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to log security event: '.$e->getMessage());
        }
    }

    public function logError(\Exception $exception, string $context = ''): void
    {
        try {
            $this->activityLog->log('error', [
                'category' => 'error',
                'description' => $context.': '.$exception->getMessage(),
                'exception_class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ], false);

            LaravelLog::error($context.': '.$exception->getMessage(), [
                'exception' => $exception,
                'user_id' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            LaravelLog::critical('Failed to log error: '.$e->getMessage());
        }
    }
}
