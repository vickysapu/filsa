@extends('layouts.admin')

@section('title', 'Buat Album Dokumentasi')

@section('content')
    <div class="lux-container">

        <div class="mb-5">
            <a href="{{ route('admin.dokumentasi.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Buat Album Dokumentasi
            </h1>
        </div>

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

        <form action="{{ route('admin.dokumentasi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="lux-grid lux-grid-2">

                <!-- Kolom Kiri: Info Album -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Album</h3>
                        <i class="ri-image-line" style="color: var(--primary);"></i>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Judul Album *</label>
                        <input type="text" name="judul_album"
                            class="lux-input @error('judul_album') lux-input-error @enderror"
                            value="{{ old('judul_album') }}" placeholder="Contoh: Kebaktian Natal 2024">
                        @error('judul_album')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Deskripsi</label>
                        <textarea name="deskripsi" class="lux-textarea @error('deskripsi') lux-input-error @enderror" rows="3"
                            placeholder="Deskripsi album...">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Kategori</label>
                            <select name="kategori_id" class="lux-select">
                                <option value="">Tanpa Kategori</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Event (Opsional)</label>
                            <select name="event_id" class="lux-select">
                                <option value="">Tidak Ada</option>
                                @foreach ($events as $event)
                                    <option value="{{ $event->id }}"
                                        {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->nama_event }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Fotografer</label>
                            <input type="text" name="fotografer" class="lux-input" value="{{ old('fotografer') }}">
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Tanggal Album *</label>
                            <input type="date" name="tanggal_album"
                                class="lux-input @error('tanggal_album') lux-input-error @enderror"
                                value="{{ old('tanggal_album', date('Y-m-d')) }}">
                            @error('tanggal_album')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Lokasi</label>
                        <input type="text" name="lokasi" class="lux-input" value="{{ old('lokasi') }}">
                    </div>
                </div>

                <!-- Kolom Kanan: Upload Foto -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Upload Foto</h3>
                        <i class="ri-gallery-upload-line" style="color: var(--accent);"></i>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Foto Album *</label>
                        <input type="file" name="photos[]" class="lux-input @error('photos.*') lux-input-error @enderror"
                            accept="image/*" multiple required>
                        @error('photos.*')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                        <p class="lux-form-help"><i class="ri-information-line"></i> Pilih multiple files. Max 10MB per file
                        </p>
                    </div>

                    <div class="lux-form-group"
                        style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                        <label class="lux-label" style="margin-bottom: 1rem;">Opsi Tampilan</label>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="status" value="published"
                                    {{ old('status') == 'published' ? 'checked' : '' }}>
                                <span>Published (Langsung tampil)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="is_featured" value="1"
                                    {{ old('is_featured') ? 'checked' : '' }}>
                                <span>Featured Album</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="allow_download" value="1"
                                    {{ old('allow_download') ? 'checked' : '' }}>
                                <span>Allow Download</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="show_in_website" value="1"
                                    {{ old('show_in_website', true) ? 'checked' : '' }} checked>
                                <span>Tampilkan di Website</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.dokumentasi.index') }}" class="lux-btn lux-btn-outline">Batal</a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Simpan Album
                </button>
            </div>
        </form>
    </div>
@endsection
