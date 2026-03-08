@extends('layouts.admin')

@section('title', 'Buat Event Baru')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('admin.events.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Buat Event Baru</h1>
            <p class="text-muted">Event akan ditampilkan di website public gereja</p>
        </div>

        <!-- Alert Error -->
        @if ($errors->any())
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <div style="color: #991b1b;">
                    <strong><i class="ri-error-warning-line"></i> Terjadi kesalahan validasi</strong>
                    <ul style="margin: 5px 0 0 20px; font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="lux-grid lux-grid-2">

                <!-- Kolom Kiri: Informasi Event -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Event</h3>
                        <i class="ri-calendar-event-line" style="color: var(--primary);"></i>
                    </div>

                    <!-- Nama Event -->
                    <div class="lux-form-group">
                        <label class="lux-label">Nama Event *</label>
                        <input type="text" name="nama_event"
                            class="lux-input @error('nama_event') lux-input-error @enderror" value="{{ old('nama_event') }}"
                            placeholder="Contoh: Kebaktian Raya Natal 2024">
                        @error('nama_event')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kategori -->
                    <div class="lux-form-group">
                        <label class="lux-label">Kategori Event *</label>
                        <select name="kategori_id" class="lux-select @error('kategori_id') lux-input-error @enderror">
                            <option value="">Pilih Kategori...</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="lux-form-group">
                        <label class="lux-label">Deskripsi Singkat *</label>
                        <textarea name="deskripsi" class="lux-textarea @error('deskripsi') lux-input-error @enderror" rows="3"
                            placeholder="Deskripsi singkat untuk card preview (max 500 karakter)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi Lengkap -->
                    <div class="lux-form-group">
                        <label class="lux-label">Deskripsi Lengkap</label>
                        <textarea name="deskripsi_lengkap" class="lux-textarea @error('deskripsi_lengkap') lux-input-error @enderror"
                            rows="5" placeholder="Deskripsi lengkap untuk halaman detail event">{{ old('deskripsi_lengkap') }}</textarea>
                        @error('deskripsi_lengkap')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal & Waktu -->
                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Tanggal Mulai *</label>
                            <input type="datetime-local" name="tanggal_mulai"
                                class="lux-input @error('tanggal_mulai') lux-input-error @enderror"
                                value="{{ old('tanggal_mulai') }}">
                            @error('tanggal_mulai')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Tanggal Selesai</label>
                            <input type="datetime-local" name="tanggal_selesai"
                                class="lux-input @error('tanggal_selesai') lux-input-error @enderror"
                                value="{{ old('tanggal_selesai') }}">
                            @error('tanggal_selesai')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div class="lux-form-group">
                        <label class="lux-label">Lokasi *</label>
                        <input type="text" name="lokasi" class="lux-input @error('lokasi') lux-input-error @enderror"
                            value="{{ old('lokasi', 'Gedung Utama') }}" placeholder="Contoh: Gedung Utama, Aula Lantai 2">
                        @error('lokasi')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alamat Lengkap -->
                    <div class="lux-form-group">
                        <label class="lux-label">Alamat Lengkap</label>
                        <textarea name="alamat_lengkap" class="lux-textarea @error('alamat_lengkap') lux-input-error @enderror" rows="2"
                            placeholder="Alamat lengkap untuk Google Maps">{{ old('alamat_lengkap') }}</textarea>
                        @error('alamat_lengkap')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Kolom Kanan: Detail & Kontak -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Detail & Kontak</h3>
                        <i class="ri-information-line" style="color: var(--accent);"></i>
                    </div>

                    <!-- Pembicara -->
                    <div class="lux-form-group">
                        <label class="lux-label">Pembicara/Narasumber</label>
                        <input type="text" name="pembicara"
                            class="lux-input @error('pembicara') lux-input-error @enderror" value="{{ old('pembicara') }}"
                            placeholder="Nama pembicara">
                        @error('pembicara')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kontak Person -->
                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Kontak Person</label>
                            <input type="text" name="kontak_person"
                                class="lux-input @error('kontak_person') lux-input-error @enderror"
                                value="{{ old('kontak_person') }}" placeholder="Nama CP">
                            @error('kontak_person')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">WhatsApp</label>
                            <input type="text" name="whatsapp_kontak"
                                class="lux-input @error('whatsapp_kontak') lux-input-error @enderror"
                                value="{{ old('whatsapp_kontak') }}" placeholder="08123456789">
                            @error('whatsapp_kontak')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Email Kontak -->
                    <div class="lux-form-group">
                        <label class="lux-label">Email Kontak</label>
                        <input type="email" name="email_kontak"
                            class="lux-input @error('email_kontak') lux-input-error @enderror"
                            value="{{ old('email_kontak') }}" placeholder="event@gereja.com">
                        @error('email_kontak')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Harga Tiket & Kuota -->
                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Harga Tiket (Rp)</label>
                            <input type="number" name="harga_tiket"
                                class="lux-input @error('harga_tiket') lux-input-error @enderror"
                                value="{{ old('harga_tiket', 0) }}" min="0" step="1000">
                            @error('harga_tiket')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Kuota Peserta</label>
                            <input type="number" name="kuota_peserta"
                                class="lux-input @error('kuota_peserta') lux-input-error @enderror"
                                value="{{ old('kuota_peserta') }}" min="1"
                                placeholder="Kosongkan jika unlimited">
                            @error('kuota_peserta')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Gambar Featured -->
                    <div class="lux-form-group">
                        <label class="lux-label">Gambar Featured *</label>
                        <input type="file" name="gambar_featured"
                            class="lux-input @error('gambar_featured') lux-input-error @enderror" accept="image/*">
                        @error('gambar_featured')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                        <p class="lux-form-help"><i class="ri-information-line"></i> JPG, PNG. Max 5MB. Rasio 16:9
                            direkomendasikan</p>
                    </div>

                    <!-- Gambar Galeri -->
                    <div class="lux-form-group">
                        <label class="lux-label">Gambar Galeri</label>
                        <input type="file" name="gambar_galeri[]"
                            class="lux-input @error('gambar_galeri.*') lux-input-error @enderror" accept="image/*"
                            multiple>
                        @error('gambar_galeri.*')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                        <p class="lux-form-help"><i class="ri-information-line"></i> Bisa pilih multiple files. Max 5MB
                            per file</p>
                    </div>

                    <!-- Status & Options -->
                    <div class="lux-form-group"
                        style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                        <label class="lux-label" style="margin-bottom: 1rem;">Opsi Tampilan</label>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="status" value="published"
                                    {{ old('status') == 'published' ? 'checked' : '' }}
                                    style="width: 18px; height: 18px; accent-color: var(--success);">
                                <span>Published (Langsung tampil di website)</span>
                            </label>

                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="is_featured" value="1"
                                    {{ old('is_featured') ? 'checked' : '' }}
                                    style="width: 18px; height: 18px; accent-color: var(--warning);">
                                <span>Featured Event (Tampil di homepage)</span>
                            </label>

                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="show_in_website" value="1"
                                    {{ old('show_in_website', true) ? 'checked' : '' }}
                                    style="width: 18px; height: 18px; accent-color: var(--primary);">
                                <span>Tampilkan di Website Public</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.events.index') }}" class="lux-btn lux-btn-outline">Batal</a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Simpan Event
                </button>
            </div>
        </form>
    </div>
@endsection
