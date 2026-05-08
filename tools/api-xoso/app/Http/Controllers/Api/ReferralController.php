<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user->referral_code) {
            $user->forceFill(['referral_code' => User::generateReferralCode()])->save();
            $user->refresh();
        }

        $frontend = (string) config('constant.url_frontend');
        $refLink = rtrim($frontend, '/') . '/register?ref=' . $user->referral_code;

        return response()->json([
            'referral_code' => $user->referral_code,
            'referral_link' => $refLink,
            'referred_by_user_id' => $user->referred_by_user_id,
            'referral_rewarded_at' => $user->referral_rewarded_at,
        ]);
    }

    public function attach(Request $request)
    {
        $payload = $request->validate([
            'referral_code' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        abort_if($user->referred_by_user_id, 422, 'Bạn đã nhập mã giới thiệu trước đó.');

        $code = Str::upper(trim($payload['referral_code']));

        $referrer = User::query()->where('referral_code', $code)->first();
        abort_if(!$referrer, 422, 'Mã giới thiệu không hợp lệ.');
        abort_if($referrer->id === $user->id, 422, 'Mã giới thiệu không hợp lệ.');

        $user->forceFill(['referred_by_user_id' => $referrer->id])->save();

        return response()->json([
            'message' => 'Đã lưu mã giới thiệu.',
            'referred_by_user_id' => $referrer->id,
        ]);
    }
}
