<?php

namespace App\Observers;

use App\Models\ContactBookEntry;
use App\Services\ContactBookService;
use Illuminate\Support\Facades\Cache;

class ContactBookEntryObserver
{
    public function created(ContactBookEntry $entry): void
    {
        $this->bustCache($entry);
    }

    public function updated(ContactBookEntry $entry): void
    {
        $this->bustCache($entry);
    }

    public function deleted(ContactBookEntry $entry): void
    {
        $this->bustCache($entry);
    }

    protected function bustCache(ContactBookEntry $entry): void
    {
        // Use the model's company_id (not the request context) so console
        // and cross-tenant operations invalidate the right cache.
        Cache::forget(ContactBookService::cacheKey($entry->company_id));
    }
}
