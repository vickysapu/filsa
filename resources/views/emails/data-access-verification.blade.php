@component('mail::message')
    # 🔐 Verifikasi Permintaan Akses Data

    Halo **{{ $requester_name }}**,

    Terima kasih telah mengajukan permintaan akses data untuk proyek:

    > **{{ $projectName }}**

    ---

    ### 📋 Detail Permintaan

    **Tujuan Penggunaan:**
    {{ $purpose }}

    **Tabel yang Diminta:**
    @foreach ($tables as $table)
        - {{ $table }}
    @endforeach

    ---

    ### ✅ Langkah Verifikasi

    Untuk melanjutkan proses approval, silakan verifikasi email Anda dengan mengklik tombol di bawah:

    @component('mail::button', ['url' => $verificationUrl, 'color' => 'primary'])
        Verifikasi Email Saya
    @endcomponent

    > ⚠️ **Penting:**
    > - Link verifikasi ini akan kadaluarsa dalam **{{ $expiresIn }}**
    > - Jika Anda tidak mengajukan permintaan ini, abaikan email ini
    > - Token akses hanya akan dikirim setelah email terverifikasi dan disetujui administrator

    ---

    ### 🔒 Keamanan Data

    Sistem kami menggunakan enkripsi tingkat tinggi untuk melindungi data Anda:
    - ✅ Token akses di-hash dengan SHA-512
    - ✅ Response data dienkripsi end-to-end
    - ✅ Audit log untuk setiap akses data

    Salam,
    **Tim Keamanan Data**
    {{ config('app.name') }}

    @component('mail::subcopy')
        **Jika tombol di atas tidak berfungsi**, salin dan tempel URL berikut ke browser Anda:
        {{ $verificationUrl }}
    @endcomponent
@endcomponent
