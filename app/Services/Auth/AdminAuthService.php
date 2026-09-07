<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    /**
     * Authenticate admin and create token.
     */
    public function login(array $credentials): array
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('messages.invalid_credentials')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            Auth::logout();

            abort(403, __('messages.admin_only'));
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('admin_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout current admin token.
     */
    public function logout(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $user->currentAccessToken()?->delete();
    }
}