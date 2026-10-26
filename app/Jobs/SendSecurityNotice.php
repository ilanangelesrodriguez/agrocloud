<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\{DB, Mail};

class SendSecurityNotice implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public function backoff(): array { return [30, 120, 600]; }

    public function __construct(public int $attemptId, public string $address, public string $subject, public string $message) { $this->onQueue('security-mail'); }

    public function handle(): void
    {
        $attempt = DB::table('security_mail_attempts')->find($this->attemptId);
        if (!$attempt || $attempt->status === 'provider_accepted') return;
        DB::table('security_mail_attempts')->where('id', $this->attemptId)->increment('attempts');
        try {
            $mailer = config('mail.default');
            $transport = config("mail.mailers.$mailer.transport", $mailer);
            Mail::raw($this->message, fn ($mail) => $mail->to($this->address)->subject($this->subject));
            DB::table('security_mail_attempts')->where('id', $this->attemptId)->update([
                'status' => in_array($transport, ['array', 'log', 'failover'], true) ? 'simulated' : 'provider_accepted', 'updated_at' => now(),
            ]);
        } catch (\Throwable $error) {
            DB::table('security_mail_attempts')->where('id', $this->attemptId)->update(['status' => 'retry_pending', 'updated_at' => now()]);
            throw $error;
        }
    }

    public function failed(?\Throwable $error): void
    {
        DB::table('security_mail_attempts')->where('id', $this->attemptId)->update(['status' => 'failed', 'updated_at' => now()]);
    }
}
