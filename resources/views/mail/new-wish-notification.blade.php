@component('mail::message')
# Neuer Herzenswunsch

Es wurde ein neuer Herzenswunsch eingereicht. Er wartet im Control Panel auf die Prüfung.

**Name:** {{ $name }}<br>
**E-Mail:** {{ $email }}

**Wunsch:**

{{ $wish }}

@component('mail::button', ['url' => $url])
Im Control Panel öffnen
@endcomponent

{{ config('app.name') }}
@endcomponent
