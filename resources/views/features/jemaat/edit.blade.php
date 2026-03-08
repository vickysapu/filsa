@extends('layouts.admin')

@section('title', 'Edit Data Jemaat')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('admin.jemaat.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Edit Data Jemaat</h1>
            <p class="text-muted">Memperbarui data: <strong>{{ $jemaat->nama_lengkap }}</strong></p>
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

        <form action="{{ route('admin.jemaat.update', $jemaat) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="lux-grid lux-grid-2">

                <!-- Kolom Kiri: Identitas Diri -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Identitas Diri</h3>
                        <i class="ri-user-line" style="color: var(--primary);"></i>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="lux-form-group">
                        <label class="lux-label">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap"
                            class="lux-input @error('nama_lengkap') lux-input-error @enderror"
                            value="{{ old('nama_lengkap', $jemaat->nama_lengkap) }}" placeholder="Sesuai KTP">
                        @error('nama_lengkap')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIK (Readonly) -->
                    <div class="lux-form-group">
                        <label class="lux-label">NIK</label>
                        <input type="number" name="NIK" class="lux-input" value="{{ old('NIK', $jemaat->NIK) }}"
                            readonly style="background: #f1f5f9; cursor: not-allowed; color: var(--text-muted);">
                        <p class="lux-form-help"><i class="ri-information-line"></i> NIK tidak dapat diubah</p>
                    </div>

                    <!-- Tempat & Tanggal Lahir -->
                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Tempat Lahir *</label>
                            <input type="text" name="tempat_lahir"
                                class="lux-input @error('tempat_lahir') lux-input-error @enderror"
                                value="{{ old('tempat_lahir', $jemaat->tempat_lahir) }}">
                            @error('tempat_lahir')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Tanggal Lahir *</label>
                            <input type="date" name="tanggal_lahir"
                                class="lux-input @error('tanggal_lahir') lux-input-error @enderror"
                                value="{{ old('tanggal_lahir', $jemaat->tanggal_lahir) }}">
                            @error('tanggal_lahir')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Jenis Kelamin (Boolean: 1=L, 0=P) -->
                    <div class="lux-form-group">
                        <label class="lux-label">Jenis Kelamin *</label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                <input type="radio" name="jenis_kelamin" value="1"
                                    {{ old('jenis_kelamin', $jemaat->jenis_kelamin) == 1 ? 'checked' : '' }}
                                    style="accent-color: var(--primary); width: 16px; height: 16px;">
                                <span>Laki-laki</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                <input type="radio" name="jenis_kelamin" value="0"
                                    {{ old('jenis_kelamin', $jemaat->jenis_kelamin) == 0 ? 'checked' : '' }}
                                    style="accent-color: var(--primary); width: 16px; height: 16px;">
                                <span>Perempuan</span>
                            </label>
                        </div>
                        @error('jenis_kelamin')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Update Foto -->
                    <div class="lux-form-group">
                        <label class="lux-label">Update Foto</label>
                        <input type="file" name="foto" class="lux-input @error('foto') lux-input-error @enderror"
                            accept="image/*">
                        @error('foto')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror

                        @if ($jemaat->foto)
                            <div style="margin-top: 10px;">
                                <p class="lux-label">Foto Saat Ini:</p>
                                <img src="{{ asset('storage/' . $jemaat->foto) }}" alt="{{ $jemaat->nama_lengkap }}"
                                    style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius); border: 2px solid #e2e8f0;">
                            </div>
                        @endif
                        <p class="lux-form-help"><i class="ri-information-line"></i> Kosongkan jika tidak ingin mengubah
                            foto</p>
                    </div>
                </div>

                <!-- Kolom Kanan: Kontak & Data Tambahan -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Kontak & Data</h3>
                        <i class="ri-phone-line" style="color: var(--accent);"></i>
                    </div>

                    <!-- Email & No HP -->
                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Email</label>
                            <input type="email" name="email" class="lux-input @error('email') lux-input-error @enderror"
                                value="{{ old('email', $jemaat->email) }}" placeholder="email@contoh.com">
                            @error('email')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">No. HP *</label>
                            <input type="tel" name="no_hp"
                                class="lux-input @error('no_hp') lux-input-error @enderror"
                                value="{{ old('no_hp', $jemaat->no_hp) }}" placeholder="08123456789">
                            @error('no_hp')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="lux-form-group">
                        <label class="lux-label">Alamat Lengkap *</label>
                        <textarea name="alamat" class="lux-textarea @error('alamat') lux-input-error @enderror" rows="3"
                            placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan">{{ old('alamat', $jemaat->alamat) }}</textarea>
                        @error('alamat')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 1.5rem 0;">

                    <!-- Pendidikan & Pekerjaan -->
                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Pendidikan Terakhir</label>
                            <select name="pendidikan" class="lux-select @error('pendidikan') lux-input-error @enderror">
                                <option value="">Pilih...</option>
                                <option value="SD"
                                    {{ old('pendidikan', $jemaat->pendidikan) == 'SD' ? 'selected' : '' }}>SD / Sederajat
                                </option>
                                <option value="SMP"
                                    {{ old('pendidikan', $jemaat->pendidikan) == 'SMP' ? 'selected' : '' }}>SMP / Sederajat
                                </option>
                                <option value="SMA"
                                    {{ old('pendidikan', $jemaat->pendidikan) == 'SMA' ? 'selected' : '' }}>SMA / SMK /
                                    Sederajat</option>
                                <option value="D3"
                                    {{ old('pendidikan', $jemaat->pendidikan) == 'D3' ? 'selected' : '' }}>Diploma (D3)
                                </option>
                                <option value="S1"
                                    {{ old('pendidikan', $jemaat->pendidikan) == 'S1' ? 'selected' : '' }}>Sarjana (S1)
                                </option>
                                <option value="S2"
                                    {{ old('pendidikan', $jemaat->pendidikan) == 'S2' ? 'selected' : '' }}>Magister (S2)
                                </option>
                                <option value="S3"
                                    {{ old('pendidikan', $jemaat->pendidikan) == 'S3' ? 'selected' : '' }}>Doktor (S3)
                                </option>
                            </select>
                            @error('pendidikan')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan"
                                class="lux-input @error('pekerjaan') lux-input-error @enderror"
                                value="{{ old('pekerjaan', $jemaat->pekerjaan) }}" placeholder="Contoh: Wiraswasta">
                            @error('pekerjaan')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status Pernikahan -->
                    <div class="lux-form-group">
                        <label class="lux-label">Status Pernikahan *</label>
                        <select name="status_pernikahan"
                            class="lux-select @error('status_pernikahan') lux-input-error @enderror">
                            <option value="">Pilih...</option>
                            <option value="belum menikah"
                                {{ old('status_pernikahan', $jemaat->status_pernikahan) == 'belum menikah' ? 'selected' : '' }}>
                                Belum Menikah</option>
                            <option value="menikah"
                                {{ old('status_pernikahan', $jemaat->status_pernikahan) == 'menikah' ? 'selected' : '' }}>
                                Menikah</option>
                            <option value="janda"
                                {{ old('status_pernikahan', $jemaat->status_pernikahan) == 'janda' ? 'selected' : '' }}>
                                Janda</option>
                            <option value="duda"
                                {{ old('status_pernikahan', $jemaat->status_pernikahan) == 'duda' ? 'selected' : '' }}>Duda
                            </option>
                        </select>
                        @error('status_pernikahan')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Bergabung -->
                    <div class="lux-form-group">
                        <label class="lux-label">Tanggal Bergabung *</label>
                        <input type="date" name="tanggal_bergabung"
                            class="lux-input @error('tanggal_bergabung') lux-input-error @enderror"
                            value="{{ old('tanggal_bergabung', $jemaat->tanggal_bergabung->format('Y-m-d')) }}">
                        @error('tanggal_bergabung')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Jemaat (Boolean: 1=Aktif, 0=Non-Aktif) -->
                    <div class="lux-form-group"
                        style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                        {{-- Hidden input untuk menangani checkbox boolean --}}
                        <input type="hidden" name="status_jemaat" value="0">

                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                            <input type="checkbox" name="status_jemaat" value="1"
                                {{ old('status_jemaat', $jemaat->status_jemaat) ? 'checked' : '' }}
                                style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <span>Status Jemaat Aktif</span>
                        </label>
                        <p class="text-muted" style="font-size: 0.8rem; margin: 5px 0 0 28px;">
                            Hilangkan centang jika jemaat pindah gereja atau non-aktif sementara.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.jemaat.index') }}" class="lux-btn lux-btn-outline">Batal</a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Update Data
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Format input NIK dan No HP agar hanya angka
        document.querySelectorAll('input[type="number"], input[type="tel"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
@endpush
