<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('profile'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully');
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->login($request->email, $request->password);

        if (!$user) {
            return $this->error('Invalid credentials', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('profile'),
            'token' => $token
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }

    public function forgotPassword(Request $request)
    {
        return $this->success(null, 'If your email exists in our system, you will receive a reset link.');
    }
}
