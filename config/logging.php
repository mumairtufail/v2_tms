<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        '2fa' => [
            'driver' => 'single',
            'path'   => storage_path('logs/2fa.log'),
            'level'  => 'debug',
            'replace_placeholders' => true,
        ],

        'orderedit-location' => [
            'driver' => 'single',
            'path' => storage_path('logs/orderedit-location.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'plugins' => [
            'driver' => 'single',
            'path' => storage_path('logs/plugins.log'),
            'level' => 'debug',
        ],

        'orders' => [
            'driver' => 'daily',
            'path' => storage_path('logs/orders.log'),
            'level' => 'debug',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'google_places' => [
            'driver' => 'daily',
            'path' => storage_path('logs/google-places.log'),
            'level' => 'debug',
            'days' => 14,
            'tap' => [\App\Logging\GooglePlacesFormatter::class],
        ],

        /*
        |----------------------------------------------------------------------
        | Per-module channels
        |----------------------------------------------------------------------
        | One file per area so a problem can be traced without reading through
        | everything else. Each is daily-rotated with its own retention.
        */

        // Every outgoing email: which SMTP account sent it, to whom, and why it failed
        'mail' => [
            'driver' => 'daily',
            'path' => storage_path('logs/mail.log'),
            'level' => 'debug',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        // In-app and email notifications: who was notified, who was skipped and why
        'notifications' => [
            'driver' => 'daily',
            'path' => storage_path('logs/notifications.log'),
            'level' => 'debug',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        // Customers module: people, portal access, commodities, accessorials, credit
        'customers' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customers.log'),
            'level' => 'debug',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        // Manifests: cost estimates, resource assignment, status changes
        'manifests' => [
            'driver' => 'daily',
            'path' => storage_path('logs/manifests.log'),
            'level' => 'debug',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        // Customer portal: sign-in, order submission, credit limit blocks
        'portal' => [
            'driver' => 'daily',
            'path' => storage_path('logs/portal.log'),
            'level' => 'debug',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        // QuickBooks sync: customers, invoices and API errors
        'quickbooks' => [
            'driver' => 'daily',
            'path' => storage_path('logs/quickbooks.log'),
            'level' => 'debug',
            'days' => 30,
            'replace_placeholders' => true,
        ],
    ],

];
