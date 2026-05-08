<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly EmailVerificationService $emailVerificationService)
    {
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'verification_code' => ['required', 'string', 'size:6'],
            'referral_code' => ['nullable', 'string', 'max:64'],
        ]);

        $ok = $this->emailVerificationService->verifyAndConsumeRegisterCode(
            (string) $request->email,
            (string) $request->verification_code
        );
        abort_if(!$ok, 422, 'Mã xác nhận không đúng hoặc đã hết hạn.');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
        ]);

        $refCode = $request->input('referral_code');
        if ($refCode) {
            $code = Str::upper(trim((string) $refCode));
            $referrer = User::query()->where('referral_code', $code)->first();
            abort_if(!$referrer, 422, 'Mã giới thiệu không hợp lệ.');
            abort_if($referrer->id === $user->id, 422, 'Mã giới thiệu không hợp lệ.');
            $user->forceFill(['referred_by_user_id' => $referrer->id])->save();
        }

        $user->startVipTrial();
        $user->refresh();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công.',
            'user' => $user,
            'token' => $token,
            'vip_status' => $user->getVipStatus(),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'vip_status' => $user->getVipStatus(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'vip_status' => $request->user()->getVipStatus(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $payload = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        abort_if(!$user, 401, 'Unauthenticated');

        abort_if(!Hash::check($payload['current_password'], $user->password), 422, 'Mật khẩu hiện tại không đúng.');

        $user->forceFill([
            'password' => $payload['password'],
        ])->save();

        return response()->json([
            'message' => 'Đổi mật khẩu thành công.',
        ]);
    }
}
