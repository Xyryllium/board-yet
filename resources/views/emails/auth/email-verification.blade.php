@component('mail::message')
# ✅ Verify Your Email Address

Welcome to **{{ config('app.name') }}**! Please verify your email address to complete your account setup.

## Account Details

| **Name**  | {{ $notifiable->name }} |
|-----------|-------------------------|
| **Email** | {{ $notifiable->email }} |
| **Registered At** | {{ now()->format('M j, Y \a\t g:i A') }} |

To get started, please verify your email address by clicking the button below:

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

---

## ⚠️ Important Information

- This verification link will expire in **{{ config('auth.verification.expire', 60) }} minutes**
- If you didn't create this account, you can safely ignore this email
- You must verify your email before accessing certain features
- For security reasons, this link can only be used once

---

**This verification email was sent by {{ config('app.name') }}**

If you have any questions or concerns, please contact our support team.

Thanks,  
{{ config('app.name') }} Team
@endcomponent
