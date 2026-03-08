@extends('layouts.admin')

@section('title', 'Edit Album')

@section('content')
    <div class="lux-container">

        <div class="mb-5">
            <a href="{{ route('admin.dokumentasi.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Edit Album</h1>
        </div>

        @if ($errors->any())
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <div style="color: #991b1b;">
                    <strong><i class="ri-error-warning-line"></i> Error</strong>
                    <ul style="margin: 5px 0 0 20px; font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.dokumentasi.update', $album) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="lux-grid lux-grid-2">
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Album</h3>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Judul Album *</label>
                        <input type="text" name="judul_album" class="lux-input"
                            value="{{ old('judul_album', $album->judul_album) }}" required>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Deskripsi</label>
                        <textarea name="deskripsi" class="lux-textarea" rows="3">{{ old('deskripsi', $album->deskripsi) }}</textarea>
                    </div>

                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Kategori</label>
                            <select name="kategori_id" class="lux-select">
                                <option value="">Tanpa Kategori</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        {{ old('kategori_id', $album->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Event</label>
                            <select name="event_id" class="lux-select">
                                <option value="">Tidak Ada</option>
                                @foreach ($events as $event)
                                    <option value="{{ $event->id }}"
                                        {{ old('event_id', $album->event_id) == $event->id ? 'selected' : '' }}>
                                        {{ $event->nama_event }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Fotografer</label>
                            <input type="text" name="fotografer" class="lux-input"
                                value="{{ old('fotografer', $album->fotografer) }}">
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Tanggal Album</label>
                            <input type="date" name="tanggal_album" class="lux-input"
                                value="{{ old('tanggal_album', $album->tanggal_album->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Lokasi</label>
                        <input type="text" name="lokasi" class="lux-input" value="{{ old('lokasi', $album->lokasi) }}">
                    </div>
                </div>

                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Upload Foto Baru</h3>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Tambah Foto</label>
                        <input type="file" name="photos[]" class="lux-input" accept="image/*" multiple>
                        <p class="lux-form-help">Max 10MB per file</p>
                    </div>

                    @if ($album->photos->count() > 0)
                        <div class="lux-form-group">
                            <label class="lux-label">Hapus Foto</label>
                            <div
                                style="max-height: 300px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: var(--radius); padding: 0.5rem;">
                                @foreach ($album->photos as $photo)
                                    <label
                                        style="display: flex; align-items: center; gap: 10px; padding: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                                        <input type="checkbox" name="delete_photos[]" value="{{ $photo->id }}">
                                        <img src="{{ asset('storage/' . $photo->file_path) }}"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        <span style="font-size: 0.85rem;">{{ $photo->file_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="lux-form-group"
                        style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                        <label class="lux-label" style="margin-bottom: 1rem;">Opsi</label>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="status" value="published"
                                    {{ old('status', $album->status) == 'published' ? 'checked' : '' }}>
                                <span>Published</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="is_featured" value="1"
                                    {{ old('is_featured', $album->is_featured) ? 'checked' : '' }}>
                                <span>Featured</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="show_in_website" value="1"
                                    {{ old('show_in_website', $album->show_in_website) ? 'checked' : '' }}>
                                <span>Show in Website</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.dokumentasi.index') }}" class="lux-btn lux-btn-outline">Batal</a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Update Album
                </button>
            </div>
        </form>
    </div>
@endsection
