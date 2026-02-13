<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body>
    <h2>Welcome to La Verdad Herald!</h2>
    <p>Hi {{ $user->name }},</p>
    <p>Please verify your email address by clicking the link below:</p>
    <p><a href="{{ $verificationUrl }}">Verify Email</a></p>
    <p>Or copy and paste this link in your browser:</p>
    <p>{{ $verificationUrl }}</p>
    <p>This link will expire in 24 hours.</p>
    <p>If you didn't create this account, please ignore this email.</p>
</body>
</html>
