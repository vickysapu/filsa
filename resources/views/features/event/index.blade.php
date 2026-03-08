@extends('layouts.admin')

@section('title', 'Manajemen Events')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Events</h1>
                <p class="text-muted">Kelola event gereja untuk website public</p>
            </div>
            @if (auth()->user()->hasPermission('event.create'))
                <a href="{{ route('admin.events.create') }}" class="lux-btn lux-btn-primary">
                    <i class="ri-calendar-add-line"></i> Buat Event Baru
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
            <form action="{{ route('admin.events.index') }}" method="GET"
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
                    <label class="lux-label">Status</label>
                    <select name="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published
                        </option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="Nama event, lokasi...">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="lux-btn lux-btn-primary" style="flex: 1;">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="lux-btn lux-btn-outline" title="Reset">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Events Grid -->
        <div class="lux-grid lux-grid-3">
            @forelse($events as $event)
                <div class="lux-card" style="padding: 0; overflow: hidden; position: relative;">

                    <!-- Featured Badge -->
                    @if ($event->is_featured)
                        <div style="position: absolute; top: 1rem; left: 1rem; z-index: 10;">
                            <span class="lux-badge" style="background: var(--warning); color: white;">
                                <i class="ri-star-line"></i> Featured
                            </span>
                        </div>
                    @endif

                    <!-- Status Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem; z-index: 10;">
                        <span class="lux-badge {{ $event->status_label['color'] }}">
                            {{ $event->status_label['label'] }}
                        </span>
                    </div>

                    <!-- Featured Image -->
                    @if ($event->gambar_featured)
                        <img src="{{ asset('storage/' . $event->gambar_featured) }}" alt="{{ $event->nama_event }}"
                            style="width: 100%; height: 200px; object-fit: cover;">
                    @else
                        <div
                            style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                        display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="ri-calendar-event-line" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                    @endif

                    <!-- Content -->
                    <div style="padding: 1.5rem;">
                        <!-- Kategori -->
                        <span class="lux-badge"
                            style="background: {{ $event->kategori->warna }}20; color: {{ $event->kategori->warna }}; margin-bottom: 0.75rem; display: inline-block;">
                            {{ $event->kategori->nama_kategori }}
                        </span>

                        <!-- Nama Event -->
                        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.75rem; line-height: 1.4;">
                            {{ $event->nama_event }}
                        </h3>

                        <!-- Deskripsi Singkat -->
                        <p class="text-muted"
                            style="font-size: 0.9rem; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $event->deskripsi }}
                        </p>

                        <!-- Info Grid -->
                        <div style="display: grid; gap: 0.5rem; margin-bottom: 1.5rem;">
                            <div
                                style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                                <i class="ri-calendar-line" style="color: var(--primary);"></i>
                                <span>{{ $event->tanggal_mulai->format('d M Y') }}</span>
                            </div>
                            <div
                                style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                                <i class="ri-time-line" style="color: var(--primary);"></i>
                                <span>{{ $event->tanggal_mulai->format('H:i') }} WIB</span>
                            </div>
                            <div
                                style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                                <i class="ri-map-pin-line" style="color: var(--primary);"></i>
                                <span>{{ Str::limit($event->lokasi, 30) }}</span>
                            </div>
                            @if ($event->kuota_peserta)
                                <div
                                    style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                                    <i class="ri-team-line" style="color: var(--primary);"></i>
                                    <span>{{ $event->jumlah_terdaftar }}/{{ $event->kuota_peserta }} Terdaftar</span>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.events.show', $event) }}" class="lux-btn lux-btn-outline lux-btn-sm"
                                style="flex: 1;">
                                <i class="ri-eye-line"></i> Detail
                            </a>
                            @if (auth()->user()->hasPermission('event.edit'))
                                <a href="{{ route('admin.events.edit', $event) }}"
                                    class="lux-btn lux-btn-primary lux-btn-sm">
                                    <i class="ri-pencil-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--text-muted);">
                    <i class="ri-calendar-off-line" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.1rem;">Belum ada event</p>
                    @if (auth()->user()->hasPermission('event.create'))
                        <a href="{{ route('admin.events.create') }}" class="lux-btn lux-btn-primary"
                            style="margin-top: 1rem;">
                            <i class="ri-add-line"></i> Buat Event Pertama
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($events->hasPages())
            <div style="margin-top: 2rem; display: flex; justify-content: center;">
                {{ $events->links('pagination::simple-bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
