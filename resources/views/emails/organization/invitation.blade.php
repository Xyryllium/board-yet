@component('mail::message')
# You're Invited!

You have been invited to join the organization **{{ $invitation->organization->name }}** as a **{{ ucfirst($invitation->role) }}**.

Click the button below to accept your invitation:

@component('mail::button', ['url' => url("/invitations/accept/{$invitation->token}")])
Accept Invitation
@endcomponent

If you did not expect this invitation, you may ignore this email.

Thanks,  
{{ config('app.name') }}
@endcomponent
