@component('mail::message')
# ✅ Permintaan Akses Data Disetujui

Halo **{{ $request->requester_name }}**,

Permintaan akses data untuk proyek **{{ $projectName }}** telah **DISETUJUI** oleh administrator.

---

## 🔑 Token Akses Anda

**⚠️ PENTING: Simpan token ini di tempat aman!**  
Token hanya ditampilkan **SEKALI** dan tidak dapat dilihat kembali.

@foreach($tokens as $token)
### 📊 Tabel: {{ $token['table'] }}

**Token:**  
@component('mail::panel')
{{ $token['token'] }}
@endcomponent

**Token Prefix:** `{{ $token['prefix'] }}`  
**Masa Berlaku:** {{ $token['expires_at'] ? \Carbon\Carbon::parse($token['expires_at'])->format('d M Y, H:i') : 'Tidak ada batas' }}

@endforeach

---

## 📖 Cara Penggunaan

### Endpoint API