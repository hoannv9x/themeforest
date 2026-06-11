<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ProfileController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());
        return $this->success($user, 'Profile updated successfully');
    }
}
