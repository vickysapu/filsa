@extends('emails.data-access.layout')

@php
    $rows = [
        'Nama' => $requester_name,
        'Email' => $requester_email,
        'Proyek' => $projectName,
        'URL proyek' => $projectUrl ?: 'Tidak diisi',
        'Branch' => $branchName,
        'IP pengirim' => $ipAddress,
        'ID permintaan' => $requestId,
    ];
@endphp

@section('title', 'Permintaan Akses Data Baru')
@section('preheader', $requester_name . ' mengajukan akses data untuk proyek ' . $projectName)

@section('content')
    <h1
        style="margin:0 0 6px;font-family:Georgia,'Times New Roman',serif;font-size:26px;line-height:1.25;color:#103B2B;font-weight:700;">
        Permintaan Akses Data Baru</h1>
    <p style="margin:0 0 22px;font-size:13px;color:#66766C;">Menunggu peninjauan Anda.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:0 0 20px;border-top:1px solid #E5E8DD;">
        @foreach ($rows as $label => $value)
            <tr>
                <td
                    style="padding:10px 0;border-bottom:1px solid #E5E8DD;width:130px;vertical-align:top;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#66766C;">
                    {{ $label }}</td>
                <td style="padding:10px 0;border-bottom:1px solid #E5E8DD;color:#103B2B;word-break:break-word;">
                    {{ $value }}</td>
            </tr>
        @endforeach
        <tr>
            <td
                style="padding:10px 0;border-bottom:1px solid #E5E8DD;vertical-align:top;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#66766C;">
                Tabel diminta</td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E8DD;">
                @foreach ($tables as $table)
                    <span
                        style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;background-color:#DCE6D8;color:#103B2B;font-size:12px;font-weight:700;border-radius:3px;">{{ $table }}</span>
                @endforeach
            </td>
        </tr>
    </table>

    <div style="font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#174C35;margin:0 0 8px;">
        Tujuan penggunaan</div>
    <div style="margin:0 0 20px;padding:14px 18px;background-color:#F5F1E7;border:1px solid #D7DED2;color:#103B2B;">
        {{ $purpose }}</div>

    @include('emails.data-access.partials.keybox', [
        'label' => 'Kunci konfirmasi (sekali pakai)',
        'value' => $oneTimeKey,
        'note' =>
            'Sama dengan kode yang dikirim ke peminta. Gunakan untuk mencocokkan jika peminta menghubungi Anda.',
    ])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:8px 0 4px;border-left:4px solid #174C35;background-color:#F3F6F0;">
        <tr>
            <td style="padding:14px 18px;font-size:13px;line-height:1.7;color:#43584B;">
                <strong style="color:#103B2B;">Langkah untuk admin</strong><br>
                1. Tinjau permintaan ini di menu Request API.<br>
                2. Setelah disetujui, token otomatis dikirim ke email peminta.<br>
                3. Tiap token hanya bisa dipakai login portal
                {{ \App\Services\Security\DataAccessSecurityService::PORTAL_LOGIN_LIMIT }} kali. Jika peminta menghubungi
                karena jatah habis, terbitkan token baru lalu kirim ke emailnya. Membalas email ini langsung tertuju ke
                peminta.
            </td>
        </tr>
    </table>

    @include('emails.data-access.partials.button', ['url' => $adminUrl, 'label' => 'Buka Request API'])
@endsection
