<?php

namespace App\Jobs;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(
        public Mailable $mailable,
        public string|array $to,
        public ?int $companyId = null,
    ) {
    }

    public function handle(MailService $mailService): void
    {
        $mailService->sendNow($this->mailable, $this->to, $this->companyId);
    }

    public function failed(Throwable $e): void
    {
        Log::channel('mail')->error('Queued email gave up after all retries', [
            'mailable'   => get_class($this->mailable),
            'to'         => $this->to,
            'company_id' => $this->companyId,
            'error'      => $e->getMessage(),
        ]);
    }
}
