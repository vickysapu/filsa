@extends('layouts.admin')
@section('title', 'Jadwal Meeting')

@section('content')
    <div class="lux-container">
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Jadwal Meeting</h1>
                <p class="text-muted">Kelola jadwal meeting online untuk jemaat</p>
            </div>
            @if (auth()->user()->hasPermission('meetings.create'))
                <button type="button" class="lux-btn lux-btn-primary" id="btnAddMeeting">
                    <i class="ri-video-add-line"></i> Buat Meeting
                </button>
            @endif
        </div>

        <div id="alertContainer" class="mb-4"></div>

        <!-- Filter -->
        <div class="lux-card mb-5">
            <form id="formFilter">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <label class="lux-label">Status</label>
                        <select id="filter_status" class="lux-select">
                            <option value="">Semua Status</option>
                            <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Akan Datang
                            </option>
                            <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Sedang
                                Berlangsung</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="lux-label">Cari</label>
                        <input type="text" id="filter_search" class="lux-input" value="{{ request('search') }}"
                            placeholder="Nama meeting...">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-self: end;">
                        <button type="button" id="btnFilter" class="lux-btn lux-btn-primary" style="flex: 1;">
                            <i class="ri-filter-line"></i> Filter
                        </button>
                        <button type="button" id="btnReset" class="lux-btn lux-btn-outline">
                            <i class="ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="lux-card">
            <div class="lux-card-body">
                <div class="table-responsive">
                    <table class="lux-table">
                        <thead>
                            <tr>
                                <th>Meeting</th>
                                <th>Platform</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="meetingsTableBody">
                            @forelse($meetings as $meeting)
                                <tr>
                                    <td>
                                        <strong>{{ $meeting->nama_meeting }}</strong>
                                        <br><small class="text-muted">{{ Str::limit($meeting->deskripsi, 50) }}</small>
                                    </td>
                                    <td>
                                        @if ($meeting->platform === 'jitsi')
                                            <span class="lux-badge" style="background: #1d84ff20; color: #1d84ff;">
                                                <i class="ri-video-line"></i> Jitsi Meet
                                            </span>
                                        @elseif($meeting->platform === 'zoom')
                                            <span class="lux-badge" style="background: #2d8cff20; color: #2d8cff;">
                                                <i class="ri-camera-line"></i> Zoom
                                            </span>
                                        @else
                                            <span class="lux-badge" style="background: #00877920; color: #008779;">
                                                <i class="ri-camera-lens-line"></i> Google Meet
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="ri-calendar-line"></i> {{ $meeting->tanggal_mulai->format('d M Y') }}
                                        </div>
                                        <small class="text-muted">{{ $meeting->tanggal_mulai->format('H:i') }} WIB</small>
                                    </td>
                                    <td>
                                        @if ($meeting->tanggal_mulai > now())
                                            <span class="lux-badge lux-badge-info">Akan Datang</span>
                                        @elseif($meeting->tanggal_selesai && $meeting->tanggal_selesai < now())
                                            <span class="lux-badge lux-badge-secondary">Selesai</span>
                                        @else
                                            <span class="lux-badge lux-badge-success">Berlangsung</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="lux-btn lux-btn-outline lux-btn-sm btn-view-meeting"
                                                data-id="{{ $meeting->id }}">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            @if (auth()->user()->hasPermission('meetings.edit'))
                                                <button class="lux-btn lux-btn-outline lux-btn-sm btn-edit-meeting"
                                                    data-id="{{ $meeting->id }}">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            @endif
                                            @if (auth()->user()->hasPermission('meetings.delete'))
                                                <button class="lux-btn lux-btn-danger lux-btn-sm btn-delete-meeting"
                                                    data-id="{{ $meeting->id }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="ri-calendar-off-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-3">Belum ada meeting dijadwalkan</p>
                                        @if (auth()->user()->hasPermission('meetings.create'))
                                            <button class="lux-btn lux-btn-primary mt-2" id="btnAddMeeting2">
                                                <i class="ri-add-line"></i> Buat Meeting Pertama
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="mt-4">
                    {{ $meetings->links('pagination::simple-bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Meeting -->
    <div class="modal fade" id="modalMeetingForm" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content lux-card">
                <form id="formMeeting">
                    @csrf
                    <input type="hidden" name="id" id="meetingId">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalMeetingLabel">Buat Meeting Baru</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div id="formErrors" class="alert alert-danger d-none">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Nama Meeting *</label>
                            <input type="text" name="nama_meeting" id="nama_meeting" class="lux-input form-control"
                                required maxlength="255">
                        </div>

                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="lux-textarea form-control" rows="3" maxlength="1000"></textarea>
                        </div>

                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Platform *</label>
                            <select name="platform" id="platform" class="lux-select form-select" required
                                onchange="togglePlatformFields()">
                                <option value="jitsi">Jitsi Meet (Gratis)</option>
                                <option value="zoom">Zoom Meeting</option>
                                <option value="google_meet">Google Meet</option>
                            </select>
                            <small class="text-muted">Jitsi Meet direkomendasikan - gratis tanpa batas waktu</small>
                        </div>

                        <div id="jitsiFields" class="lux-form-group mb-3">
                            <label class="lux-label">Room ID (Opsional)</label>
                            <input type="text" name="meeting_id" id="meeting_id" class="lux-input form-control"
                                placeholder="Kosongkan untuk auto-generate">
                            <small class="text-muted">Akan di-generate otomatis jika kosong</small>
                        </div>

                        <div id="manualUrlFields" class="lux-form-group mb-3" style="display: none;">
                            <label class="lux-label">URL Meeting *</label>
                            <input type="url" name="meeting_url" id="meeting_url" class="lux-input form-control"
                                placeholder="https://zoom.us/j/... atau https://meet.google.com/...">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Tanggal Mulai *</label>
                                    <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai"
                                        class="lux-input form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Tanggal Selesai</label>
                                    <input type="datetime-local" name="tanggal_selesai" id="tanggal_selesai"
                                        class="lux-input form-control">
                                </div>
                            </div>
                        </div>

                        <div class="lux-form-group mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                    value="1" checked>
                                <span class="form-check-label">Meeting Aktif</span>
                            </label>
                        </div>

                        <div class="lux-form-group mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="show_in_website" id="show_in_website"
                                    class="form-check-input" value="1" checked>
                                <span class="form-check-label">Tampilkan di Website Public</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitMeeting">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Meeting -->
    <div class="modal fade" id="modalDetailMeeting" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content lux-card">
                <div class="modal-header lux-modal-header">
                    <h3 class="modal-title lux-modal-title" id="detailNamaMeeting">Detail Meeting</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body lux-modal-body" id="detailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer lux-modal-footer">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Tutup</button>
                    <a href="#" id="btnJoinMeeting" class="lux-btn lux-btn-primary" target="_blank">
                        <i class="ri-video-add-line"></i> Join Meeting
                    </a>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="baseUrlStore" value="{{ route('admin.meetings.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.meetings.update', ['meeting' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.meetings.destroy', ['meeting' => '__ID__']) }}">
    <input type="hidden" id="baseUrlEdit" value="{{ route('admin.meetings.edit', ['meeting' => '__ID__']) }}">
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalForm = new bootstrap.Modal(document.getElementById('modalMeetingForm'));
            const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailMeeting'));
            const formMeeting = document.getElementById('formMeeting');
            const btnSave = document.getElementById('btnSubmitMeeting');
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;

            // Toggle Platform Fields
            window.togglePlatformFields = function() {
                const platform = document.getElementById('platform').value;
                const jitsiFields = document.getElementById('jitsiFields');
                const manualUrlFields = document.getElementById('manualUrlFields');
                const urlInput = manualUrlFields.querySelector('input');

                if (platform === 'jitsi') {
                    jitsiFields.style.display = 'block';
                    manualUrlFields.style.display = 'none';
                    urlInput.required = false;
                } else {
                    jitsiFields.style.display = 'none';
                    manualUrlFields.style.display = 'block';
                    urlInput.required = true;
                }
            };

            // Open Modal Add
            document.getElementById('btnAddMeeting')?.addEventListener('click', () => openModal('create'));
            document.getElementById('btnAddMeeting2')?.addEventListener('click', () => openModal('create'));

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
                    togglePlatformFields();
                }
                modalForm.show();
            }

            // Edit Meeting
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-edit-meeting')) {
                    const btn = e.target.closest('.btn-edit-meeting');
                    const id = btn.dataset.id;
                    loadMeetingForEdit(id);
                }
            });

            // Delete Meeting
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete-meeting')) {
                    const btn = e.target.closest('.btn-delete-meeting');
                    const id = btn.dataset.id;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Hapus',
                        text: 'Yakin ingin menghapus meeting ini?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) deleteMeeting(id);
                    });
                }
            });

            // View/Detail Meeting
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-view-meeting')) {
                    const btn = e.target.closest('.btn-view-meeting');
                    const id = btn.dataset.id;
                    showDetailMeeting(id);
                }
            });

            // Filter
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

            // Form Submit
            formMeeting?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!formMeeting.checkValidity()) {
                    formMeeting.classList.add('was-validated');
                    return;
                }

                const meetingId = document.getElementById('meetingId').value;
                let baseUrl = isEditMode ? document.getElementById('baseUrlUpdate').value.replace(
                    '__ID__', meetingId) : document.getElementById('baseUrlStore').value;
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;

                btnSave.disabled = true;
                btnSave.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
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
                        modalForm.hide();
                        loadMeetings(1);
                    } else {
                        if (data.errors) showErrors(data.errors);
                        else showAlert('error', data.message || 'Gagal menyimpan meeting');
                    }
                } catch (err) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan';
                }
            });

            // Load Meetings with Pagination
            function loadMeetings(page = 1) {
                const status = document.getElementById('filter_status').value;
                const search = document.getElementById('filter_search').value.trim();
                const params = new URLSearchParams({
                    page,
                    status,
                    search,
                    ajax: 1,
                    _token_nav: currentToken
                });

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
                        if (newBody) document.getElementById('meetingsTableBody').innerHTML = newBody.innerHTML;
                        if (newPagination) {
                            document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;
                            // Rebind pagination clicks
                            document.querySelectorAll('#paginationContainer a').forEach(link => {
                                link.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const url = new URL(this.href);
                                    loadMeetings(url.searchParams.get('page') || 1);
                                });
                            });
                        }
                        bindEvents();
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data meeting'));
            }

            // Load Meeting for Edit
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
                        if (data.success) openModal('edit', data.data);
                        else showAlert('error', data.message || 'Gagal memuat data meeting');
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            // Delete Meeting
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
                        } else showAlert('error', data.message || 'Gagal menghapus meeting');
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            // Show Detail Meeting
            function showDetailMeeting(id) {
                document.getElementById('detailContent').innerHTML =
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
                modalDetail.show();

                fetch(`{{ route('admin.meetings.show', '__ID__') }}?ajax=1&_token_nav=${currentToken}`.replace(
                        '__ID__', id), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const m = data.meeting;
                            document.getElementById('detailNamaMeeting').textContent = m.nama_meeting;
                            document.getElementById('btnJoinMeeting').href = m.meeting_url;

                            let html = `
                    <div class="mb-3">
                        <span class="lux-badge" style="background: ${getPlatformColor(m.platform)}20; color: ${getPlatformColor(m.platform)};">
                            <i class="ri-${getPlatformIcon(m.platform)}"></i> ${getPlatformName(m.platform)}
                        </span>
                    </div>
                    ${m.deskripsi ? `<p class="mb-3">${m.deskripsi}</p>` : ''}
                    <div class="row mb-3">
                        <div class="col-6"><strong><i class="ri-calendar-line"></i> Mulai:</strong><br>${new Date(m.tanggal_mulai).toLocaleString('id-ID')}</div>
                        ${m.tanggal_selesai ? `<div class="col-6"><strong><i class="ri-calendar-line"></i> Selesai:</strong><br>${new Date(m.tanggal_selesai).toLocaleString('id-ID')}</div>` : ''}
                    </div>
                    <div class="mb-3">
                        <strong><i class="ri-status-line"></i> Status:</strong><br>
                        <span class="lux-badge ${getStatusBadge(m)}">${getStatusText(m)}</span>
                    </div>
                    <div class="mb-3">
                        <strong><i class="ri-user-line"></i> Dibuat oleh:</strong><br>
                        ${m.creator?.name || '-'}
                    </div>
                `;
                            document.getElementById('detailContent').innerHTML = html;
                        } else {
                            showAlert('error', data.message || 'Gagal memuat detail');
                        }
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            // Helpers
            function getPlatformName(p) {
                return {
                    jitsi: 'Jitsi Meet',
                    zoom: 'Zoom',
                    google_meet: 'Google Meet'
                } [p] || p;
            }

            function getPlatformIcon(p) {
                return {
                    jitsi: 'video-line',
                    zoom: 'camera-line',
                    google_meet: 'camera-lens-line'
                } [p] || 'video-line';
            }

            function getPlatformColor(p) {
                return {
                    jitsi: '#1d84ff',
                    zoom: '#2d8cff',
                    google_meet: '#008779'
                } [p] || '#667eea';
            }

            function getStatusText(m) {
                if (new Date(m.tanggal_mulai) > new Date()) return 'Akan Datang';
                if (m.tanggal_selesai && new Date(m.tanggal_selesai) < new Date()) return 'Selesai';
                return 'Berlangsung';
            }

            function getStatusBadge(m) {
                if (new Date(m.tanggal_mulai) > new Date()) return 'lux-badge-info';
                if (m.tanggal_selesai && new Date(m.tanggal_selesai) < new Date()) return 'lux-badge-secondary';
                return 'lux-badge-success';
            }

            function resetForm() {
                formMeeting?.reset();
                formMeeting?.classList.remove('was-validated');
                document.getElementById('formErrors')?.classList.add('d-none');
                document.getElementById('meetingId').value = '';
                document.getElementById('platform').value = 'jitsi';
                document.getElementById('is_active').checked = true;
                document.getElementById('show_in_website').checked = true;
                togglePlatformFields();
            }

            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;
                const bg = type === 'success' ? '#f0fdf4' : '#fef2f2';
                const border = type === 'success' ? 'var(--success)' : 'var(--danger)';
                const color = type === 'success' ? '#166534' : '#991b1b';
                const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';
                container.innerHTML =
                    `<div class="lux-card" style="border-left:4px solid ${border};background:${bg};display:flex;align-items:center;gap:10px;padding:12px;"><i class="${icon}" style="color:${border};font-size:1.5rem;"></i><span style="color:${color};font-weight:600;">${message}</span></div>`;
                setTimeout(() => {
                    if (container.firstElementChild) container.firstElementChild.style.display = 'none';
                }, 5000);
            }

            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;
                let html = '<strong>Validasi gagal:</strong><ul class="mb-0">';
                for (let f in errors) {
                    (Array.isArray(errors[f]) ? errors[f] : [errors[f]]).forEach(m => {
                        html += `<li>${f}: ${m}</li>`;
                    });
                }
                html += '</ul>';
                errorList.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            function bindEvents() {
            }

            togglePlatformFields();
            bindEvents();
        });
    </script>
@endpush
