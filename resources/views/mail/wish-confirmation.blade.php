@component('mail::message')
# Vielen Dank für Ihren Herzenswunsch

Guten Tag {{ $name }}

Wir haben Ihren Herzenswunsch erhalten und melden uns bei Ihnen.

**Ihr Wunsch:**<br>
{!! nl2br(e($wish)) !!}

Vielleicht möchten Sie, dass sich Ihre Freundinnen und Freunde auch etwas wünschen können? Dann teilen Sie Ihr Bild doch auf Social Media!

Freundliche Grüsse<br>
Raiffeisenbank Weissenstein
@endcomponent
