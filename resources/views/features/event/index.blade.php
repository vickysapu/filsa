@extends('layouts.admin')
@section('title', 'Manajemen Events')

@section('content')
    <style>
        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
            padding: 1.5rem;
        }

        .modal-dialog-scrollable .lux-card {
            display: flex;
            flex-direction: column;
            height: auto;
            max-height: none;
        }

        .modal-dialog-scrollable .lux-modal-body {
            overflow-y: auto;
            flex: 1;
        }

        .modal-dialog-scrollable .modal-header,
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
            position: sticky;
            z-index: 10;
        }

        .modal-dialog-scrollable .modal-header {
            top: 0;
            background: white;
        }

        .modal-dialog-scrollable .modal-footer {
            bottom: 0;
            background: white;
            border-top: 1px solid #e2e8f0;
        }
    </style>
    <div class="lux-container">
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Events</h1>
                <p class="text-muted">Kelola event gereja untuk website public</p>
            </div>
            @if (auth()->user()->hasPermission('event.create'))
                <button type="button" class="lux-btn lux-btn-primary" id="btnAddEvent">
                    <i class="ri-add-line"></i> Buat Event Baru
                </button>
            @endif
        </div>

        <div id="alertContainer" class="mb-4"></div>

        <!-- Filter Card -->
        <div class="lux-card mb-5">
            <form id="formFilter">
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                    <div>
                        <label class="lux-label">Kategori</label>
                        <select id="filter_kategori" class="lux-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoris as $kat)
                                <option value="{{ $kat->id }}"
                                    {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="lux-label">Status</label>
                        <select id="filter_status" class="lux-select">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published
                            </option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung
                            </option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="lux-label">Cari</label>
                        <input type="text" id="filter_search" class="lux-input" value="{{ request('search') }}"
                            placeholder="Nama event, lokasi...">
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

        <!-- Events Table -->
        <div class="lux-card">
            <div class="lux-card-body">
                <div class="table-responsive">
                    <table class="lux-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="eventsTableBody">
                            @forelse($events as $evt)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            @if ($evt->gambar_featured)
                                                <img src="{{ asset('storage/' . $evt->gambar_featured) }}"
                                                    alt="{{ $evt->nama_event }}"
                                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            @else
                                                <div
                                                    style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                    <i class="ri-calendar-event-line" style="font-size: 1.5rem;"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $evt->nama_event }}</strong>
                                                <br><small class="text-muted">{{ Str::limit($evt->deskripsi, 60) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="lux-badge"
                                            style="background: {{ $evt->kategori->warna }}20; color: {{ $evt->kategori->warna }};">
                                            {{ $evt->kategori->nama_kategori }}
                                        </span>
                                    </td>
                                    <td>
                                        <div><i class="ri-calendar-line"></i> {{ $evt->tanggal_mulai->format('d M Y') }}
                                        </div>
                                        <small class="text-muted">{{ $evt->tanggal_mulai->format('H:i') }} WIB</small>
                                    </td>
                                    <td>{{ $evt->lokasi }}</td>
                                    <td>
                                        <span class="lux-badge {{ $evt->status_label['color'] }}">
                                            {{ $evt->status_label['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="lux-btn lux-btn-outline lux-btn-sm btn-view-event"
                                                data-id="{{ $evt->id }}" title="Detail">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            @if (auth()->user()->hasPermission('event.edit'))
                                                <button class="lux-btn lux-btn-outline lux-btn-sm btn-edit-event"
                                                    data-id="{{ $evt->id }}" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            @endif
                                            @if (auth()->user()->hasPermission('event.delete'))
                                                <button class="lux-btn lux-btn-danger lux-btn-sm btn-delete-event"
                                                    data-id="{{ $evt->id }}" title="Hapus">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="ri-calendar-event-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-3">Belum ada event</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="mt-4">
                    {{ $events->links('pagination::simple-bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Event -->
    <div class="modal fade" id="modalEventForm" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content lux-card">
                <form id="formEvent" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="eventId">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalEventLabel">Buat Event Baru</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div id="formErrors" class="alert alert-danger d-none">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Nama Event *</label>
                                    <input type="text" name="nama_event" id="nama_event"
                                        class="lux-input form-control" required>
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Kategori Event *</label>
                                    <select name="kategori_id" id="kategori_id" class="lux-select form-select" required>
                                        <option value="">Pilih Kategori...</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Deskripsi Singkat *</label>
                                    <textarea name="deskripsi" id="deskripsi" class="lux-textarea form-control" rows="3" required></textarea>
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Deskripsi Lengkap</label>
                                    <textarea name="deskripsi_lengkap" id="deskripsi_lengkap" class="lux-textarea form-control" rows="5"></textarea>
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
                                    <label class="lux-label">Lokasi *</label>
                                    <input type="text" name="lokasi" id="lokasi" class="lux-input form-control"
                                        required>
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Alamat Lengkap</label>
                                    <textarea name="alamat_lengkap" id="alamat_lengkap" class="lux-textarea form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Pembicara/Narasumber</label>
                                    <input type="text" name="pembicara" id="pembicara"
                                        class="lux-input form-control">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="lux-form-group mb-3">
                                            <label class="lux-label">Kontak Person</label>
                                            <input type="text" name="kontak_person" id="kontak_person"
                                                class="lux-input form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="lux-form-group mb-3">
                                            <label class="lux-label">WhatsApp</label>
                                            <input type="text" name="whatsapp_kontak" id="whatsapp_kontak"
                                                class="lux-input form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Email Kontak</label>
                                    <input type="email" name="email_kontak" id="email_kontak"
                                        class="lux-input form-control">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="lux-form-group mb-3">
                                            <label class="lux-label">Harga Tiket (Rp)</label>
                                            <input type="number" name="harga_tiket" id="harga_tiket"
                                                class="lux-input form-control" min="0" step="1000">
                                        </div>
                                    </div>
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Gambar Featured</label>
                                    <div id="currentFeatured" class="mb-2"></div>
                                    <input type="file" name="gambar_featured" id="gambar_featured"
                                        class="lux-input form-control" accept="image/*">
                                    <div id="featuredPreview" class="mt-2" style="display:none;"><img
                                            id="previewFeatured" style="max-width:100%; border-radius:8px;"></div>
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Galeri</label>
                                    <div id="currentGallery" class="lux-grid lux-grid-3 mb-2" style="gap:0.5rem;"></div>
                                    <input type="file" name="gambar_galeri[]" id="gambar_galeri"
                                        class="lux-input form-control" accept="image/*" multiple>
                                </div>
                                <div class="lux-form-group mb-3 p-3"
                                    style="background:#f8fafc; border-radius:var(--radius);">
                                    <label class="lux-label mb-2">Opsi Tampilan</label>
                                    <select name="status" id="status" class="lux-select form-select mb-2">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="ongoing">Berlangsung</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                    </select>
                                    <label class="form-check d-block mb-2"><input type="checkbox" name="is_featured"
                                            id="chkFeatured" class="form-check-input" value="1"> Featured
                                        Event</label>
                                    <label class="form-check d-block"><input type="checkbox" name="show_in_website"
                                            id="chkShowWeb" class="form-check-input" value="1" checked> Tampilkan di
                                        Website</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitEvent">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Event -->
    <div class="modal fade" id="modalDetailEvent" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content lux-card">
                <div class="modal-header lux-modal-header">
                    <h3 class="modal-title lux-modal-title" id="detailNamaEvent">Detail Event</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body lux-modal-body" id="detailContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer lux-modal-footer">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="lux-btn lux-btn-primary" id="btnEditFromDetail">
                        <i class="ri-edit-line"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="baseUrlStore" value="{{ route('admin.events.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.events.update', ['event' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.events.destroy', ['event' => '__ID__']) }}">
    <input type="hidden" id="baseUrlEdit" value="{{ route('admin.events.edit', ['event' => '__ID__']) }}">
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalForm = new bootstrap.Modal(document.getElementById('modalEventForm'));
            const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailEvent'));
            const formEvent = document.getElementById('formEvent');
            const btnSave = document.getElementById('btnSubmitEvent');
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;
            let currentEventId = null;

            // Button Add
            document.getElementById('btnAddEvent')?.addEventListener('click', function() {
                resetForm();
                isEditMode = false;
                document.getElementById('modalEventLabel').textContent = 'Buat Event Baru';
                modalForm.show();
            });

            // Button Filter
            document.getElementById('btnFilter')?.addEventListener('click', function() {
                loadEvents(1);
            });

            // Button Reset
            document.getElementById('btnReset')?.addEventListener('click', function() {
                document.getElementById('filter_kategori').value = '';
                document.getElementById('filter_status').value = '';
                document.getElementById('filter_search').value = '';
                loadEvents(1);
            });

            // Filter on Enter
            document.getElementById('filter_search')?.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadEvents(1);
                }
            });

            // View Event
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-view-event')) {
                    const btn = e.target.closest('.btn-view-event');
                    const id = btn.dataset.id;
                    showDetailEvent(id);
                }
            });

            // Edit Event
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-edit-event')) {
                    const btn = e.target.closest('.btn-edit-event');
                    const id = btn.dataset.id;
                    loadEventForEdit(id);
                }
            });

            // Delete Event
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete-event')) {
                    const btn = e.target.closest('.btn-delete-event');
                    const id = btn.dataset.id;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Hapus',
                        text: 'Yakin ingin menghapus event ini?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            deleteEvent(id);
                        }
                    });
                }
            });

            // Edit from Detail
            document.getElementById('btnEditFromDetail')?.addEventListener('click', function() {
                modalDetail.hide();
                if (currentEventId) {
                    loadEventForEdit(currentEventId);
                }
            });

            // Form Submit
            formEvent?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!formEvent.checkValidity()) {
                    formEvent.classList.add('was-validated');
                    return;
                }

                const eventId = document.getElementById('eventId').value;
                let baseUrl = isEditMode ? document.getElementById('baseUrlUpdate').value.replace(
                    '__ID__', eventId) : document.getElementById('baseUrlStore').value;
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;

                btnSave.disabled = true;
                btnSave.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
                const formData = new FormData(formEvent);

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
                        loadEvents(1);
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showAlert('error', data.message || 'Gagal menyimpan event');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan';
                }
            });

            // Preview Gambar Featured
            document.getElementById('gambar_featured')?.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewFeatured').src = e.target.result;
                        document.getElementById('featuredPreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Load Events dengan Pagination
            function loadEvents(page = 1) {
                const kategoriId = document.getElementById('filter_kategori').value;
                const status = document.getElementById('filter_status').value;
                const search = document.getElementById('filter_search').value.trim();

                const params = new URLSearchParams({
                    page: page,
                    kategori_id: kategoriId || '',
                    status: status || '',
                    search: search,
                    ajax: 1,
                    _token_nav: currentToken
                });

                fetch(`{{ route('admin.events.index') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Update table body
                        const newTableBody = doc.querySelector('#eventsTableBody');
                        if (newTableBody) {
                            document.getElementById('eventsTableBody').innerHTML = newTableBody.innerHTML;
                        }

                        // Update pagination
                        const newPagination = doc.querySelector('#paginationContainer');
                        if (newPagination) {
                            document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;

                            // Rebind pagination click events
                            document.querySelectorAll('#paginationContainer a').forEach(link => {
                                link.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const url = new URL(this.href);
                                    const page = url.searchParams.get('page') || 1;
                                    loadEvents(page);
                                });
                            });
                        }

                        // Rebind button events
                        bindEvents();
                    })
                    .catch(err => {
                        console.error(err);
                        showAlert('error', 'Gagal memuat data event');
                    });
            }

            // Show Detail Event
            function showDetailEvent(id) {
                currentEventId = id;
                document.getElementById('detailContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
                modalDetail.show();

                fetch(`{{ route('admin.events.show', '__ID__') }}?ajax=1&_token_nav=${currentToken}`.replace(
                        '__ID__', id), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('detailNamaEvent').textContent = data.event.nama_event;

                            let html = `
                    ${data.event.gambar_featured ? `<img src="{{ asset('storage') }}/${data.event.gambar_featured}" class="w-100 rounded mb-3" alt="${data.event.nama_event}">` : ''}
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Kategori:</strong></p>
                            <span class="lux-badge" style="background: ${data.event.kategori.warna}20; color: ${data.event.kategori.warna};">${data.event.kategori.nama_kategori}</span>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Status:</strong></p>
                            <span class="lux-badge ${data.event.status_label.color}">${data.event.status_label.label}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><i class="ri-calendar-line"></i> <strong>Tanggal Mulai:</strong></p>
                            <p>${new Date(data.event.tanggal_mulai).toLocaleString('id-ID')}</p>
                        </div>
                        ${data.event.tanggal_selesai ? `
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><i class="ri-calendar-line"></i> <strong>Tanggal Selesai:</strong></p>
                                                        <p>${new Date(data.event.tanggal_selesai).toLocaleString('id-ID')}</p>
                                                    </div>
                                                    ` : ''}
                    </div>

                    <div class="mb-3">
                        <p class="mb-1"><i class="ri-map-pin-line"></i> <strong>Lokasi:</strong></p>
                        <p>${data.event.lokasi}</p>
                        ${data.event.alamat_lengkap ? `<p class="text-muted">${data.event.alamat_lengkap}</p>` : ''}
                    </div>

                    <div class="mb-3">
                        <p class="mb-1"><strong>Deskripsi:</strong></p>
                        <p>${data.event.deskripsi}</p>
                    </div>

                    ${data.event.deskripsi_lengkap ? `
                                                <div class="mb-3">
                                                    <p class="mb-1"><strong>Deskripsi Lengkap:</strong></p>
                                                    <div style="line-height: 1.8;">${data.event.deskripsi_lengkap.replace(/\n/g, '<br>')}</div>
                                                </div>
                                                ` : ''}

                    ${data.event.pembicara ? `
                                                <div class="mb-3">
                                                    <p class="mb-1"><strong>Pembicara:</strong></p>
                                                    <p>${data.event.pembicara}</p>
                                                </div>
                                                ` : ''}

                    <div class="row">
                        ${data.event.harga_tiket ? `
                                                    <div class="col-md-6 mb-3">
                                                        <p class="mb-1"><strong>Harga Tiket:</strong></p>
                                                        <p>Rp ${parseInt(data.event.harga_tiket).toLocaleString('id-ID')}</p>
                                                    </div>
                                                    ` : ''}
                    </div>

                    ${data.event.kontak_person || data.event.email_kontak || data.event.whatsapp_kontak ? `
                                                <div class="mb-3 p-3" style="background: #f8fafc; border-radius: var(--radius);">
                                                    <p class="mb-2"><strong>Kontak Informasi:</strong></p>
                                                    ${data.event.kontak_person ? `<p class="mb-1"><i class="ri-user-line"></i> ${data.event.kontak_person}</p>` : ''}
                                                    ${data.event.email_kontak ? `<p class="mb-1"><i class="ri-mail-line"></i> ${data.event.email_kontak}</p>` : ''}
                                                    ${data.event.whatsapp_kontak ? `<p class="mb-1"><i class="ri-whatsapp-line"></i> ${data.event.whatsapp_kontak}</p>` : ''}
                                                </div>
                                                ` : ''}
                `;

                            document.getElementById('detailContent').innerHTML = html;
                        } else {
                            showAlert('error', data.message || 'Gagal memuat detail event');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showAlert('error', 'Terjadi kesalahan saat memuat detail');
                    });
            }

            // Load Event for Edit
            function loadEventForEdit(id) {
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
                        resetForm();
                        isEditMode = true;
                        document.getElementById('modalEventLabel').textContent = 'Edit Event';
                        document.getElementById('eventId').value = data.id;
                        document.getElementById('nama_event').value = data.nama_event || '';
                        document.getElementById('kategori_id').value = data.kategori_id || '';
                        document.getElementById('deskripsi').value = data.deskripsi || '';
                        document.getElementById('deskripsi_lengkap').value = data.deskripsi_lengkap || '';
                        document.getElementById('tanggal_mulai').value = data.tanggal_mulai ? data.tanggal_mulai
                            .replace(' ', 'T') : '';
                        document.getElementById('tanggal_selesai').value = data.tanggal_selesai ? data
                            .tanggal_selesai.replace(' ', 'T') : '';
                        document.getElementById('lokasi').value = data.lokasi || '';
                        document.getElementById('alamat_lengkap').value = data.alamat_lengkap || '';
                        document.getElementById('pembicara').value = data.pembicara || '';
                        document.getElementById('kontak_person').value = data.kontak_person || '';
                        document.getElementById('whatsapp_kontak').value = data.whatsapp_kontak || '';
                        document.getElementById('email_kontak').value = data.email_kontak || '';
                        document.getElementById('harga_tiket').value = data.harga_tiket || 0;
                        document.getElementById('status').value = data.status || 'draft';
                        document.getElementById('chkFeatured').checked = !!data.is_featured;
                        document.getElementById('chkShowWeb').checked = data.show_in_website !== false;

                        if (data.gambar_featured) {
                            document.getElementById('currentFeatured').innerHTML = `
                    <img src="{{ asset('storage') }}/${data.gambar_featured}" style="max-width:280px; border-radius:8px;">
                    <p class="text-muted small mt-2">Gambar featured saat ini</p>
                `;
                        } else {
                            document.getElementById('currentFeatured').innerHTML =
                                '<p class="text-muted small">Belum ada gambar featured</p>';
                        }

                        if (data.gambar_galeri && data.gambar_galeri.length > 0) {
                            let html = '';
                            data.gambar_galeri.forEach(img => {
                                html +=
                                    `<img src="{{ asset('storage') }}/${img}" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:6px;">`;
                            });
                            document.getElementById('currentGallery').innerHTML = html;
                        } else {
                            document.getElementById('currentGallery').innerHTML =
                                '<p class="text-muted small">Belum ada gambar galeri</p>';
                        }

                        modalForm.show();
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data event'));
            }

            function deleteEvent(id) {
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
                            loadEvents();
                        } else {
                            showAlert('error', data.message || 'Gagal menghapus event');
                        }
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            function resetForm() {
                formEvent?.reset();
                formEvent?.classList.remove('was-validated');
                document.getElementById('formErrors')?.classList.add('d-none');
                document.getElementById('eventId').value = '';
                document.getElementById('currentFeatured').innerHTML = '';
                document.getElementById('currentGallery').innerHTML = '';
                document.getElementById('featuredPreview').style.display = 'none';
                document.getElementById('chkFeatured').checked = false;
                document.getElementById('chkShowWeb').checked = true;
                document.getElementById('status').value = 'draft';
            }

            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;
                const bg = type === 'success' ? '#f0fdf4' : '#fef2f2';
                const border = type === 'success' ? 'var(--success)' : 'var(--danger)';
                const color = type === 'success' ? '#166534' : '#991b1b';
                const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';
                container.innerHTML = `
            <div class="lux-card" style="border-left:4px solid ${border};background:${bg};display:flex;align-items:center;gap:10px;padding:12px;">
                <i class="${icon}" style="color:${border};font-size:1.5rem;"></i>
                <span style="color:${color};font-weight:600;">${message}</span>
            </div>
        `;
                setTimeout(() => {
                    if (container.firstElementChild) {
                        container.firstElementChild.style.opacity = '0';
                        container.firstElementChild.style.transition = 'opacity 0.3s';
                        setTimeout(() => container.firstElementChild.remove(), 300);
                    }
                }, 5000);
            }

            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;
                let html = '';
                for (let f in errors) {
                    (Array.isArray(errors[f]) ? errors[f] : [errors[f]]).forEach(m => {
                        html += `<li>${f}: ${m}</li>`;
                    });
                }
                errorList.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            function bindEvents() {}

            bindEvents();
        });
    </script>
@endpush
