@extends('emails.data-access.layout')

@php
    $nama = html_entity_decode($request->requester_name, ENT_QUOTES);
    $proyek = html_entity_decode($projectName, ENT_QUOTES);
    $adminMail = $adminEmail ?? 'filadelfiamusa@gmail.com';
    $portal = $portalUrl ?? route('data-portal.verify');
    $limit = \App\Services\Security\DataAccessSecurityService::PORTAL_LOGIN_LIMIT;
@endphp

@section('title', 'Permintaan Akses Data Disetujui')
@section('preheader', 'Token akses untuk proyek ' . $proyek . ' sudah siap dipakai')

@section('content')
    <h1
        style="margin:0 0 6px;font-family:Georgia,'Times New Roman',serif;font-size:26px;line-height:1.25;color:#103B2B;font-weight:700;">
        Permintaan Akses Data Disetujui</h1>
    <p style="margin:0 0 22px;font-size:13px;color:#66766C;">Proyek: <strong
            style="color:#103B2B;">{{ $proyek }}</strong></p>

    <p style="margin:0 0 14px;">Halo <strong style="color:#103B2B;">{{ $nama }}</strong>,</p>
    <p style="margin:0 0 18px;">Kabar baik, permintaan akses data Anda telah <strong style="color:#174C35;">disetujui</strong>
        oleh administrator. Token akses Anda ada di bawah ini.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:0 0 8px;border-left:4px solid #C5A66A;background-color:#FBF6EA;">
        <tr>
            <td style="padding:14px 18px;font-size:13px;line-height:1.7;color:#5B4A22;">
                <strong>Simpan di tempat aman.</strong> Token hanya ditampilkan sekali di email ini dan tidak bisa dilihat
                lagi. Setiap token hanya bisa dipakai login portal <strong>{{ $limit }} kali</strong>. Jika sudah
                habis, hubungi admin di <a href="mailto:{{ $adminMail }}" style="color:#174C35;">{{ $adminMail }}</a>
                dan admin akan mengirimkan token baru.
            </td>
        </tr>
    </table>

    @foreach ($tokens as $item)
        <div
            style="margin:26px 0 0;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#174C35;">
            Tabel · {{ $item['table'] }}</div>
        @include('emails.data-access.partials.keybox', [
            'label' => 'Token akses',
            'value' => $item['token'],
            'size' => 13,
            'spacing' => 0,
            'note' =>
                'Awalan ' .
                $item['prefix'] .
                ' · Berlaku sampai ' .
                ($item['expires_at']
                    ? \Carbon\Carbon::parse($item['expires_at'])->format('d M Y, H:i')
                    : 'tanpa batas waktu'),
        ])
    @endforeach

    @include('emails.data-access.partials.button', ['url' => $portal, 'label' => 'Buka Portal Data'])

    <h2 style="margin:8px 0 10px;font-family:Georgia,'Times New Roman',serif;font-size:19px;color:#103B2B;">Cara memakai
    </h2>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="34" valign="top" style="padding:6px 0;">
                <div
                    style="width:24px;height:24px;line-height:24px;text-align:center;background-color:#174C35;color:#F5F1E7;font-size:12px;font-weight:700;border-radius:12px;">
                    1</div>
            </td>
            <td style="padding:6px 0;">Buka portal lewat tombol di atas.</td>
        </tr>
        <tr>
            <td width="34" valign="top" style="padding:6px 0;">
                <div
                    style="width:24px;height:24px;line-height:24px;text-align:center;background-color:#174C35;color:#F5F1E7;font-size:12px;font-weight:700;border-radius:12px;">
                    2</div>
            </td>
            <td style="padding:6px 0;">Salin seluruh token lalu tempel pada kolom token dan klik verifikasi.</td>
        </tr>
        <tr>
            <td width="34" valign="top" style="padding:6px 0;">
                <div
                    style="width:24px;height:24px;line-height:24px;text-align:center;background-color:#174C35;color:#F5F1E7;font-size:12px;font-weight:700;border-radius:12px;">
                    3</div>
            </td>
            <td style="padding:6px 0;">Pilih tabel yang ingin dilihat atau diunduh. Token tabel lain dimasukkan satu per
                satu lewat tombol tambah token di portal.</td>
        </tr>
    </table>

    @isset($apiBase)
        <h2 style="margin:22px 0 10px;font-family:Georgia,'Times New Roman',serif;font-size:19px;color:#103B2B;">Contoh
            pemakaian API</h2>
        <div
            style="padding:14px 16px;background-color:#103B2B;color:#DCE6D8;font-family:'SFMono-Regular',Consolas,Menlo,monospace;font-size:12px;line-height:1.7;border-radius:4px;word-break:break-all;">
            curl -H "Authorization: Bearer TOKEN_ANDA"
            "{{ rtrim($apiBase, '/') }}/{{ collect($tokens)->first()['table'] ?? 'nama_tabel' }}"</div>
    @endisset

    <p style="margin:24px 0 0;font-size:13px;color:#66766C;">Jangan membagikan token ini kepada pihak lain. Setiap akses
        tercatat pada sistem kami.</p>
@endsection
