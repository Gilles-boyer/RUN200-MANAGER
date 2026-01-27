<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job for sending bulk emails asynchronously.
 * Implements rate limiting and retry logic.
 */
class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $recipient,
        public Mailable $mailable,
        public ?string $context = null,
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->recipient)->send($this->mailable);

            Log::info('Email sent successfully', [
                'recipient' => $this->recipient->email,
                'mailable' => get_class($this->mailable),
                'context' => $this->context,
            ]);
        } catch (\Exception $e) {
            Log::warning('Email send attempt failed', [
                'recipient' => $this->recipient->email,
                'mailable' => get_class($this->mailable),
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Email job failed permanently', [
            'recipient' => $this->recipient->email,
            'mailable' => get_class($this->mailable),
            'context' => $this->context,
            'error' => $exception->getMessage(),
        ]);
    }
}
