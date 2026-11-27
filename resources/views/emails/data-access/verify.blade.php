@extends('emails.data-access.layout')

@section('title', 'Konfirmasi Permintaan Akses Data')
@section('preheader', 'Konfirmasi email Anda untuk permintaan akses data proyek ' . $projectName)

@section('content')
    <h1
        style="margin:0 0 6px;font-family:Georgia,'Times New Roman',serif;font-size:26px;line-height:1.25;color:#103B2B;font-weight:700;">
        Konfirmasi Permintaan Akses Data</h1>
    <p style="margin:0 0 22px;font-size:13px;color:#66766C;">Proyek: <strong
            style="color:#103B2B;">{{ $projectName }}</strong></p>

    <p style="margin:0 0 14px;">Halo <strong style="color:#103B2B;">{{ $requester_name }}</strong>,</p>
    <p style="margin:0 0 20px;">Permintaan akses data Anda sudah kami terima dan akan ditinjau oleh admin. Konfirmasi email
        Anda dengan tombol di bawah agar prosesnya dapat dilanjutkan.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:0 0 6px;border-top:1px solid #E5E8DD;">
        <tr>
            <td
                style="padding:12px 0;border-bottom:1px solid #E5E8DD;width:130px;vertical-align:top;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#66766C;">
                Tabel diminta</td>
            <td style="padding:12px 0;border-bottom:1px solid #E5E8DD;">
                @foreach ($tables as $table)
                    <span
                        style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;background-color:#DCE6D8;color:#103B2B;font-size:12px;font-weight:700;border-radius:3px;">{{ $table }}</span>
                @endforeach
            </td>
        </tr>
        <tr>
            <td
                style="padding:12px 0;border-bottom:1px solid #E5E8DD;vertical-align:top;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#66766C;">
                Tujuan</td>
            <td style="padding:12px 0;border-bottom:1px solid #E5E8DD;color:#103B2B;">{{ $purpose }}</td>
        </tr>
    </table>

    @if (!empty($oneTimeKey))
        @include('emails.data-access.partials.keybox', [
            'label' => 'Kunci konfirmasi (sekali pakai)',
            'value' => $oneTimeKey,
            'note' =>
                'Kode yang sama juga diterima admin sebagai pencocokan. Berlaku ' .
                ($expiresIn ?? '48 jam') .
                ' dan hanya bisa dipakai sekali.',
        ])
    @endif

    @isset($verificationUrl)
        @include('emails.data-access.partials.button', [
            'url' => $verificationUrl,
            'label' => 'Konfirmasi Email Saya',
        ])
    @endisset

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:8px 0 20px;border-left:4px solid #174C35;background-color:#F3F6F0;">
        <tr>
            <td style="padding:14px 18px;font-size:13px;line-height:1.7;color:#43584B;">
                <strong style="color:#103B2B;">Yang perlu Anda ketahui</strong><br>
                &bull; Setelah disetujui admin, token akses akan dikirim ke email ini.<br>
                &bull; Setiap token hanya bisa dipakai login portal sebanyak
                {{ \App\Services\Security\DataAccessSecurityService::PORTAL_LOGIN_LIMIT }} kali. Jika sudah habis, hubungi
                admin untuk mendapatkan token baru.
            </td>
        </tr>
    </table>

    @isset($verificationUrl)
        <p style="margin:0;font-size:12px;color:#66766C;">Jika tombol tidak berfungsi, salin tautan berikut ke browser Anda:<br>
            <a href="{{ $verificationUrl }}" style="color:#174C35;word-break:break-all;">{{ $verificationUrl }}</a>
        </p>
    @endisset
@endsection
