<p>Hello,</p>

<p>You requested a password reset. Click the link below:</p>

<p>
    <a href="{{ url('/reset-password?token='.$token.'&email='.$email) }}">
        Reset Password
    </a>
</p>

<p>If you did not request this, ignore this email.</p>
