<?php

namespace App\Services;

use App\Models\EmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    public function requestRegisterCode(string $email): void
    {
        $code = (string) random_int(100000, 999999);

        EmailVerification::query()->where('email', $email)->delete();

        EmailVerification::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(30),
        ]);

        $subject = '[XOSO] Mã xác nhận đăng ký';
        $html = "<h2>Mã xác nhận</h2>
            <p>Mã xác nhận của bạn là:</p>
            <p style='font-size: 24px; font-weight: 700; letter-spacing: 2px;'>{$code}</p>
            <p>Mã có hiệu lực trong 30 phút.</p>";

        Mail::html($html, function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });
    }

    public function verifyAndConsumeRegisterCode(string $email, string $code): bool
    {
        $verification = EmailVerification::query()
            ->where('email', $email)
            ->first();

        if (!$verification) {
            return false;
        }

        if ($verification->expires_at->isPast()) {
            return false;
        }

        if (!Hash::check($code, $verification->code_hash)) {
            return false;
        }

        $verification->delete();

        return true;
    }
}
