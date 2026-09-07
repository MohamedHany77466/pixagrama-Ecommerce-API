<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CustomerAuthService
{
    /**
     * Register a new customer.
     */
    public function register(array $data): array
    {
        $data['type'] = 'customer';

        $user = User::create($data);

        $user->assignRole('customer');

        $token = $user->createToken('customer_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate customer and create token.
     */
    public function login(array $credentials): array
    {
        if (! Auth::attempt($credentials)) {
            abort(401, __('messages.invalid_credentials'));
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasRole('customer')) {
            Auth::logout();

            abort(403, __('messages.forbidden'));
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('customer_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout current customer token.
     */
    public function logout(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $user->currentAccessToken()?->delete();
    }
}