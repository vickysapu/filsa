@extends('layouts.admin')

@section('title', 'Dokumentasi & Gallery')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Dokumentasi</h1>
                <p class="text-muted">Gallery foto dan dokumentasi kegiatan gereja</p>
            </div>
            @if (auth()->user()->hasPermission('dokumentasi.create'))
                <a href="{{ route('admin.dokumentasi.create') }}" class="lux-btn lux-btn-primary">
                    <i class="ri-gallery-upload-line"></i> Buat Album Baru
                </a>
            @endif
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--success); background: #f0fdf4;">
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="lux-card mb-4">
            <form action="{{ route('admin.dokumentasi.index') }}" method="GET"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">

                <div>
                    <label class="lux-label">Kategori</label>
                    <select name="kategori_id" class="lux-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lux-label">Event</label>
                    <select name="event_id" class="lux-select">
                        <option value="">Semua Event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                {{ $event->nama_event }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lux-label">Status</label>
                    <select name="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published
                        </option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="Judul album, fotografer...">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="lux-btn lux-btn-primary" style="flex: 1;">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                    <a href="{{ route('admin.dokumentasi.index') }}" class="lux-btn lux-btn-outline" title="Reset">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Albums Grid -->
        <div class="lux-grid lux-grid-4">
            @forelse($albums as $album)
                <div class="lux-card" style="padding: 0; overflow: hidden; position: relative;">

                    <!-- Featured Badge -->
                    @if ($album->is_featured)
                        <div style="position: absolute; top: 0.75rem; left: 0.75rem; z-index: 10;">
                            <span class="lux-badge" style="background: var(--warning); color: white; font-size: 0.7rem;">
                                <i class="ri-star-line"></i>
                            </span>
                        </div>
                    @endif

                    <!-- Cover Image -->
                    @if ($album->cover_image)
                        <img src="{{ asset('storage/' . $album->cover_image) }}" alt="{{ $album->judul_album }}"
                            style="width: 100%; height: 180px; object-fit: cover;">
                    @else
                        <div
                            style="width: 100%; height: 180px; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                        display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="ri-image-line" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    @endif

                    <!-- Content -->
                    <div style="padding: 1rem;">
                        <!-- Status -->
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span
                                class="lux-badge {{ $album->status === 'published' ? 'lux-badge-success' : 'lux-badge-warning' }}"
                                style="font-size: 0.7rem;">
                                {{ ucfirst($album->status) }}
                            </span>
                            @if ($album->event)
                                <span class="lux-badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.7rem;">
                                    <i class="ri-calendar-line"></i> Event
                                </span>
                            @endif
                        </div>

                        <!-- Judul Album -->
                        <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.3;">
                            {{ $album->judul_album }}
                        </h4>

                        <!-- Info -->
                        <div
                            style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                            <span><i class="ri-image-line"></i> {{ $album->jumlah_foto }} foto</span>
                            <span><i class="ri-calendar-line"></i> {{ $album->tanggal_album->format('d M Y') }}</span>
                        </div>

                        <!-- Actions -->
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.dokumentasi.show', $album) }}"
                                class="lux-btn lux-btn-outline lux-btn-sm" style="flex: 1;">
                                <i class="ri-eye-line"></i>
                            </a>
                            @if (auth()->user()->hasPermission('dokumentasi.edit'))
                                <a href="{{ route('admin.dokumentasi.edit', $album) }}"
                                    class="lux-btn lux-btn-primary lux-btn-sm">
                                    <i class="ri-pencil-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--text-muted);">
                    <i class="ri-gallery-off-line" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.1rem;">Belum ada album dokumentasi</p>
                    @if (auth()->user()->hasPermission('dokumentasi.create'))
                        <a href="{{ route('admin.dokumentasi.create') }}" class="lux-btn lux-btn-primary"
                            style="margin-top: 1rem;">
                            <i class="ri-add-line"></i> Buat Album Pertama
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($albums->hasPages())
            <div style="margin-top: 2rem; display: flex; justify-content: center;">
                {{ $albums->links('pagination::simple-bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
