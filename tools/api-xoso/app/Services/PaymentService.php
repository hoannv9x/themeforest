<?php

namespace App\Services;

use App\Models\ApiSubscription;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentService
{
    public function getPlansForType(string $type): array
    {
        $rows = PaymentPlan::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->plan_key] = [
                'name' => $row->name,
                'days' => (int) $row->duration_days,
                'amount' => (int) $row->amount,
            ];
        }

        return $out;
    }

    public function create(User $user, string $type, string $planKey, ?string $couponCode = null, ?int $amount = null): Payment
    {
        if ($type === 'donate') {
            return Payment::create([
                'user_id' => $user->id,
                'type' => $type,
                'plan_key' => 'donation',
                'plan_name' => 'Tán Lộc',
                'duration_days' => 0,
                'amount' => $amount,
                'transfer_content' => $this->buildTransferContent($user->id, $type),
                'bank_account_name' => config('services.payment.bank_account_name'),
                'bank_account_number' => config('services.payment.bank_account_number'),
                'bank_name' => config('services.payment.bank_name'),
                'status' => 'pending',
                'meta' => [
                    'amount_final' => $amount,
                ],
            ]);
        }

        $plan = PaymentPlan::query()
            ->where('type', $type)
            ->where('plan_key', $planKey)
            ->where('is_active', true)
            ->first();

        abort_if(!$plan, 422, 'Goi dang ky khong hop le.');

        $coupon = $this->resolveCouponForPayment($user, $couponCode);

        $amountOriginal = (int) $plan->amount;
        $discountPercent = $coupon ? (int) $coupon->discount_percent : 0;
        $discountAmount = $discountPercent > 0 ? (int) floor($amountOriginal * $discountPercent / 100) : 0;
        $amountFinal = max(0, $amountOriginal - $discountAmount);

        return Payment::create([
            'user_id' => $user->id,
            'type' => $type,
            'plan_key' => $planKey,
            'plan_name' => $plan->name,
            'duration_days' => (int) $plan->duration_days,
            'amount' => $amountFinal,
            'transfer_content' => $this->buildTransferContent($user->id, $type),
            'bank_account_name' => config('services.payment.bank_account_name'),
            'bank_account_number' => config('services.payment.bank_account_number'),
            'bank_name' => config('services.payment.bank_name'),
            'status' => 'pending',
            'meta' => array_filter([
                'amount_original' => $amountOriginal,
                'discount_percent' => $discountPercent ?: null,
                'discount_amount' => $discountAmount ?: null,
                'amount_final' => $amountFinal,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
            ], fn ($v) => $v !== null),
        ]);
    }

    public function markPaid(Payment $payment, array $meta = []): Payment
    {
        if ($payment->status === 'paid') {
            return $payment;
        }

        abort_if(in_array($payment->status, ['cancelled', 'rejected'], true), 422, 'Không thể approve giao dịch đã bị huỷ/từ chối.');

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => array_merge($payment->meta ?? [], $meta),
        ]);

        $this->applyEntitlement($payment);
        $this->consumeCouponIfAny($payment);
        $this->rewardReferralIfEligible($payment);

        return $payment->fresh();
    }

    public function reject(Payment $payment, User $actor, string $reason): Payment
    {
        abort_if($payment->status === 'paid', 422, 'Không thể từ chối giao dịch đã paid.');

        $payment->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_reason' => $reason,
            'rejected_by_user_id' => $actor->id,
            'meta' => array_merge($payment->meta ?? [], [
                'rejected_at' => now()->toDateTimeString(),
                'rejected_reason' => $reason,
                'rejected_by_user_id' => $actor->id,
            ]),
        ]);

        return $payment->fresh();
    }

    public function cancel(Payment $payment, User $actor, ?string $reason = null): Payment
    {
        abort_if($payment->status !== 'pending', 422, 'Chỉ có thể huỷ giao dịch đang pending.');

        $payment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_reason' => $reason,
            'meta' => array_merge($payment->meta ?? [], array_filter([
                'cancelled_at' => now()->toDateTimeString(),
                'cancelled_reason' => $reason ?: null,
                'cancelled_by_user_id' => $actor->id,
            ], fn ($v) => $v !== null)),
        ]);

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
        $payment->update([
            'manual_review_status' => 'requested',
            'manual_review_requested_at' => now(),
            'meta' => array_merge($payment->meta ?? [], [
                'manual_review_requested_at' => now()->toDateTimeString(),
            ]),
        ]);

        $this->sendAdminEmail($payment->fresh('user'));

        return $payment->fresh();
    }

    private function buildTransferContent(int $userId, string $type): string
    {
        $prefix = match ($type) {
            'api' => 'API',
            'donate' => 'LOC',
            default => 'VIP',
        };
        return sprintf('XOSO-%s-%d-%s', $prefix, $userId, Str::upper(Str::random(6)));
    }

    private function applyEntitlement(Payment $payment): void
    {
        $user = $payment->user;
        $now = Carbon::now();

        if ($payment->type === 'donate') {
            // Tán lộc không cấp quyền gì thêm, chỉ ghi nhận
            return;
        }

        if ($payment->type === 'vip') {
            $currentExpiredAt = $user->vip_expired_at;
            $base = ($currentExpiredAt && $currentExpiredAt->isFuture()) ? $currentExpiredAt : $now;
            $expiresAt = $base->copy()->addDays($payment->duration_days);

            $user->forceFill([
                'role' => User::ROLE_VIP,
                'vip_expired_at' => $expiresAt,
                'vip_trial_started_at' => null,
            ])->save();
            return;
        }

        $expiresAt = $now->copy()->addDays($payment->duration_days);

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

    private function resolveCouponForPayment(User $user, ?string $couponCode): ?Coupon
    {
        $couponCode = $couponCode !== null ? trim((string) $couponCode) : '';
        if ($couponCode === '') {
            return null;
        }

        $code = Str::upper($couponCode);

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        abort_if(!$coupon, 422, 'Mã coupon không hợp lệ hoặc đã hết hạn.');
        abort_if($coupon->discount_type !== 'percent', 422, 'Mã coupon không hợp lệ.');
        abort_if($coupon->discount_percent <= 0, 422, 'Mã coupon không hợp lệ.');

        if ($coupon->user_id !== null) {
            abort_if((int) $coupon->user_id !== (int) $user->id, 422, 'Mã coupon không áp dụng cho tài khoản này.');
        }

        if ($coupon->max_uses !== null) {
            abort_if((int) $coupon->used_count >= (int) $coupon->max_uses, 422, 'Mã coupon đã hết lượt sử dụng.');
        }

        return $coupon;
    }

    private function consumeCouponIfAny(Payment $payment): void
    {
        $couponId = $payment->meta['coupon_id'] ?? null;
        if (!$couponId) {
            return;
        }

        Coupon::query()->whereKey($couponId)->increment('used_count');
    }

    private function rewardReferralIfEligible(Payment $payment): void
    {
        $user = $payment->user?->fresh();
        if (!$user) {
            return;
        }

        if (!$user->referred_by_user_id) {
            return;
        }

        if ($user->referral_rewarded_at) {
            return;
        }

        $referrer = User::query()->whereKey($user->referred_by_user_id)->first();
        $user->forceFill(['referral_rewarded_at' => now()])->save();

        if (!$referrer) {
            return;
        }

        Coupon::create([
            'code' => Coupon::generateUniqueCode(10),
            'discount_type' => 'percent',
            'discount_percent' => 5,
            'max_uses' => 1,
            'used_count' => 0,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
            'user_id' => $referrer->id,
            'source' => 'referral',
            'meta' => [
                'referred_user_id' => $user->id,
                'payment_id' => $payment->id,
            ],
        ]);
    }

    private function sendAdminEmail(Payment $payment): void
    {
        $user = $payment->user;
        $vipLink = config('constant.url_frontend').'/admin/payments?payment='.$payment->id;
        $normalLink = config('constant.url_frontend').'/admin/payments?payment='.$payment->id.'&role='.User::ROLE_USER;
        $developerLink = config('constant.url_frontend').'/admin/payments?payment='.$payment->id.'&permission='.User::PERMISSION_DEVELOPER;
        $userPermissionLink = config('constant.url_frontend').'/admin/payments?payment='.$payment->id.'&permission='.User::PERMISSION_USER;

        $amountLine = "<p>Số tiền: <strong>".number_format($payment->amount)." VND</strong></p>";
        $original = $payment->meta['amount_original'] ?? null;
        $discountPercent = $payment->meta['discount_percent'] ?? null;
        $discountAmount = $payment->meta['discount_amount'] ?? null;
        $couponCode = $payment->meta['coupon_code'] ?? null;
        if ($original && $discountAmount && $couponCode) {
            $amountLine = "<p>Số tiền: <strong>".number_format((int) $payment->amount)." VND</strong> (gốc ".number_format((int) $original)." - giảm {$discountPercent}% / ".number_format((int) $discountAmount).", coupon {$couponCode})</p>";
        }

        $adminMail = env('ADMIN_PAYMENT_EMAIL', 'adminxosoai@gmail.com');
        $subject = '[XOSO] Thanh toán thành công: '.$user->email;
        $html = "<h2>Thông báo xác nhận thanh toán thành công</h2>
            <p>User: <strong>{$user->name}</strong> ({$user->email})</p>
            <p>Loại gói cài đặt: <strong>{$payment->plan_name}</strong> ({$payment->type})</p>
            <p>Nội dung chuyển khoản: <strong>{$payment->transfer_content}</strong></p>
            {$amountLine}
            <hr>
            <p>Cập nhật role:</p>
            <p><a href='{$vipLink}'>Approve VIP</a> | <a href='{$normalLink}'>Approve User thường</a></p>
            <p>Cập nhật permission:</p>
            <p><a href='{$developerLink}'>Approve Developer</a> | <a href='{$userPermissionLink}'>Approve User thường</a></p>";

        Mail::html($html, function ($message) use ($adminMail, $subject) {
            $message->to($adminMail)->subject($subject);
        });
    }
}
