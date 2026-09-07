<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\CustomerAuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class CustomerAuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CustomerAuthService $customerAuthService
    ) {}

    /**
     * Register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->customerAuthService->register(
            $request->validated()
        );

        return $this->successResponse(
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            __('messages.register'),
            201
        );
    }

    /**
     * Customer Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->customerAuthService->login(
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
     * Customer Logout
     */
    public function logout(): JsonResponse
    {
        $this->customerAuthService->logout();

        return $this->successResponse(
            [],
            __('messages.logout_success')
        );
    }
}