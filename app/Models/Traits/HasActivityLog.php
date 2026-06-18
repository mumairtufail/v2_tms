<?php

namespace App\Models\Traits;

use App\Services\ActivityLog;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

trait HasActivityLog
{
    protected static function bootHasActivityLog(): void
    {
        static::created(function ($model) {
            $model->recordModelActivity('created');
        });

        static::updated(function ($model) {
            if ($model->wasChanged() && ! $model->wasRecentlyCreated) {
                $model->recordModelActivity('updated');
            }
        });

        static::deleted(function ($model) {
            $model->recordModelActivity('deleted');
        });
    }

    public function recordModelActivity(string $action): void
    {
        if (! Auth::user('web') && ! Auth::user('customer')) {
            return;
        }

        $data = [
            'description' => match ($action) {
                'created' => "Created {$this->getActivityEntityName()}: {$this->getActivityDescription()}",
                'updated' => "Updated {$this->getActivityEntityName()}: {$this->getActivityChanges()}",
                'deleted' => "Deleted {$this->getActivityEntityName()}: {$this->getActivityDescription()}",
                default => ucfirst($action).' '.$this->getActivityEntityName(),
            },
        ];

        if ($action === 'updated') {
            $data['changes'] = collect($this->getChanges())
                ->except(['updated_at', 'created_at'])
                ->all();
        }

        app(ActivityLog::class)->logModel($action, $this, $data);
    }

    public function subjectActivityLogs(): HasMany
    {
        return $this->hasMany(\App\Models\ActivityLogs::class, 'company_id', 'company_id')
            ->where('data->model_type', static::class)
            ->where('data->model_id', $this->getKey());
    }

    protected function getActivityEntityName(): string
    {
        return class_basename($this);
    }

    protected function getActivityDescription(): string
    {
        return $this->name ?? $this->title ?? "#{$this->id}";
    }

    protected function getActivityChanges(): string
    {
        $changes = [];

        foreach ($this->getChanges() as $key => $value) {
            if (in_array($key, ['updated_at', 'created_at'], true)) {
                continue;
            }

            $changes[] = "{$key}: {$this->getOriginal($key)} → {$value}";
        }

        return implode(', ', $changes) ?: $this->getActivityDescription();
    }
}
