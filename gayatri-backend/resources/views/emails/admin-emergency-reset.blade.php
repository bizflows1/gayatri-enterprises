<x-mail::message>
# Emergency Admin Password Reset

We received a request to reset the admin password for the portal. 
Use the following One-Time Password (OTP) to verify your request:

<div style="background-color: #f3f4f6; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; border-radius: 8px; margin: 20px 0;">
{{ $otp }}
</div>

If you did not request this password reset, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
