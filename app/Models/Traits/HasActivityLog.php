<?php

namespace App\Models\Traits;

use App\Services\ActivityLog;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Records create/update/delete on a model together with the before and after
 * values, so "Updated the order" can be answered with what actually changed.
 *
 * A model can tune the output with:
 *   protected string $activityLabel     - human name for the record type
 *   protected string $activityLabelKey  - attribute used as the record's name
 *   protected array  $activityIgnore    - attributes excluded from the diff
 */
trait HasActivityLog
{
    protected static function bootHasActivityLog(): void
    {
        static::created(function ($model) {
            $model->recordModelActivity('created');
        });

        static::updated(function ($model) {
            // No wasRecentlyCreated guard: Eloquent never fires `updated` for
            // an insert, so the guard only suppressed genuine updates made to
            // a record in the same request that created it.
            if ($model->wasChanged()) {
                $model->recordModelActivity('updated');
            }
        });

        static::deleted(function ($model) {
            $model->recordModelActivity('deleted');
        });
    }

    public function recordModelActivity(string $action): void
    {
        if (! config('audit.enabled', true)) {
            return;
        }

        // Auth::user() takes no guard argument - the previous Auth::user('web')
        // silently checked the same guard twice, so portal-only sessions never
        // recorded a single model change.
        if (! Auth::guard('web')->check() && ! Auth::guard('customer')->check()) {
            return;
        }

        // Nothing left after ignored attributes are removed means nothing a
        // reviewer would care about changed - a login touching last_login_at,
        // for instance. Writing the row anyway just buries the real entries.
        if ($action === 'updated' && $this->getActivityChanges() === []) {
            return;
        }

        $data = [
            'description' => match ($action) {
                'created' => "Created {$this->getActivityEntityName()}: {$this->getActivityDescription()}",
                'updated' => "Updated {$this->getActivityEntityName()}: {$this->getActivityChangeSummary()}",
                'deleted' => "Deleted {$this->getActivityEntityName()}: {$this->getActivityDescription()}",
                default => ucfirst($action).' '.$this->getActivityEntityName(),
            },
        ];

        if ($action === 'updated') {
            // Both sides of every change, which is the part that makes the
            // entry evidence rather than a notification.
            $data['changes'] = $this->getActivityChanges();
        }

        if ($action === 'created') {
            $data['attributes'] = $this->filterActivityAttributes($this->getAttributes());
        }

        if ($action === 'deleted') {
            $data['attributes'] = $this->filterActivityAttributes($this->getOriginal());
        }

        app(ActivityLog::class)->logModel($action, $this, $data);
    }

    public function subjectActivityLogs(): HasMany
    {
        return $this->hasMany(\App\Models\ActivityLogs::class, 'subject_id', $this->getKeyName())
            ->where('subject_type', static::class);
    }

    /**
     * Before and after for every changed attribute, secrets removed.
     *
     * @return array<string, array{from: mixed, to: mixed}>
     */
    protected function getActivityChanges(): array
    {
        $redact = array_map('strtolower', config('audit.redact', []));
        $ignore = $this->activityIgnoredAttributes();
        $changes = [];

        foreach ($this->getChanges() as $key => $value) {
            if (in_array($key, $ignore, true)) {
                continue;
            }

            // Redact both sides by key. Letting the sanitizer match the key
            // would replace the whole {from, to} pair with a string and lose
            // the fact that the attribute changed at all.
            if (in_array(strtolower((string) $key), $redact, true)) {
                $changes[$key] = ['from' => '[redacted]', 'to' => '[redacted]'];

                continue;
            }

            $changes[$key] = app(ActivityLog::class)->sanitize([
                'from' => $this->getOriginal($key),
                'to' => $value,
            ]);
        }

        return $changes;
    }

    /**
     * Drop timestamps, internal columns and anything the model opts out of.
     */
    protected function filterActivityAttributes(array $attributes): array
    {
        return app(ActivityLog::class)->sanitize(
            collect($attributes)->except($this->activityIgnoredAttributes())->all()
        );
    }

    /**
     * @return array<int, string>
     */
    protected function activityIgnoredAttributes(): array
    {
        return array_merge(
            config('audit.ignore_attributes', []),
            property_exists($this, 'activityIgnore') ? $this->activityIgnore : [],
        );
    }

    protected function getActivityEntityName(): string
    {
        if (property_exists($this, 'activityLabel')) {
            return $this->activityLabel;
        }

        return str(class_basename($this))->snake(' ')->lower()->toString();
    }

    /**
     * A name a person would recognise: the model's own label attribute if it
     * declares one, otherwise the usual candidates, otherwise the key.
     */
    protected function getActivityDescription(): string
    {
        if (property_exists($this, 'activityLabelKey') && ! empty($this->{$this->activityLabelKey})) {
            return (string) $this->{$this->activityLabelKey};
        }

        foreach (['name', 'title', 'order_number', 'carrier_name', 'manifest_number', 'reference'] as $key) {
            if (! empty($this->{$key})) {
                return (string) $this->{$key};
            }
        }

        return '#'.$this->getKey();
    }

    protected function getActivityChangeSummary(): string
    {
        $parts = [];

        foreach ($this->getActivityChanges() as $key => $change) {
            if (! is_array($change)) {
                $parts[] = $key.' changed';

                continue;
            }

            $parts[] = sprintf(
                '%s %s → %s',
                $key,
                $this->formatActivityValue($change['from'] ?? null),
                $this->formatActivityValue($change['to'] ?? null)
            );
        }

        if ($parts === []) {
            return $this->getActivityDescription();
        }

        return $this->getActivityDescription().' ('.implode(', ', $parts).')';
    }

    protected function formatActivityValue(mixed $value): string
    {
        return match (true) {
            $value === null => 'empty',
            is_bool($value) => $value ? 'yes' : 'no',
            is_array($value) => '['.count($value).' items]',
            default => str((string) $value)->limit(60)->toString(),
        };
    }
}
