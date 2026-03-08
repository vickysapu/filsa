@extends('layouts.admin')

@section('title', 'Tambah Pemasukan')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('admin.pemasukan.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Tambah Pemasukan</h1>
            <p class="text-muted">Catat pemasukan keuangan gereja yang baru</p>
        </div>

        <!-- Alert Error -->
        @if ($errors->any())
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <div style="color: #991b1b; display: flex; align-items: start; gap: 10px;">
                    <i class="ri-error-warning-line" style="font-size: 1.2rem; margin-top: 2px;"></i>
                    <div>
                        <strong>Terjadi kesalahan validasi</strong>
                        <ul style="margin: 5px 0 0 20px; font-size: 0.9rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.pemasukan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="lux-grid lux-grid-2">

                <!-- Kolom Kiri: Informasi Transaksi -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Transaksi</h3>
                        <i class="ri-wallet-line" style="color: var(--primary);"></i>
                    </div>

                    <!-- Tanggal -->
                    <div class="lux-form-group">
                        <label class="lux-label">Tanggal Transaksi *</label>
                        <input type="date" name="tanggal" class="lux-input @error('tanggal') lux-input-error @enderror"
                            value="{{ old('tanggal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                        @error('tanggal')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                        <p class="lux-form-help"><i class="ri-information-line"></i> Tanggal tidak boleh lebih dari hari ini
                        </p>
                    </div>

                    <!-- Kategori -->
                    <div class="lux-form-group">
                        <label class="lux-label">Kategori Pemasukan *</label>
                        <select name="kategori" class="lux-select @error('kategori') lux-input-error @enderror">
                            <option value="">Pilih Kategori...</option>
                            <option value="persembahan_minggu"
                                {{ old('kategori') == 'persembahan_minggu' ? 'selected' : '' }}>Persembahan Minggu
                            </option>
                            <option value="persembahan_khusus"
                                {{ old('kategori') == 'persembahan_khusus' ? 'selected' : '' }}>Persembahan Khusus
                            </option>
                            <option value="donasi" {{ old('kategori') == 'donasi' ? 'selected' : '' }}>Donasi</option>
                            <option value="kegiatan" {{ old('kategori') == 'kegiatan' ? 'selected' : '' }}>Kegiatan
                            </option>
                            <option value="usaha_mandiri" {{ old('kategori') == 'usaha_mandiri' ? 'selected' : '' }}>Usaha Mandiri</option>
                            <option value="lainnya" {{ old('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('kategori')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber Dana -->
                    <div class="lux-form-group">
                        <label class="lux-label">Sumber Dana *</label>
                        <input type="text" name="sumber_dana"
                            class="lux-input @error('sumber_dana') lux-input-error @enderror"
                            value="{{ old('sumber_dana') }}"
                            placeholder="Contoh: Kolekte Minggu I, Donasi Bapak John, dll">
                        @error('sumber_dana')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah -->
                    <div class="lux-form-group">
                        <label class="lux-label">Jumlah (Rp) *</label>
                        <div style="position: relative;">
                            <span
                                style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-weight: 600;">Rp</span>
                            <input type="number" name="jumlah"
                                class="lux-input @error('jumlah') lux-input-error @enderror" value="{{ old('jumlah') }}"
                                placeholder="1.000.000" min="1000" step="1000"
                                style="padding-left: 40px; font-weight: 600; font-size: 1.1rem;">
                        </div>
                        @error('jumlah')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                        <p class="lux-form-help"><i class="ri-information-line"></i> Minimal Rp 1.000</p>
                    </div>

                    <!-- Status -->
                    <div class="lux-form-group">
                        <label class="lux-label">Status Awal *</label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                <input type="radio" name="status" value="pending"
                                    {{ old('status', 'pending') == 'pending' ? 'checked' : '' }}
                                    style="accent-color: var(--warning); width: 16px; height: 16px;">
                                <span>Pending (Perlu Verifikasi)</span>
                            </label>
                            @if (auth()->user()->hasPermission('pemasukan.verify'))
                                <label
                                    style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                    <input type="radio" name="status" value="verified"
                                        {{ old('status') == 'verified' ? 'checked' : '' }}
                                        style="accent-color: var(--success); width: 16px; height: 16px;">
                                    <span>Verified (Langsung Terverifikasi)</span>
                                </label>
                            @endif
                        </div>
                        @error('status')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Kolom Kanan: Detail & Bukti -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Detail & Bukti</h3>
                        <i class="ri-file-text-line" style="color: var(--accent);"></i>
                    </div>

                    <!-- Keterangan -->
                    <div class="lux-form-group">
                        <label class="lux-label">Keterangan</label>
                        <textarea name="keterangan" class="lux-textarea @error('keterangan') lux-input-error @enderror" rows="4"
                            placeholder="Deskripsi tambahan tentang pemasukan ini...">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                        <p class="lux-form-help"><i class="ri-information-line"></i> Opsional, maksimal 1000 karakter</p>
                    </div>

                    <!-- Bukti Foto -->
                    <div class="lux-form-group">
                        <label class="lux-label">Bukti Foto/Document</label>
                        <div style="border: 2px dashed #e2e8f0; border-radius: var(--radius); padding: 2rem; text-align: center; 
                                transition: var(--transition);"
                            onmouseover="this.style.borderColor='var(--primary)'"
                            onmouseout="this.style.borderColor='#e2e8f0'">
                            <input type="file" name="bukti_foto"
                                class="lux-input @error('bukti_foto') lux-input-error @enderror" accept="image/*,.pdf"
                                style="display: none;" id="bukti_foto_input">
                            <label for="bukti_foto_input" style="cursor: pointer;">
                                <i class="ri-upload-cloud-2-line"
                                    style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                <p style="font-weight: 600; color: var(--text-main);">Klik untuk upload bukti</p>
                                <p class="text-muted" style="font-size: 0.85rem;">JPG, PNG, atau PDF (Max 5MB)</p>
                            </label>
                            <div id="file_preview" style="margin-top: 1rem; display: none;">
                                <p style="color: var(--success); font-weight: 600;">
                                    <i class="ri-checkbox-circle-line"></i> File terpilih: <span id="file_name"></span>
                                </p>
                            </div>
                        </div>
                        @error('bukti_foto')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Info Box -->
                    <div
                        style="margin-top: 2rem; padding: 1rem; background: #f0f9ff; border: 1px solid #bae6fd; 
                            border-radius: var(--radius);">
                        <div style="display: flex; align-items: start; gap: 10px;">
                            <i class="ri-information-line"
                                style="color: var(--primary); font-size: 1.2rem; margin-top: 2px;"></i>
                            <div>
                                <p style="font-weight: 600; color: var(--primary); margin-bottom: 0.5rem;">Informasi
                                    Penting</p>
                                <ul style="font-size: 0.85rem; color: var(--text-muted); margin: 0; padding-left: 1.2rem;">
                                    <li>Nomor transaksi akan digenerate otomatis</li>
                                    <li>Pemasukan pending perlu diverifikasi oleh admin</li>
                                    <li>Simpan bukti fisik untuk arsip gereja</li>
                                    <li>Data yang sudah verified tidak dapat diedit</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.pemasukan.index') }}" class="lux-btn lux-btn-outline">Batal</a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Simpan Pemasukan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Preview file name saat upload
        document.getElementById('bukti_foto_input').addEventListener('change', function(e) {
            const preview = document.getElementById('file_preview');
            const fileName = document.getElementById('file_name');

            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2);

                fileName.textContent = file.name + ' (' + fileSize + ' MB)';
                preview.style.display = 'block';

                // Validasi ukuran file client-side
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB');
                    this.value = '';
                    preview.style.display = 'none';
                }
            }
        });

        // Format input jumlah dengan separator ribuan
        document.querySelector('input[name="jumlah"]').addEventListener('input', function(e) {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
@endpush
