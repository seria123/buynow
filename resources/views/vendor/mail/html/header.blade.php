@props(['url'])
<tr>
<td class="header" style="background-color: #ffffff; padding: 40px 40px 30px; text-align: center; border-radius: 24px 24px 0 0;">
<a href="{{ $url }}" style="text-decoration: none; display: inline-block;">
@if (trim($slot) === 'Laravel' || trim($slot) === config('app.name'))
<table cellpadding="0" cellspacing="0" role="presentation" align="center" style="margin: 0 auto;">
<tr>
<td style="padding-right: 12px; vertical-align: middle;">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="background-color: #FACC15; border-radius: 16px; width: 56px; height: 56px; text-align: center; vertical-align: middle; box-shadow: 0 4px 6px -1px rgba(250, 204, 21, 0.3);">
<span style="color: #000000; font-size: 24px; font-weight: bold; line-height: 56px;">B</span>
</td>
</tr>
</table>
</td>
<td style="vertical-align: middle;">
<span style="color: #FACC15; font-size: 28px; font-weight: bold; text-decoration: none;">Buynow</span>
</td>
</tr>
</table>
@else
<span style="color: #FACC15; font-size: 28px; font-weight: bold;">{!! $slot !!}</span>
@endif
</a>
</td>
</tr>
