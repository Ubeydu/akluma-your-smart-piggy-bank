@component('mail::message')
    # Admin Retry Alert

    Saving ID **{{ $saving->id }}** has failed to send an email **{{ $attemptCount }} times**.

    - Piggy Bank: **{{ $saving->piggyBank->name }}**
    - User: **{{ $saving->piggyBank->user->email }}**
    - Saving Date: **{{ $saving->saving_date }}**

    You may want to investigate this saving.

    Thanks,<br>
    Akluma System
@endcomponent
