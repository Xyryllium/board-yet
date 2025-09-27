@component('mail::message')
# 🎉 You're Invited!

You have been invited to join **{{ $invitation->organization->name }}** as a **{{ ucfirst($invitation->role) }}**.

## Organization Details

| **Organization** | {{ $invitation->organization->name }} |
|------------------|--------------------------------------|
| **Your Role**    | {{ ucfirst($invitation->role) }}     |
| **Invited By**   | {{ config('app.name') }}             |

You've been invited to collaborate on boards, manage projects, and work together with your team members.

@component('mail::button', ['url' => $invitationUrl])
Accept Invitation
@endcomponent

---

## ⚠️ Important Security Note

This invitation link is unique to you and will expire in 7 days. If you didn't expect this invitation, you can safely ignore this email.

---

**This invitation was sent by {{ config('app.name') }}**

If you have any questions, please contact your team administrator.

Thanks,  
{{ config('app.name') }} Team
@endcomponent
