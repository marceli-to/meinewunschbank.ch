@props(['url'])
<tr>
<td class="header">
{{-- Text wordmark until the Raiffeisen lockup exists as a hosted PNG. Remote
     images are blocked by default in most clients anyway, so text also means
     the sender is identifiable before anyone clicks "show images". --}}
<a href="{{ $url }}" style="display: inline-block;">{{ $slot }}</a>
</td>
</tr>
