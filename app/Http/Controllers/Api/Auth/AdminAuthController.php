<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AdminAuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AdminAuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AdminAuthService $adminAuthService
    ) {}

    /**
     * Admin Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->adminAuthService->login(
            $request->validated()
        );

        return $this->successResponse(
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            __('messages.login_success')
        );
    }

    /**
     * Admin Logout
     */
    public function logout(): JsonResponse
    {
        $this->adminAuthService->logout();

        return $this->successResponse(
            [],
            __('messages.logout_success')
        );
    }
}