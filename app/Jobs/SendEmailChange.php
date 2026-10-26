<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\{ShouldQueue, ShouldBeEncrypted};
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\{DB, Mail};

class SendEmailChange implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;
    public int $tries = 3;
    public function backoff(): array { return [30, 120, 600]; }
    public function __construct(public int $userId, public string $token, public int $attemptId) { $this->onQueue('security-mail'); }

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user || !$user->pending_email || !$user->email_change_expires_at?->isFuture()
            || !hash_equals((string) $user->email_change_hash, hash('sha256', $this->token))) {
            DB::table('security_mail_attempts')->where('id', $this->attemptId)->update(['status' => 'superseded', 'updated_at' => now()]);
            return;
        }
        DB::table('security_mail_attempts')->where('id', $this->attemptId)->increment('attempts');
        $url = route('users.email.confirm', ['usuario' => $user, 'token' => $this->token]);
        try {
            Mail::send('emails.email-change', compact('user', 'url'), fn ($mail) => $mail->to($user->pending_email)->subject('Confirma tu nuevo correo de AgroCloud'));
            $mailer = config('mail.default');
            DB::table('security_mail_attempts')->where('id', $this->attemptId)->update(['status' => in_array(config("mail.mailers.$mailer.transport", $mailer), ['array', 'log', 'failover'], true) ? 'simulated' : 'provider_accepted', 'updated_at' => now()]);
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
