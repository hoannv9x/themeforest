<?php

namespace App\Services;

use App\Models\ApiSubscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PaymentService
{
    public const VIP_PLANS = [
        'vip_7d' => ['name' => 'VIP 7 ngày', 'days' => 7, 'amount' => 99000],
        'vip_15d' => ['name' => 'VIP 15 ngày', 'days' => 15, 'amount' => 179000],
        'vip_30d' => ['name' => 'VIP 30 ngày', 'days' => 30, 'amount' => 299000],
        'vip_6m' => ['name' => 'VIP 6 tháng', 'days' => 180, 'amount' => 1490000],
        'vip_1y' => ['name' => 'VIP 1 năm', 'days' => 365, 'amount' => 2590000],
    ];

    public const API_PLANS = [
        'api_7d' => ['name' => 'API 7 ngày', 'days' => 7, 'amount' => 199000],
        'api_15d' => ['name' => 'API 15 ngày', 'days' => 15, 'amount' => 349000],
        'api_30d' => ['name' => 'API 30 ngày', 'days' => 30, 'amount' => 599000],
        'api_6m' => ['name' => 'API 6 tháng', 'days' => 180, 'amount' => 2990000],
        'api_1y' => ['name' => 'API 1 năm', 'days' => 365, 'amount' => 4990000],
    ];

    public function create(User $user, string $type, string $planKey): Payment
    {
        $catalog = $type === 'api' ? self::API_PLANS : self::VIP_PLANS;
        $plan = $catalog[$planKey] ?? null;

        abort_if(!$plan, 422, 'Goi dang ky khong hop le.');

        return Payment::create([
            'user_id' => $user->id,
            'type' => $type,
            'plan_key' => $planKey,
            'plan_name' => $plan['name'],
            'duration_days' => $plan['days'],
            'amount' => $plan['amount'],
            'transfer_content' => $this->buildTransferContent($user->id, $type),
            'bank_account_name' => config('services.payment.bank_account_name'),
            'bank_account_number' => config('services.payment.bank_account_number'),
            'bank_name' => config('services.payment.bank_name'),
            'status' => 'pending',
        ]);
    }

    public function markPaid(Payment $payment, array $meta = []): Payment
    {
        if ($payment->status === 'paid') {
            return $payment;
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => array_merge($payment->meta ?? [], $meta),
        ]);

        $this->applyEntitlement($payment);
        $this->sendAdminEmail($payment->fresh('user'));

        return $payment->fresh();
    }

    public function recentPayments(User $user, int $days = 3): Collection
    {
        $from = now()->subDays($days)->startOfDay();

        return Payment::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $from)
            ->latest('id')
            ->get();
    }

    public function notifyManualTransferCompleted(Payment $payment): Payment
    {
        if (($payment->meta['manual_review_requested_at'] ?? null) === null) {
            $payment->update([
                'meta' => array_merge($payment->meta ?? [], [
                    'manual_review_requested_at' => now()->toDateTimeString(),
                ]),
            ]);
        }

        $this->sendManualReviewEmail($payment->fresh('user'));

        return $payment->fresh();
    }

    private function buildTransferContent(int $userId, string $type): string
    {
        $prefix = $type === 'api' ? 'API' : 'VIP';
        return sprintf('XOSO-%s-%d-%s', $prefix, $userId, Str::upper(Str::random(6)));
    }

    private function applyEntitlement(Payment $payment): void
    {
        $user = $payment->user;
        $expiresAt = Carbon::now()->addDays($payment->duration_days);

        if ($payment->type === 'vip') {
            $user->forceFill([
                'role' => User::ROLE_VIP,
                'vip_expired_at' => $expiresAt,
            ])->save();
            return;
        }

        $user->forceFill([
            'permission' => User::PERMISSION_DEVELOPER,
        ])->save();

        ApiSubscription::create([
            'user_id' => $user->id,
            'plan_key' => $payment->plan_key,
            'plan_name' => $payment->plan_name,
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);
    }

    private function sendAdminEmail(Payment $payment): void
    {
        $user = $payment->user;
        $vipLink = URL::temporarySignedRoute('admin.user.update-role', now()->addDays(7), [
            'user' => $user->id,
            'role' => User::ROLE_VIP,
            'days' => $payment->duration_days,
        ]);
        $normalLink = URL::temporarySignedRoute('admin.user.update-role', now()->addDays(7), [
            'user' => $user->id,
            'role' => User::ROLE_USER,
            'days' => 0,
        ]);
        $developerLink = URL::temporarySignedRoute('admin.user.update-permission', now()->addDays(7), [
            'user' => $user->id,
            'permission' => User::PERMISSION_DEVELOPER,
            'days' => $payment->duration_days,
        ]);
        $userPermissionLink = URL::temporarySignedRoute('admin.user.update-permission', now()->addDays(7), [
            'user' => $user->id,
            'permission' => User::PERMISSION_USER,
            'days' => 0,
        ]);

        $adminMail = env('ADMIN_PAYMENT_EMAIL', 'adminxosoai@gmail.com');
        $subject = '[XOSO] Thanh toán thành công: '.$user->email;
        $html = "<h2>Thong bao thanh toán thành công</h2>
            <p>User: <strong>{$user->name}</strong> ({$user->email})</p>
            <p>Loại gói cài đặt: <strong>{$payment->plan_name}</strong> ({$payment->type})</p>
            <p>Nội dung chuyển khoản: <strong>{$payment->transfer_content}</strong></p>
            <p>Số tiền: <strong>".number_format($payment->amount)." VND</strong></p>
            <hr>
            <p>Cập nhật role:</p>
            <p><a href='{$vipLink}'>Approve VIP</a> | <a href='{$normalLink}'>Approve User thường</a></p>
            <p>Cập nhật permission:</p>
            <p><a href='{$developerLink}'>Approve Developer</a> | <a href='{$userPermissionLink}'>Approve User thường</a></p>";

        Mail::html($html, function ($message) use ($adminMail, $subject) {
            $message->to($adminMail)->subject($subject);
        });
    }

    private function sendManualReviewEmail(Payment $payment): void
    {
        $user = $payment->user;
        $mailTo = env('ADMIN_PAYMENT_EMAIL', 'xosoai@gmail.com');
        $subject = '[XOSO] Yêu cầu kiểm tra thanh toán: ' . $user->email;
        $html = "<h2>Yêu cầu kiểm tra thanh toán thành công</h2>
            <p>Loại gói cài: <strong>{$payment->plan_name}</strong> ({$payment->type})</p>
            <p>So tiền: <strong>" . number_format($payment->amount) . " VND</strong></p>
            <p>Nội dung chuyển khoản: <strong>{$payment->transfer_content}</strong></p>
            <p>Tình trạng: <strong>{$payment->status}</strong></p>
            <p>Thời gian user xác nhận thanh toán: <strong>" . now()->toDateTimeString() . "</strong></p>";

        Mail::html($html, function ($message) use ($mailTo, $subject) {
            $message->to($mailTo)->subject($subject);
        });
    }
}
