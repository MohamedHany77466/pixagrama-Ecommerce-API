<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding:20px;">

    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">

        <h2>Hello {{ $name }},</h2>

        <p>We received a request to reset your password.</p>

        <p>Please use the following verification code:</p>

        <div style="text-align:center; margin:30px 0;">
            <span style="font-size:32px; font-weight:bold; letter-spacing:8px; color:#2563eb;">
                {{ $otp }}
            </span>
        </div>

        <p>
            This code is valid for
            <strong>{{ $minutes }} minutes</strong>.
        </p>

        <p>
            If you did not request a password reset, you can safely ignore this email.
        </p>

        <hr>

        <small>
            This is an automated email. Please do not reply.
        </small>

    </div>

</body>
</html>