<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:14px 0;border:1px solid #D7DED2;border-left:4px solid #C5A66A;background-color:#F5F1E7;">
    <tr>
        <td style="padding:16px 18px;">
            <div
                style="font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#43584B;">
                {{ $label }}</div>
            <div
                style="margin-top:8px;font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:{{ $size ?? 20 }}px;line-height:1.5;font-weight:700;letter-spacing:{{ $spacing ?? 1 }}px;color:#103B2B;word-break:break-all;">
                {{ $value }}</div>
            @isset($note)
                <div style="margin-top:8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#66766C;">
                    {{ $note }}</div>
            @endisset
        </td>
    </tr>
</table>
