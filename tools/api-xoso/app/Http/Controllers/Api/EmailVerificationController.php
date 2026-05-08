<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function requestCode(Request $request, EmailVerificationService $emailVerificationService)
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $emailVerificationService->requestRegisterCode($payload['email']);

        return response()->json([
            'message' => 'Đã gửi mã xác nhận. Vui lòng kiểm tra email.',
        ]);
    }
}
