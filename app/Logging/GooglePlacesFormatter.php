<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class GooglePlacesFormatter
{
    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new class extends LineFormatter {
                public function format(LogRecord $record): string
                {
                    $ts    = $record->datetime->format('Y-m-d H:i:s');
                    $level = str_pad($record->level->name, 7);
                    $event = $record->message;
                    $ctx   = $record->context;

                    $lines = ["[{$ts}] {$level} | {$event}"];

                    foreach ($ctx as $key => $value) {
                        $formatted = is_array($value) || is_object($value)
                            ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                            : (string) $value;

                        // Indent multi-line values
                        $indented = implode("\n             ", explode("\n", $formatted));
                        $lines[] = sprintf('  %-16s %s', $key . ':', $indented);
                    }

                    return implode("\n", $lines) . "\n" . str_repeat('-', 80) . "\n";
                }
            });
        }
    }
}
