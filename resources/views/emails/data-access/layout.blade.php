<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>@yield('title', 'Gereja Filadelfia')</title>
</head>

<body style="margin:0;padding:0;background-color:#EEF1EA;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#EEF1EA;font-size:1px;line-height:1px;">
        @yield('preheader')</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#EEF1EA;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%;max-width:600px;">
                    <tr>
                        <td style="background-color:#174C35;padding:26px 32px;border-radius:6px 6px 0 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:14px;vertical-align:middle;">
                                        <img src="{{ asset('img/white.tree-logo.png') }}" width="40" height="40"
                                            alt="" style="display:block;border:0;width:40px;height:40px;">
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <div
                                            style="font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:700;letter-spacing:2px;color:#F5F1E7;line-height:1.1;">
                                            FILADELFIA</div>
                                        <div
                                            style="font-family:Arial,Helvetica,sans-serif;font-size:10px;font-weight:700;letter-spacing:2px;color:#C5A66A;text-transform:uppercase;margin-top:4px;">
                                            Portal Data Gereja</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:3px;line-height:3px;font-size:3px;background-color:#C5A66A;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td
                            style="background-color:#FFFFFF;padding:36px 32px 32px;border-left:1px solid #D7DED2;border-right:1px solid #D7DED2;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;color:#43584B;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="background-color:#103B2B;padding:22px 32px;border-radius:0 0 6px 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#9FB29A;">
                            <div style="color:#F5F1E7;font-weight:700;">Gereja Filadelfia · Bandung</div>
                            <div>Pesan ini dikirim otomatis oleh Portal Data Gereja Filadelfia. Jika Anda tidak merasa
                                mengajukan permintaan ini, abaikan email ini.</div>
                            <div style="margin-top:8px;color:#6F8A78;">&copy; {{ date('Y') }} Gereja Filadelfia
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
