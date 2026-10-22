<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    |
    | Turning this off stops every writer in the audit pipeline. Intended for
    | test suites and one-off maintenance scripts, not for production.
    |
    */

    'enabled' => env('AUDIT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Trusted proxies
    |--------------------------------------------------------------------------
    |
    | Consumed by App\Http\Middleware\TrustProxies. Until this is set, Laravel
    | ignores X-Forwarded-For and every log row behind a load balancer, Nginx
    | or Cloudflare records the proxy address rather than the client's.
    |
    | Set it to the comma separated addresses or CIDR ranges of the proxies in
    | front of this app. '*' trusts whatever is calling, which is only safe when
    | the app cannot be reached except through that proxy.
    |
    */

    'trusted_proxies' => env('TRUSTED_PROXIES'),

    /*
    |--------------------------------------------------------------------------
    | Read tracking
    |--------------------------------------------------------------------------
    |
    | When enabled, GET/HEAD requests are recorded too, so the trail answers
    | "who looked at this customer" and not just "who changed it". This roughly
    | doubles write volume, so the noisy routes below are skipped regardless.
    |
    */

    'log_reads' => env('AUDIT_LOG_READS', true),

    /*
    |--------------------------------------------------------------------------
    | Route exclusions
    |--------------------------------------------------------------------------
    |
    | Path patterns (fnmatch syntax, no leading slash) that never produce a log
    | row. Keep this to genuinely high-frequency, low-value traffic: polling,
    | typeahead, asset and health endpoints.
    |
    */

    'excluded_paths' => [
        'livewire/*',
        'build/*',
        'storage/*',
        'up',
        'favicon.ico',
        'api/google/places/*',
        '*/orders/search-customers',
        'api/customers/search',
        'broadcasting/auth',
    ],

    /*
    | Paths excluded from READ logging only. These are still logged when they
    | are written to. Use for pages that are hit constantly but carry no
    | sensitive payload.
    */

    'excluded_read_paths' => [
        '*/notifications',
        '*/dashboard',
        // Reading the trail must not write to it. Every visit, summary tile
        // click, filter and detail drawer used to add a "Viewed the activity
        // logs list" row, so the counts on the page climbed while someone was
        // only looking at them. Covers the company view, a single entry, the
        // order activity panel (staff and portal) and the super admin view.
        '*/activity-logs',
        '*/activity-logs/*',
        'admin/logs',
        'admin/logs/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redaction
    |--------------------------------------------------------------------------
    |
    | Keys scrubbed from any payload before it is written, at any nesting
    | depth. Matched case-insensitively against the key name.
    |
    */

    'redact' => [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'remember_token',
        'api_key',
        'api_secret',
        'secret',
        'token',
        'access_token',
        'refresh_token',
        '_token',
        'authorization',
        'credit_card',
        'card_number',
        'cvv',
        'ssn',
        'sin',
        'smtp_password',
        'mail_password',
    ],

    /*
    | Request keys dropped entirely (not redacted - removed) because they are
    | large structural payloads that belong in the record, not the audit row.
    */

    'strip_input' => [
        '_method',
        'stops',
        'quote_data',
        'contact_book_entries',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payload limits
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Write behaviour
    |--------------------------------------------------------------------------
    |
    | Entries are buffered and written in one statement from a terminating
    | callback, after the response has gone to the client. Turning deferral off
    | writes each row inline, which is slower but useful when debugging.
    |
    | 'connection' moves the table to another database connection entirely,
    | which is how this scales past a single server's write capacity.
    |
    */

    'deferred_writes' => env('AUDIT_DEFERRED_WRITES', true),
    'buffer_limit' => env('AUDIT_BUFFER_LIMIT', 100),
    'connection' => env('AUDIT_DB_CONNECTION'),

    /*
    | Stack frames kept on a technical entry. Full traces are the single
    | largest thing stored, so they are capped rather than kept whole.
    */

    'trace_frames' => env('AUDIT_TRACE_FRAMES', 20),

    'max_string_length' => 500,
    'max_array_depth' => 6,

    /*
    |--------------------------------------------------------------------------
    | Model change tracking
    |--------------------------------------------------------------------------
    |
    | Attributes never captured in a before/after diff, on top of 'redact'.
    |
    */

    'ignore_attributes' => [
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Days to keep rows before `audit:prune` removes them. Set to 0 to keep
    | logs forever. Successful read entries expire on their own shorter clock
    | because they are the bulk of the volume and the least evidential.
    |
    */

    'retention_days' => env('AUDIT_RETENTION_DAYS', 365),
    'read_retention_days' => env('AUDIT_READ_RETENTION_DAYS', 30),

    /*
    | Stack traces and headers are cleared from entries older than this, while
    | the entry itself survives to its full retention. They are the bulk of the
    | stored bytes and nobody debugs a request from two months ago.
    */

    'technical_retention_days' => env('AUDIT_TECHNICAL_RETENTION_DAYS', 30),

];
