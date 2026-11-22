@extends('layouts.admin')

@section('title', 'Jadwal Meeting')

@php
    function getPlatformName($p)
    {
        return [
            'jitsi' => 'Jitsi Meet',
            'zoom' => 'Zoom',
            'google_meet' => 'camera-lens-line',
            'internal' => 'vidicon-line',
        ][$p] ??
            ($p ?? '-');
    }

    function getPlatformIcon($p)
    {
        return [
            'jitsi' => 'video-line',
            'zoom' => 'camera-line',
            'google_meet' => 'camera-lens-line',
        ][$p] ?? 'video-line';
    }

    function getStatusText($m)
    {
        if (!$m || !$m->tanggal_mulai) {
            return '-';
        }

        $now = now();

        if ($m->tanggal_mulai > $now) {
            return 'Akan Datang';
        }
        if ($m->tanggal_selesai && $m->tanggal_selesai < $now) {
            return 'Selesai';
        }
        return 'Berlangsung';
    }

    function getStatusBadgeClass($m)
    {
        if (!$m || !$m->tanggal_mulai) {
            return 'badge-status-upcoming';
        }

        $now = now();

        if ($m->tanggal_mulai > $now) {
            return 'badge-status-upcoming';
        }
        if ($m->tanggal_selesai && $m->tanggal_selesai < $now) {
            return 'badge-status-completed';
        }
        return 'badge-status-ongoing';
    }
@endphp

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Jadwal Meeting</h1>
                <p class="text-muted-custom mb-0">Kelola jadwal meeting online untuk jemaat</p>
            </div>
            @if (auth()->user()->hasPermission('meetings.create'))
                <button type="button" class="btn btn-custom" id="btnAddMeeting">
                    <i class="ri-video-add-line me-1"></i> Buat Meeting
                </button>
            @endif
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="formFilter">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                            <select id="filter_status" class="form-select form-select-custom">
                                <option value="">Semua Status</option>
                                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Akan
                                    Datang</option>
                                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Sedang
                                    Berlangsung</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai
                                </option>
                            </select>
                        </div>
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Cari</label>
                            <div class="position-relative">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="filter_search" class="form-control form-control-custom ps-5"
                                    value="{{ request('search') }}" placeholder="Nama meeting...">
                            </div>
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-2">
                            <button type="button" id="btnFilter" class="btn btn-custom flex-fill">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            <button type="button" id="btnReset" class="btn btn-outline-custom" title="Reset">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-custom">
            {{-- DataTable Toolbar --}}
            <div class="dt-toolbar">
                <div class="dt-entries">
                    <label>Tampilkan</label>
                    <select onchange="var p=new URLSearchParams(window.location.search);p.set('per_page',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString()">
                        @foreach([10,25,50,100] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 10) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    <label>entri</label>
                </div>
                <div class="dt-search">
                    <div class="dt-search-wrap">
                        <i class="ri-search-line"></i>
                        <input type="text" placeholder="Cari nama meeting..." value="{{ request('search') }}"
                            onkeydown="if(event.key==='Enter'){var p=new URLSearchParams(window.location.search);p.set('search',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString();}">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Meeting</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Platform</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Jadwal</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="meetingsTableBody">
                        @forelse($meetings as $meeting)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <div class="fw-semibold">{{ $meeting->nama_meeting }}</div>
                                    <small class="text-muted-custom">{{ Str::limit($meeting->deskripsi, 50) }}</small>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-platform-{{ $meeting->platform }}">
                                        <i class="ri-{{ getPlatformIcon($meeting->platform) }} me-1"></i>
                                        {{ getPlatformName($meeting->platform) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-medium">
                                        <i class="ri-calendar-line me-1"></i> {{ $meeting->tanggal_mulai->format('d M Y') }}
                                    </div>
                                    <small class="text-muted-custom">{{ $meeting->tanggal_mulai->format('H:i') }}
                                        WIB</small>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge {{ getStatusBadgeClass($meeting) }}">
                                        {{ getStatusText($meeting) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn-action btn-action-info btn-view-meeting"
                                            data-id="{{ $meeting->id }}" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        @if (auth()->user()->hasPermission('meetings.edit'))
                                            <button type="button" class="btn-action btn-action-primary btn-edit-meeting"
                                                data-id="{{ $meeting->id }}" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endif
                                        @if (auth()->user()->hasPermission('meetings.delete'))
                                            <button type="button" class="btn-action btn-action-danger btn-delete-meeting"
                                                data-id="{{ $meeting->id }}" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-calendar-off-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Belum ada meeting dijadwalkan</h6>
                                        <p class="text-muted-custom small mb-4">Mulai jadwalkan meeting pertama untuk jemaat
                                        </p>
                                        @if (auth()->user()->hasPermission('meetings.create'))
                                            <button type="button" class="btn btn-custom" id="btnAddMeeting2">
                                                <i class="ri-add-line me-1"></i> Buat Meeting Pertama
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($meetings->hasPages())
                <div class="dt-footer">
                    <span class="dt-info">
                        Menampilkan {{ $meetings->firstItem() }}–{{ $meetings->lastItem() }} dari {{ $meetings->total() }} data
                    </span>
                    <div>
                        {{ $meetings->withQueryString()->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="dt-footer">
                    <span class="dt-info">Menampilkan {{ $meetings->count() }} data</span>
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Form Meeting -->
    <div class="modal fade" id="modalMeetingForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <form id="formMeeting">
                    @csrf
                    <input type="hidden" name="id" id="meetingId">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalMeetingLabel">Buat Meeting Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Meeting <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama_meeting" id="nama_meeting"
                                class="form-control form-control-custom" required maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control form-control-custom textarea-custom" rows="3"
                                maxlength="1000"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Platform <span
                                    class="text-danger">*</span></label>
                            <select name="platform" id="platform" class="form-select form-select-custom" required
                                onchange="togglePlatformFields()">
                                <option value="jitsi">Jitsi Meet (Gratis)</option>
                                <option value="zoom">Zoom Meeting</option>
                                <option value="google_meet">Google Meet</option>
                                <option value="internal">Meeting Internal (Aplikasi Sendiri)</option>
                            </select>
                            <small class="text-muted-custom"><i class="ri-information-line me-1"></i>Jitsi Meet
                                direkomendasikan - gratis tanpa batas waktu</small>
                        </div>

                        <!-- Jitsi Fields -->
                        <div id="jitsiFields" class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Room ID (Opsional)</label>
                            <input type="text" name="meeting_id" id="meeting_id"
                                class="form-control form-control-custom" placeholder="Kosongkan untuk auto-generate">
                            <small class="text-muted-custom">Akan di-generate otomatis jika kosong</small>
                        </div>

                        <!-- Manual URL Fields (Zoom/Google Meet) -->
                        <div id="manualUrlFields" class="mb-3" style="display: none;">
                            <label class="form-label small fw-semibold text-muted-custom">URL Meeting <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="meeting_url" id="meeting_url"
                                class="form-control form-control-custom"
                                placeholder="https://zoom.us/j/... atau https://meet.google.com/...">
                        </div>

                        <div id="internalFields" style="display:none">
                            <hr class="border-custom my-4">
                            <h6 class="fw-semibold mb-3">Pengaturan Ruang Meeting</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Password Room
                                    (opsional)</label>
                                <input type="password" name="password" id="password"
                                    class="form-control form-control-custom" autocomplete="new-password"
                                    placeholder="Kosongkan jika tanpa password">
                                <label class="form-check mt-2">
                                    <input type="checkbox" name="clear_password" id="clear_password" value="1"
                                        class="form-check-input-custom">
                                    <span class="form-check-label-custom">Hapus password yang sudah ada</span>
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Stream Key YouTube
                                    (opsional)</label>
                                <input type="password" name="youtube_stream_key" id="youtube_stream_key"
                                    class="form-control form-control-custom" autocomplete="new-password"
                                    placeholder="Tempel stream key dari YouTube Studio">
                                <small class="text-muted-custom" id="ytKeyHint" style="display:none">
                                    <i class="ri-check-line me-1"></i>Stream key sudah tersimpan. Isi lagi hanya jika ingin
                                    menggantinya.
                                </small>
                                <label class="form-check mt-2">
                                    <input type="checkbox" name="clear_youtube_key" id="clear_youtube_key"
                                        value="1" class="form-check-input-custom">
                                    <span class="form-check-label-custom">Hapus stream key yang sudah ada</span>
                                </label>
                            </div>

                            <label class="form-check form-switch-custom mb-3">
                                <input type="checkbox" name="viewer_mode" id="viewer_mode" value="1"
                                    class="form-check-input-custom">
                                <span class="form-check-label-custom">Mode Penonton (hanya host yang tampil; peserta angkat
                                    tangan untuk bicara)</span>
                            </label>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Co-Host (tahan Ctrl untuk
                                    pilih banyak)</label>
                                <select name="host_ids[]" id="host_ids" class="form-select form-select-custom" multiple
                                    size="5">
                                    @foreach ($users ?? [] as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Panel Samping</label>
                                <select name="side_type" id="side_type" class="form-select form-select-custom mb-2">
                                    <option value="none">Tidak dipakai</option>
                                    <option value="text">Teks / Lirik lagu</option>
                                    <option value="image">Gambar (URL)</option>
                                    <option value="video">Video (URL YouTube atau .mp4)</option>
                                </select>
                                <textarea name="side_content" id="side_content" class="form-control form-control-custom textarea-custom"
                                    rows="5" maxlength="5000" placeholder="Isi lirik, atau tempel URL gambar/video"></textarea>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">Tanggal Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai"
                                    class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">Tanggal Selesai</label>
                                <input type="datetime-local" name="tanggal_selesai" id="tanggal_selesai"
                                    class="form-control form-control-custom">
                            </div>
                        </div>

                        <hr class="border-custom my-4">

                        <div class="d-flex flex-column gap-3">
                            <label class="form-check form-switch-custom">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input-custom"
                                    value="1" checked>
                                <span class="form-check-label-custom">Meeting Aktif</span>
                            </label>
                            <label class="form-check form-switch-custom">
                                <input type="checkbox" name="show_in_website" id="show_in_website"
                                    class="form-check-input-custom" value="1" checked>
                                <span class="form-check-label-custom">Tampilkan di Website Public</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitMeeting">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Meeting -->
    <div class="modal fade" id="modalDetailMeeting" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="detailNamaMeeting">Detail Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="detailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted-custom mb-0">Memuat detail meeting...</p>
                    </div>
                </div>
                <div class="modal-footer border-top border-custom py-3 px-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Tutup</button>
                    <a href="#" id="btnJoinMeeting" class="btn btn-custom" target="_blank">
                        <i class="ri-video-add-line me-1"></i> Join Meeting
                    </a>
                    <button type="button" id="btnCopyLink" class="btn btn-outline-custom">
                        <i class="ri-links-line me-1"></i> Salin Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Base URLs -->
    <input type="hidden" id="baseUrlStore" value="{{ route('admin.meetings.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.meetings.update', ['meeting' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.meetings.destroy', ['meeting' => '__ID__']) }}">
    <input type="hidden" id="baseUrlEdit" value="{{ route('admin.meetings.edit', ['meeting' => '__ID__']) }}">

    <style>
        /* ===== PAGE SPECIFIC STYLES ===== */
        .page-wrapper {
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== PLATFORM BADGES ===== */
        .badge-platform-jitsi {
            background: rgba(29, 132, 255, 0.15);
            color: #1d84ff;
            border: 1px solid rgba(29, 132, 255, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-platform-zoom {
            background: rgba(45, 140, 255, 0.15);
            color: #2d8cff;
            border: 1px solid rgba(45, 140, 255, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-platform-google_meet {
            background: rgba(0, 135, 121, 0.15);
            color: #008779;
            border: 1px solid rgba(0, 135, 121, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-upcoming {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-platform-internal {
            background: rgba(124, 58, 237, 0.15);
            color: #7c3aed;
            border: 1px solid rgba(124, 58, 237, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-ongoing {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-completed {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* Form pembungkus harus ikut jadi flex column agar body bisa scroll */
        .modal-dialog-scrollable .modal-content>form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
            /* tambahkan ini */
        }

        /* ===== ACTION BUTTONS ===== */
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: var(--transition);
            font-size: 1rem;
            padding: 0;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-action-info {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-info:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-primary {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-primary:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-action-danger:hover {
            background: #ef4444;
            color: white;
        }

        /* ===== FORM ELEMENTS ===== */
        .form-control-custom,
        .form-select-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .form-control-custom::placeholder {
            color: var(--text-secondary);
        }

        .textarea-custom {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        .form-check-input-custom {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            accent-color: var(--accent-blue);
        }

        .form-check-input-custom:checked {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .form-check-input-custom[type="checkbox"] {
            border-radius: 4px;
        }

        .form-check-label-custom {
            font-size: 0.9rem;
            color: var(--text-primary);
            cursor: pointer;
            margin-left: 0.35rem;
        }

        .form-switch-custom .form-check-input-custom {
            width: 40px;
            height: 22px;
            border-radius: 11px;
            background-image: none;
            background-color: var(--border-color);
        }

        .form-switch-custom .form-check-input-custom:checked {
            background-color: var(--accent-blue);
        }

        /* ===== ALERT CUSTOM ===== */
        .alert-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            animation: slideIn 0.3s ease;
        }

        .alert-custom.success {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .alert-custom.error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .alert-custom i {
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ===== EMPTY STATE ===== */
        .empty-state-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--bg-body);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 2.5rem;
            opacity: 0.6;
        }

        /* ===== MODAL CUSTOM ===== */
        .modal-content-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: var(--bg-surface);
            padding: 1rem 1.5rem;
        }

        .modal-body {
            background: var(--bg-body);
        }

        .modal-footer {
            background: var(--bg-surface);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
        }

        .modal-dialog-scrollable .modal-header,
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
        }

        /* ===== PAGINATION ===== */
        .pagination .page-link {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 0.875rem;
            margin: 0 2px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            background: var(--bg-body);
            color: var(--text-secondary);
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .modal-content-custom {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 800px;
            }

            .d-flex.flex-wrap.gap-2 {
                flex-direction: column;
            }

            .d-flex.flex-wrap.gap-2 .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCES =====
            let modalForm, modalDetail;
            const modalFormEl = document.getElementById('modalMeetingForm');
            const modalDetailEl = document.getElementById('modalDetailMeeting');

            if (modalFormEl && bootstrap.Modal) modalForm = new bootstrap.Modal(modalFormEl);
            if (modalDetailEl && bootstrap.Modal) modalDetail = new bootstrap.Modal(modalDetailEl);

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== CONSTANTS =====
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;

            const platformNames = {
                jitsi: 'Jitsi Meet',
                zoom: 'Zoom',
                google_meet: 'Google Meet',
                internal: 'Meeting Gereja'
            };
            const platformIcons = {
                jitsi: 'video-line',
                zoom: 'camera-line',
                google_meet: 'camera-lens-line',
                internal: 'vidicon-line'
            };
            const getPlatformName = p => platformNames[p] || p || '-';
            const getPlatformIcon = p => platformIcons[p] || 'video-line';

            function getStatusKey(m) {
                const now = new Date();
                if (!m.tanggal_mulai) return null;
                if (new Date(m.tanggal_mulai) > now) return 'upcoming';
                if (m.tanggal_selesai && new Date(m.tanggal_selesai) < now) return 'completed';
                return 'ongoing';
            }
            const getStatusText = m => ({
                upcoming: 'Akan Datang',
                completed: 'Selesai',
                ongoing: 'Berlangsung'
            } [getStatusKey(m)] || '-');
            const getStatusBadgeClass = m => 'badge-status-' + (getStatusKey(m) || 'upcoming');

            function escapeHtml(s) {
                return String(s ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }

            // ===== PLATFORM FIELD TOGGLE =====
            window.togglePlatformFields = function() {
                const platform = document.getElementById('platform')?.value;
                const jitsiFields = document.getElementById('jitsiFields');
                const manualUrlFields = document.getElementById('manualUrlFields');
                const urlInput = manualUrlFields?.querySelector('input');

                const internalFields = document.getElementById('internalFields');
                if (internalFields) internalFields.style.display = platform === 'internal' ? 'block' : 'none';

                if (platform === 'jitsi') {
                    jitsiFields.style.display = 'block';
                    manualUrlFields.style.display = 'none';
                    if (urlInput) urlInput.required = false;
                } else if (platform === 'internal') {
                    jitsiFields.style.display = 'none';
                    manualUrlFields.style.display = 'none';
                    if (urlInput) urlInput.required = false;
                } else {
                    jitsiFields.style.display = 'none';
                    manualUrlFields.style.display = 'block';
                    if (urlInput) urlInput.required = true;
                }
            }

            // ===== OPEN MODAL (CREATE) =====
            function openModal(mode = 'create', data = null) {
                resetForm();
                isEditMode = mode === 'edit';
                document.getElementById('modalMeetingLabel').textContent = mode === 'edit' ? 'Edit Meeting' :
                    'Buat Meeting Baru';

                if (mode === 'edit' && data) {
                    document.getElementById('meetingId').value = data.id;
                    document.getElementById('nama_meeting').value = data.nama_meeting || '';
                    document.getElementById('deskripsi').value = data.deskripsi || '';
                    document.getElementById('platform').value = data.platform || 'jitsi';
                    document.getElementById('meeting_id').value = data.meeting_id || '';
                    document.getElementById('meeting_url').value = data.meeting_url || '';
                    document.getElementById('tanggal_mulai').value = data.tanggal_mulai ? data.tanggal_mulai
                        .replace(' ', 'T') : '';
                    document.getElementById('tanggal_selesai').value = data.tanggal_selesai ? data.tanggal_selesai
                        .replace(' ', 'T') : '';
                    document.getElementById('is_active').checked = !!data.is_active;
                    document.getElementById('show_in_website').checked = data.show_in_website !== false;
                    document.getElementById('viewer_mode').checked = !!data.viewer_mode;
                    document.getElementById('side_type').value = data.side_type || 'none';
                    document.getElementById('side_content').value = data.side_content || '';
                    document.getElementById('password').value = '';
                    document.getElementById('clear_password').checked = false;
                    document.getElementById('youtube_stream_key').value = '';
                    document.getElementById('clear_youtube_key').checked = false;
                    document.getElementById('ytKeyHint').style.display = data.has_youtube_key ? 'block' : 'none';
                    const hostIds = (data.host_ids || []).map(String);
                    [...document.getElementById('host_ids').options].forEach(o => o.selected = hostIds.includes(o
                        .value));
                    togglePlatformFields();
                }

                if (modalForm) modalForm.show();
            }

            // ===== EVENT: Add Meeting Buttons =====
            document.getElementById('btnAddMeeting')?.addEventListener('click', () => openModal('create'));
            document.getElementById('btnAddMeeting2')?.addEventListener('click', () => openModal('create'));

            // ===== EVENT: Edit Meeting (delegation) =====
            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.btn-edit-meeting');
                if (editBtn) {
                    e.preventDefault();
                    loadMeetingForEdit(editBtn.dataset.id);
                }
            });

            // ===== EVENT: Delete Meeting (delegation) =====
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-meeting');
                if (deleteBtn) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Konfirmasi Hapus',
                            text: 'Yakin ingin menghapus meeting ini?',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) deleteMeeting(deleteBtn.dataset.id);
                        });
                    } else {
                        if (confirm('Yakin ingin menghapus meeting ini?')) {
                            deleteMeeting(deleteBtn.dataset.id);
                        }
                    }
                }
            });

            // ===== EVENT: View Meeting (delegation) =====
            document.addEventListener('click', function(e) {
                const viewBtn = e.target.closest('.btn-view-meeting');
                if (viewBtn) {
                    e.preventDefault();
                    showDetailMeeting(viewBtn.dataset.id);
                }
            });

            // ===== EVENT: Filter =====
            document.getElementById('btnFilter')?.addEventListener('click', () => loadMeetings(1));
            document.getElementById('btnReset')?.addEventListener('click', function() {
                document.getElementById('filter_status').value = '';
                document.getElementById('filter_search').value = '';
                loadMeetings(1);
            });
            document.getElementById('filter_search')?.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadMeetings(1);
                }
            });

            // ===== EVENT: Form Submit =====
            const formMeeting = document.getElementById('formMeeting');
            const btnSave = document.getElementById('btnSubmitMeeting');

            formMeeting?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!formMeeting.checkValidity()) {
                    formMeeting.classList.add('was-validated');
                    return;
                }

                const meetingId = document.getElementById('meetingId').value;
                let baseUrl = isEditMode ?
                    document.getElementById('baseUrlUpdate').value.replace('__ID__', meetingId) :
                    document.getElementById('baseUrlStore').value;

                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;

                if (btnSave) {
                    btnSave.disabled = true;
                    btnSave.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                const formData = new FormData(formMeeting);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (modalForm) modalForm.hide();
                        loadMeetings(1);
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showAlert('error', data.message || 'Gagal menyimpan meeting');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    if (btnSave) {
                        btnSave.disabled = false;
                        btnSave.innerHTML = '<i class="ri-save-line me-1"></i> Simpan';
                    }
                }
            });

            // ===== FUNCTION: Load Meetings =====
            function loadMeetings(page = 1) {
                const status = document.getElementById('filter_status')?.value || '';
                const search = document.getElementById('filter_search')?.value?.trim() || '';
                const params = new URLSearchParams({
                    page,
                    status,
                    search,
                    ajax: 1,
                    _token_nav: currentToken
                });

                const tableBody = document.getElementById('meetingsTableBody');
                if (tableBody) {
                    tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-5 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted-custom mt-3 mb-0">Memuat data meeting...</p>
                    </td>
                </tr>`;
                }

                fetch(`{{ route('admin.meetings.index') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(r => r.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newBody = doc.querySelector('#meetingsTableBody');
                        const newPagination = doc.querySelector('#paginationContainer');

                        if (newBody && tableBody) {
                            tableBody.innerHTML = newBody.innerHTML;
                        }
                        if (newPagination) {
                            const paginationEl = document.getElementById('paginationContainer');
                            if (paginationEl) {
                                paginationEl.innerHTML = newPagination.innerHTML;
                                bindPaginationEvents();
                            }
                        }
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data meeting'));
            }

            // ===== FUNCTION: Bind Pagination Events =====
            function bindPaginationEvents() {
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        loadMeetings(url.searchParams.get('page') || 1);
                    });
                });
            }

            // ===== FUNCTION: Load Meeting for Edit =====
            function loadMeetingForEdit(id) {
                let baseUrl = document.getElementById('baseUrlEdit').value.replace('__ID__', id);
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}&ajax=1`;

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            openModal('edit', data.data);
                        } else {
                            showAlert('error', data.message || 'Gagal memuat data meeting');
                        }
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            // ===== FUNCTION: Delete Meeting =====
            function deleteMeeting(id) {
                let baseUrl = document.getElementById('baseUrlDelete').value.replace('__ID__', id);
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}`;

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            loadMeetings(1);
                        } else {
                            showAlert('error', data.message || 'Gagal menghapus meeting');
                        }
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            // ===== FUNCTION: Show Detail Meeting =====
            function showDetailMeeting(id) {
                const detailContent = document.getElementById('detailContent');
                if (detailContent) {
                    detailContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted-custom mb-0">Memuat detail meeting...</p>
                </div>`;
                }
                if (modalDetail) modalDetail.show();

                fetch(`{{ route('admin.meetings.show', '__ID__') }}?ajax=1&_token_nav=${currentToken}`.replace(
                        '__ID__', id), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            showAlert('error', data.message || 'Gagal memuat detail');
                            return;
                        }

                        const m = data.meeting;
                        document.getElementById('detailNamaMeeting').textContent = m.nama_meeting;
                        document.getElementById('btnJoinMeeting').href = m.join_url;

                        const platformBadge = `<span class="badge badge-platform-${m.platform}">
                <i class="ri-${getPlatformIcon(m.platform)} me-1"></i>${getPlatformName(m.platform)}
            </span>`;
                        const statusBadge =
                            `<span class="badge ${getStatusBadgeClass(m)}">${getStatusText(m)}</span>`;
                        const endDate = m.tanggal_selesai ?
                            `<div class="col-6"><p class="small text-muted-custom mb-1">Selesai</p><p class="fw-medium mb-0">${new Date(m.tanggal_selesai).toLocaleString('id-ID')}</p></div>` :
                            '';

                        detailContent.innerHTML = `
                <div class="mb-4 text-center">${platformBadge}</div>
                
                ${m.deskripsi ? `<p class="mb-4 text-muted-custom">${escapeHtml(m.deskripsi)}</p>` : ''}                
                <div class="row g-4 mb-4">
                    <div class="col-6">
                        <p class="small text-muted-custom mb-1"><i class="ri-calendar-line me-1"></i> Mulai</p>
                        <p class="fw-medium mb-0">${new Date(m.tanggal_mulai).toLocaleString('id-ID')}</p>
                    </div>
                    ${endDate}
                </div>
                
                <div class="mb-4">
                    <p class="small text-muted-custom mb-1"><i class="ri-status-line me-1"></i> Status</p>
                    ${statusBadge}
                </div>
                
                <div>
                    <p class="small text-muted-custom mb-1"><i class="ri-user-line me-1"></i> Dibuat oleh</p>
                    <p class="fw-medium mb-0">${m.creator?.name || '-'}</p>
                </div>
            `;
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            // ===== FUNCTION: Reset Form =====
            function resetForm() {
                const form = document.getElementById('formMeeting');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                    document.getElementById('side_type').value = 'none';
                }

                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');

                document.getElementById('meetingId').value = '';
                document.getElementById('platform').value = 'jitsi';
                document.getElementById('is_active').checked = true;
                document.getElementById('show_in_website').checked = true;
                document.getElementById('ytKeyHint').style.display = 'none';
                togglePlatformFields();
            }

            // ===== FUNCTION: Show Alert =====
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;

                const alertClass = type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';
                const borderColor = type === 'success' ? '#22c55e' : '#ef4444';
                const textColor = type === 'success' ? '#166534' : '#991b1b';

                container.innerHTML = `
            <div class="alert-custom ${alertClass}" data-auto-close>
                <i class="${icon}"></i>
                <span>${message}</span>
                <button class="alert-close">&times;</button>
            </div>`;

                // Auto close after 5 seconds
                const alert = container.firstElementChild;
                setTimeout(() => {
                    if (alert) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);

                // Close button handler
                alert?.querySelector('.alert-close')?.addEventListener('click', () => alert.remove());
            }

            // ===== FUNCTION: Show Validation Errors =====
            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;

                let html = '';
                for (let field in errors) {
                    const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                    messages.forEach(msg => {
                        html += `<li><strong>${field}:</strong> ${msg}</li>`;
                    });
                }
                errorList.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            // ===== INIT =====
            togglePlatformFields();
            bindPaginationEvents();

            // ===== AUTO-CLOSE ALERTS =====
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // ===== ESC KEY TO CLOSE MODALS =====
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (modalForm?._isShown) modalForm.hide();
                    if (modalDetail?._isShown) modalDetail.hide();
                }
            });
        });
    </script>
@endsection
