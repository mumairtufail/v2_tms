<?php

namespace App\Exceptions;

use App\Services\ActivityLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Exceptions that are ordinary control flow rather than something a
     * reviewer needs to see. The request middleware already records the
     * resulting non-2xx response for these.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontAudit = [
        ValidationException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Audit first, then report as normal.
     *
     * This overrides report() rather than registering a reportable callback:
     * Laravel's internalDontReport list - authentication, authorization, CSRF
     * and HTTP exceptions - returns from report() before any callback runs, and
     * those are precisely the failures worth auditing.
     */
    public function report(Throwable $e): void
    {
        $this->auditException($e);

        parent::report($e);
    }

    /**
     * Put the failure in the audit trail alongside the successes, so "what
     * happened at 14:09" has one answer rather than two half-answers split
     * between the activity log and the Laravel log file.
     */
    protected function auditException(Throwable $e): void
    {
        try {
            if (! config('audit.enabled', true)) {
                return;
            }

            // Console failures have no actor and belong in the Laravel log.
            // Tests run in console but must still exercise this path.
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            foreach ($this->dontAudit as $ignored) {
                if ($e instanceof $ignored) {
                    return;
                }
            }

            [$action, $description, $severity, $technicalSummary] = $this->classify($e);

            app(ActivityLog::class)->logError($action, $description, [
                'severity' => $severity,
                // What a super admin reads instead of the generic line.
                'stack' => $technicalSummary,
                'route' => request()->route()?->getName() ?? request()->path(),
                'status_code' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500,
                // Everything diagnostic rides on the throwable: the service
                // pulls the class, message, file, trace, cause chain and any
                // failing SQL into the super-admin-only technical column.
                'throwable' => $e,
            ]);
        } catch (Throwable) {
            // Auditing a failure must never become a second failure.
        }
    }

    /**
     * Map an exception to an action, a description safe to show a company user,
     * a severity, and the specific line a super admin should see instead.
     *
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    protected function classify(Throwable $e): array
    {
        if ($e instanceof AuthenticationException) {
            return [
                'auth.unauthenticated',
                'Blocked: request made without a valid session',
                ActivityLog::SEVERITY_WARNING,
                'Unauthenticated request to a guarded route',
            ];
        }

        if ($e instanceof AuthorizationException) {
            return [
                'authorization.denied',
                'Denied: not authorized for this action',
                ActivityLog::SEVERITY_WARNING,
                'Authorization gate or policy returned false',
            ];
        }

        if ($e instanceof TokenMismatchException) {
            return [
                'security.csrf_mismatch',
                'Blocked: your session expired, please try again',
                ActivityLog::SEVERITY_WARNING,
                'CSRF token mismatch - expired session, duplicate tab, or forged request',
            ];
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            return [
                'http.error.'.$status,
                $status >= 500
                    ? 'The system could not complete this request'
                    : sprintf('Request was rejected (%d)', $status),
                $status >= 500 ? ActivityLog::SEVERITY_ERROR : ActivityLog::SEVERITY_WARNING,
                sprintf('%s: %s', class_basename($e), $e->getMessage() ?: 'no message'),
            ];
        }

        // Deliberately generic: the class name and message describe the
        // platform's internals and belong in the technical column only.
        return [
            'error.unhandled',
            'A system error occurred and was reported',
            ActivityLog::SEVERITY_CRITICAL,
            sprintf('%s: %s', class_basename($e), $e->getMessage()),
        ];
    }
}
