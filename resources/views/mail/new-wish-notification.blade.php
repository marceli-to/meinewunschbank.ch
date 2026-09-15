@component('mail::message')
# Neuer Herzenswunsch

Es wurde ein neuer Herzenswunsch eingereicht. Er wartet im Control Panel auf die Prüfung.<br><br>
**Name:**<br>
{{ $name }}<br><br>
**E-Mail:**<br>
{{ $email }}<br><br>
**Wunsch:**<br>
{!! nl2br(e($wish)) !!}

@component('mail::button', ['url' => $url])
Im Control Panel öffnen
@endcomponent
@endcomponent
