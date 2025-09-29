@component('mail::message')
# 🔐 Reset Your Password

You requested a password reset for your **{{ config('app.name') }}** account.

## Account Details

| **Email** | {{ $email }} |
|-----------|--------------|
| **Requested At** | {{ now()->format('M j, Y \a\t g:i A') }} |

If you requested this password reset, click the button below to set a new password:

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

---

## ⚠️ Important Security Information

- This password reset link will expire in **60 minutes**
- If you didn't request this password reset, you can safely ignore this email
- Your password will remain unchanged until you click the link above
- For security reasons, this link can only be used once

---

**This password reset was requested from {{ config('app.name') }}**

If you have any questions or concerns, please contact our support team.

Thanks,  
{{ config('app.name') }} Team
@endcomponent
