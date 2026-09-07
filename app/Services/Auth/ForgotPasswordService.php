<?php

namespace App\Services\Auth;

use App\Mail\OtpPasswordResetMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordService
{
    /**
     * Send OTP to user email
     */
    public function sendOtp(string $email): array
    {
        $user = User::where('email', $email)->first();

       if (!$user) {
    return [
        'success' => true,
        'message' => 'If the email exists, an OTP has been sent.',
        'status' => 200,
    ];
}

        // Delete old OTP
        PasswordResetOtp::where('email', $email)->delete();

        // Generate secure OTP
        $otp = random_int(100000, 999999);

        // Save OTP
        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send email
        Mail::to($email)->send(
            new OtpPasswordResetMail(
                $user->name,
                $otp,
                10
            )
        );

        return [
            'success' => true,
            'message' => 'OTP sent successfully.',
            'status' => 200,
            'data' => [
                'email' => $email,
                'expires_in_minutes' => 10,
            ],
        ];
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $otpRecord = PasswordResetOtp::where('email', $email)
    ->latest()
    ->first();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => 'OTP not found.',
                'status' => 404,
            ];
        }

        if ($otpRecord->expires_at->isPast()) {
            $otpRecord->delete();

            return [
                'success' => false,
                'message' => 'OTP has expired.',
                'status' => 400,
            ];
        }

        if ($otpRecord->otp !== $otp) {
            return [
                'success' => false,
                'message' => 'Invalid OTP.',
                'status' => 400,
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'status' => 200,
        ];
    }

    /**
     * Reset Password
     */
    public function resetPassword(
        string $email,
        string $otp,
        string $password
    ): array {

        $result = $this->verifyOtp($email, $otp);

        if (!$result['success']) {
            return $result;
        }

        $user = User::where('email', $email)->first();

        $user->update([
            'password' => Hash::make($password),
        ]);

        PasswordResetOtp::where('email', $email)->delete();

        return [
            'success' => true,
            'message' => 'Password has been reset successfully.',
            'status' => 200,
        ];
    }
}
