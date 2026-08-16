@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ asset('assets/images/logo/olten_location.png') }}" width="150" alt="Logo Olten">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
