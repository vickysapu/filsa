@extends('layouts.admin')

@section('title', 'Edit Pengeluaran')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('admin.pengeluaran.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Edit Pengeluaran</h1>
            <p class="text-muted">
                Transaksi: <strong style="color: var(--danger);">{{ $pengeluaran->nomor_transaksi }}</strong>
            </p>
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

        <!-- Warning if status is verified -->
        @if ($pengeluaran->status === 'verified')
            <div class="lux-card mb-4" style="border-left: 4px solid var(--warning); background: #fef9c3;">
                <div style="color: #854d0e; display: flex; align-items: start; gap: 10px;">
                    <i class="ri-alert-line" style="font-size: 1.2rem; margin-top: 2px;"></i>
                    <div>
                        <strong>Perhatian!</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                            Pengeluaran ini sudah <strong>verified</strong>. Editing dibatasi untuk menjaga integritas data
                            keuangan.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.pengeluaran.update', $pengeluaran) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="lux-grid lux-grid-2">

                <!-- Kolom Kiri: Informasi Transaksi -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Transaksi</h3>
                        <i class="ri-wallet-3-line" style="color: var(--danger);"></i>
                    </div>

                    <!-- Nomor Transaksi (Readonly) -->
                    <div class="lux-form-group">
                        <label class="lux-label">Nomor Transaksi</label>
                        <input type="text" class="lux-input" value="{{ $pengeluaran->nomor_transaksi }}" readonly
                            style="background: #f1f5f9; cursor: not-allowed; color: var(--text-muted); font-family: monospace; font-weight: 600;">
                        <p class="lux-form-help"><i class="ri-information-line"></i> Tidak dapat diubah</p>
                    </div>

                    <!-- Tanggal -->
                    <div class="lux-form-group">
                        <label class="lux-label">Tanggal Transaksi *</label>
                        <input type="date" name="tanggal" class="lux-input @error('tanggal') lux-input-error @enderror"
                            value="{{ old('tanggal', $pengeluaran->tanggal->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                        @error('tanggal')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kategori -->
                    <div class="lux-form-group">
                        <label class="lux-label">Kategori Pengeluaran *</label>
                        <select name="kategori" class="lux-select @error('kategori') lux-input-error @enderror">
                            <option value="">Pilih Kategori...</option>
                            <option value="operasional"
                                {{ old('kategori', $pengeluaran->kategori) == 'operasional' ? 'selected' : '' }}>🏢
                                Operasional</option>
                            <option value="listrik_air"
                                {{ old('kategori', $pengeluaran->kategori) == 'listrik_air' ? 'selected' : '' }}>💡 Listrik
                                & Air</option>
                            <option value="gaji_karyawan"
                                {{ old('kategori', $pengeluaran->kategori) == 'gaji_karyawan' ? 'selected' : '' }}>💰 Gaji
                                Karyawan</option>
                            <option value="kegiatan"
                                {{ old('kategori', $pengeluaran->kategori) == 'kegiatan' ? 'selected' : '' }}>🎉 Kegiatan
                            </option>
                            <option value="pemeliharaan"
                                {{ old('kategori', $pengeluaran->kategori) == 'pemeliharaan' ? 'selected' : '' }}>🔧
                                Pemeliharaan</option>
                            <option value="amal_sosial"
                                {{ old('kategori', $pengeluaran->kategori) == 'amal_sosial' ? 'selected' : '' }}>🤝 Amal &
                                Sosial</option>
                            <option value="konsumsi"
                                {{ old('kategori', $pengeluaran->kategori) == 'konsumsi' ? 'selected' : '' }}>🍽️ Konsumsi
                            </option>
                            <option value="lainnya"
                                {{ old('kategori', $pengeluaran->kategori) == 'lainnya' ? 'selected' : '' }}>📌 Lainnya
                            </option>
                        </select>
                        @error('kategori')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keperluan -->
                    <div class="lux-form-group">
                        <label class="lux-label">Keperluan *</label>
                        <input type="text" name="keperluan"
                            class="lux-input @error('keperluan') lux-input-error @enderror"
                            value="{{ old('keperluan', $pengeluaran->keperluan) }}"
                            placeholder="Contoh: Pembelian alat kebersihan">
                        @error('keperluan')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Penerima -->
                    <div class="lux-form-group">
                        <label class="lux-label">Penerima Dana *</label>
                        <input type="text" name="penerima" class="lux-input @error('penerima') lux-input-error @enderror"
                            value="{{ old('penerima', $pengeluaran->penerima) }}"
                            placeholder="Nama toko, vendor, atau penerima">
                        @error('penerima')
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
                                class="lux-input @error('jumlah') lux-input-error @enderror"
                                value="{{ old('jumlah', $pengeluaran->jumlah) }}" placeholder="1.000.000" min="1000"
                                step="1000" style="padding-left: 40px; font-weight: 600; font-size: 1.1rem;">
                        </div>
                        @error('jumlah')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Kolom Kanan: Detail & Bukti -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Detail & Bukti</h3>
                        <i class="ri-receipt-line" style="color: var(--accent);"></i>
                    </div>

                    <!-- Keterangan -->
                    <div class="lux-form-group">
                        <label class="lux-label">Keterangan</label>
                        <textarea name="keterangan" class="lux-textarea @error('keterangan') lux-input-error @enderror" rows="4"
                            placeholder="Deskripsi tambahan tentang pengeluaran ini...">{{ old('keterangan', $pengeluaran->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bukti Foto -->
                    <div class="lux-form-group">
                        <label class="lux-label">Bukti Foto/Document</label>

                        @if ($pengeluaran->bukti_foto)
                            <div
                                style="margin-bottom: 1rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                                <p class="lux-label" style="margin-bottom: 0.5rem;">Bukti Saat Ini:</p>
                                @if (pathinfo($pengeluaran->bukti_foto, PATHINFO_EXTENSION) === 'pdf')
                                    <div
                                        style="display: flex; align-items: center; gap: 10px; padding: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 8px;">
                                        <i class="ri-file-pdf-line" style="font-size: 2rem; color: var(--danger);"></i>
                                        <div>
                                            <p style="font-weight: 600;">{{ basename($pengeluaran->bukti_foto) }}</p>
                                            <p class="text-muted" style="font-size: 0.85rem;">PDF Document</p>
                                        </div>
                                        <a href="{{ asset('storage/' . $pengeluaran->bukti_foto) }}" target="_blank"
                                            class="lux-btn lux-btn-outline lux-btn-sm" style="margin-left: auto;">
                                            <i class="ri-eye-line"></i> Lihat
                                        </a>
                                    </div>
                                @else
                                    <img src="{{ asset('storage/' . $pengeluaran->bukti_foto) }}" alt="Bukti"
                                        style="max-width: 200px; border-radius: var(--radius); border: 2px solid #e2e8f0;">
                                @endif
                            </div>
                        @endif

                        <div style="border: 2px dashed #e2e8f0; border-radius: var(--radius); padding: 2rem; text-align: center; 
                                transition: var(--transition);"
                            onmouseover="this.style.borderColor='var(--danger)'"
                            onmouseout="this.style.borderColor='#e2e8f0'">
                            <input type="file" name="bukti_foto"
                                class="lux-input @error('bukti_foto') lux-input-error @enderror" accept="image/*,.pdf"
                                style="display: none;" id="bukti_foto_input">
                            <label for="bukti_foto_input" style="cursor: pointer;">
                                <i class="ri-upload-cloud-2-line"
                                    style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                <p style="font-weight: 600; color: var(--text-main);">Upload bukti baru (opsional)</p>
                                <p class="text-muted" style="font-size: 0.85rem;">Struk, faktur, atau kwitansi (Max 5MB)
                                </p>
                            </label>
                            <div id="file_preview" style="margin-top: 1rem; display: none;">
                                <p style="color: var(--success); font-weight: 600;">
                                    <i class="ri-checkbox-circle-line"></i> File baru: <span id="file_name"></span>
                                </p>
                            </div>
                        </div>
                        @error('bukti_foto')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                        <p class="lux-form-help"><i class="ri-information-line"></i> Kosongkan jika tidak ingin mengubah
                            bukti</p>
                    </div>

                    <!-- Info Timeline -->
                    <div
                        style="margin-top: 2rem; padding: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; 
                            border-radius: var(--radius);">
                        <p class="lux-label" style="margin-bottom: 1rem;">Timeline Transaksi</p>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Dibuat:</span>
                                <span style="font-weight: 600; color: var(--text-main);">
                                    {{ $pengeluaran->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Oleh:</span>
                                <span style="font-weight: 600; color: var(--text-main);">
                                    {{ $pengeluaran->creator->name ?? '-' }}
                                </span>
                            </div>
                            @if ($pengeluaran->verified_at)
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Verified:</span>
                                    <span style="font-weight: 600; color: var(--success);">
                                        {{ $pengeluaran->verified_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.pengeluaran.index') }}" class="lux-btn lux-btn-outline">Batal</a>
                <button type="submit" class="lux-btn lux-btn-primary" style="background: var(--danger);">
                    <i class="ri-save-line"></i> Update Pengeluaran
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

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB');
                    this.value = '';
                    preview.style.display = 'none';
                }
            }
        });

        // Format input jumlah
        document.querySelector('input[name="jumlah"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
@endpush
