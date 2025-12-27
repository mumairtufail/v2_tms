<?php

namespace App\Models\Traits;

use App\Models\ActivityLogs;
use Illuminate\Support\Facades\Auth;

trait HasActivityLog
{
    /**
     * Boot the trait.
     */
    protected static function bootHasActivityLog(): void
    {
        // Log creation
        static::created(function ($model) {
            $model->logActivity('created', "Created {$model->getActivityEntityName()}: {$model->getActivityDescription()}");
        });

        // Log updates
        static::updated(function ($model) {
            if ($model->wasChanged() && !$model->wasRecentlyCreated) {
                $changes = $model->getActivityChanges();
                $model->logActivity('updated', "Updated {$model->getActivityEntityName()}: {$changes}");
            }
        });

        // Log deletion
        static::deleted(function ($model) {
            $model->logActivity('deleted', "Deleted {$model->getActivityEntityName()}: {$model->getActivityDescription()}");
        });
    }

    /**
     * Log an activity.
     */
    public function logActivity(string $action, string $description, array $properties = []): void
    {
        ActivityLogs::create([
            'user_id' => Auth::id(),
            'company_id' => $this->company_id ?? config('app.current_company_id'),
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get activity logs for this model.
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLogs::class, 'model');
    }

    /**
     * Get the entity name for activity logging.
     */
    protected function getActivityEntityName(): string
    {
        return class_basename($this);
    }

    /**
     * Get the description for activity logging.
     */
    protected function getActivityDescription(): string
    {
        return $this->name ?? $this->title ?? "#{$this->id}";
    }

    /**
     * Get changes for activity logging.
     */
    protected function getActivityChanges(): string
    {
        $changes = [];
        foreach ($this->getChanges() as $key => $value) {
            if (in_array($key, ['updated_at', 'created_at'])) {
                continue;
            }
            $changes[] = "{$key}: {$this->getOriginal($key)} → {$value}";
        }
        return implode(', ', $changes);
    }
}
