@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ rtrim(config('app.url'), '/') }}/mail/logo.png" alt="{{ config('app.name') }}" width="220" height="47" style="display: block; width: 220px; height: 47px; border: none;">
</a>
</td>
</tr>
