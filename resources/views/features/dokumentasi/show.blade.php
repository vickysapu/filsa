@extends('layouts.admin')

@section('title', 'Detail Album')

@section('content')
    <div class="lux-container">

        <div class="mb-5">
            <a href="{{ route('admin.dokumentasi.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">{{ $album->judul_album }}
            </h1>
        </div>

        <div class="lux-grid lux-grid-3" style="gap: 1.5rem;">

            <!-- Photos Grid -->
            <div class="lux-card" style="grid-column: span 2;">
                <div class="lux-card-header">
                    <h3 class="lux-card-title">{{ $album->jumlah_foto }} Foto</h3>
                    @if (auth()->user()->hasPermission('dokumentasi.edit'))
                        <button class="lux-btn lux-btn-primary lux-btn-sm"
                            onclick="document.getElementById('uploadModal').style.display='block'">
                            <i class="ri-add-line"></i> Upload Foto
                        </button>
                    @endif
                </div>

                <div class="lux-grid lux-grid-3" style="gap: 0.5rem;">
                    @forelse($album->photos as $photo)
                        <div style="position: relative; group">
                            <img src="{{ asset('storage/' . $photo->file_path) }}" alt="{{ $photo->caption ?? 'Foto' }}"
                                style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                onclick="window.open(this.src, '_blank')">
                            @if ($photo->is_cover)
                                <span class="lux-badge"
                                    style="position: absolute; top: 5px; left: 5px; font-size: 0.65rem;">Cover</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted" style="grid-column: 1 / -1; text-align: center; padding: 2rem;">Belum ada foto
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- Sidebar Info -->
            <div>
                <div class="lux-card mb-4">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Info Album</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <p class="lux-label">Kategori</p>
                            <p style="font-weight: 600;">{{ $album->kategori->nama_kategori ?? '-' }}</p>
                        </div>
                        @if ($album->event)
                            <div>
                                <p class="lux-label">Event</p>
                                <p style="font-weight: 600;">{{ $album->event->nama_event }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="lux-label">Tanggal</p>
                            <p style="font-weight: 600;">{{ $album->tanggal_album->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="lux-label">Fotografer</p>
                            <p style="font-weight: 600;">{{ $album->fotografer ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="lux-label">Status</p>
                            <span
                                class="lux-badge {{ $album->status === 'published' ? 'lux-badge-success' : 'lux-badge-warning' }}">
                                {{ ucfirst($album->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Aksi</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @if (auth()->user()->hasPermission('dokumentasi.edit'))
                            <a href="{{ route('admin.dokumentasi.edit', $album) }}" class="lux-btn lux-btn-primary">
                                <i class="ri-pencil-line"></i> Edit Album
                            </a>
                        @endif
                        <button onclick="window.print()" class="lux-btn lux-btn-outline">
                            <i class="ri-printer-line"></i> Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);"
            onclick="document.getElementById('uploadModal').style.display='none'"></div>
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                background: white; border-radius: var(--radius); width: 90%; max-width: 500px;">
            <form action="{{ route('admin.dokumentasi.upload', $album) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0;">
                    <h3 style="font-weight: 700; margin: 0;">Upload Foto Baru</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <input type="file" name="photos[]" class="lux-input" accept="image/*" multiple required>
                    <p class="lux-form-help" style="margin-top: 0.5rem;">Max 10MB per file</p>
                </div>
                <div
                    style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('uploadModal').style.display='none'"
                        class="lux-btn lux-btn-outline">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
@endsection
