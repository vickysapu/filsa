@extends('layouts.admin')

@section('title', 'Manajemen Events')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Events</h1>
                <p class="text-muted-custom mb-0">Kelola event gereja untuk website public</p>
            </div>
            @if (auth()->user()->hasPermission('event.create'))
                <button type="button" class="btn btn-custom" id="btnAddEvent">
                    <i class="ri-add-line me-1"></i> Buat Event Baru
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
                        @if ($isSuperAdmin)
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-semibold text-muted-custom mb-2">Cabang</label>
                                <select id="filter_branch" class="form-select form-select-custom">
                                    <option value="">Semua Cabang</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Kategori</label>
                            <select id="filter_kategori" class="form-select form-select-custom">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoris as $kat)
                                    <option value="{{ $kat->id }}"
                                        {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                            <select id="filter_status" class="form-select form-select-custom">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published
                                </option>
                                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Cari</label>
                            <div class="position-relative">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="filter_search" class="form-control form-control-custom ps-5"
                                    value="{{ request('search') }}" placeholder="Nama event, lokasi...">
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

        <!-- Events Table -->
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Event</th>
                            @if ($isSuperAdmin)
                                <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Cabang
                                </th>
                            @endif
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kategori</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tanggal</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Lokasi</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">
                        @forelse($events as $evt)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($evt->gambar_featured)
                                            <img src="{{ asset('storage/' . $evt->gambar_featured) }}"
                                                alt="{{ $evt->nama_event }}" class="event-thumbnail">
                                        @else
                                            <div class="event-thumbnail event-thumbnail-placeholder">
                                                <i class="ri-calendar-event-line"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $evt->nama_event }}</div>
                                            <small class="text-muted-custom">{{ Str::limit($evt->deskripsi, 60) }}</small>
                                        </div>
                                    </div>
                                </td>
                                @if ($isSuperAdmin)
                                    <td class="py-3 px-4">
                                        <span
                                            class="badge bg-light text-dark border">{{ $evt->branch->name ?? '-' }}</span>
                                    </td>
                                @endif
                                <td class="py-3 px-4">
                                    <span class="badge badge-category"
                                        style="background: {{ $evt->kategori->warna }}15; color: {{ $evt->kategori->warna }}; border: 1px solid {{ $evt->kategori->warna }}40;">
                                        {{ $evt->kategori->nama_kategori }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-medium">
                                        <i class="ri-calendar-line me-1"></i> {{ $evt->tanggal_mulai->format('d M Y') }}
                                    </div>
                                    <small class="text-muted-custom">{{ $evt->tanggal_mulai->format('H:i') }} WIB</small>
                                </td>
                                <td class="py-3 px-4">{{ $evt->lokasi }}</td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-status-{{ $evt->status }}">
                                        {{ $evt->status_label['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn-action btn-action-info btn-view-event"
                                            data-id="{{ $evt->id }}" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        @if (auth()->user()->hasPermission('event.edit'))
                                            <button type="button" class="btn-action btn-action-primary btn-edit-event"
                                                data-id="{{ $evt->id }}" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endif
                                        @if (auth()->user()->hasPermission('event.delete'))
                                            <button type="button" class="btn-action btn-action-danger btn-delete-event"
                                                data-id="{{ $evt->id }}" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 7 : 6 }}" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-calendar-event-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Belum ada event</h6>
                                        <p class="text-muted-custom small mb-0">Buat event pertama Anda untuk mulai
                                            mengelola kegiatan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($events->hasPages())
                <div class="d-flex justify-content-center p-4 border-top border-custom" id="paginationContainer">
                    {{ $events->links('pagination::simple-bootstrap-4') }}
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Form Event -->
    <div class="modal fade" id="modalEventForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <form id="formEvent" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="eventId">
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalEventLabel">Buat Event Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="row g-4">
                            <!-- Left Column: Basic Info -->
                            <div class="col-lg-6">
                                <div class="card card-custom">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-information-line me-2 text-primary"></i>Informasi Dasar
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        @if ($isSuperAdmin)
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted-custom">Cabang <span
                                                        class="text-danger">*</span></label>
                                                <select name="branch_id" id="branch_id"
                                                    class="form-select form-select-custom" required>
                                                    <option value="">Pilih Cabang...</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Nama Event <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="nama_event" id="nama_event"
                                                class="form-control form-control-custom" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Kategori Event
                                                <span class="text-danger">*</span></label>
                                            <select name="kategori_id" id="kategori_id"
                                                class="form-select form-select-custom" required>
                                                <option value="">Pilih Kategori...</option>
                                                @foreach ($kategoris as $kategori)
                                                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi Singkat
                                                <span class="text-danger">*</span></label>
                                            <textarea name="deskripsi" id="deskripsi" class="form-control form-control-custom textarea-custom" rows="3"
                                                required></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi
                                                Lengkap</label>
                                            <textarea name="deskripsi_lengkap" id="deskripsi_lengkap" class="form-control form-control-custom textarea-custom"
                                                rows="4"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Schedule & Contact -->
                            <div class="col-lg-6">
                                <div class="card card-custom mb-4">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-calendar-schedule-line me-2 text-primary"></i>Jadwal & Lokasi
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Tanggal Mulai
                                                    <span class="text-danger">*</span></label>
                                                <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai"
                                                    class="form-control form-control-custom" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Tanggal
                                                    Selesai</label>
                                                <input type="datetime-local" name="tanggal_selesai" id="tanggal_selesai"
                                                    class="form-control form-control-custom">
                                            </div>
                                        </div>

                                        <div class="mb-3 mt-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Lokasi <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="lokasi" id="lokasi"
                                                class="form-control form-control-custom" required>
                                        </div>

                                        <div>
                                            <label class="form-label small fw-semibold text-muted-custom">Alamat
                                                Lengkap</label>
                                            <textarea name="alamat_lengkap" id="alamat_lengkap" class="form-control form-control-custom textarea-custom"
                                                rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-custom mb-4">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-user-star-line me-2 text-primary"></i>Kontak & Pembicara
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label
                                                class="form-label small fw-semibold text-muted-custom">Pembicara/Narasumber</label>
                                            <input type="text" name="pembicara" id="pembicara"
                                                class="form-control form-control-custom">
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Kontak
                                                    Person</label>
                                                <input type="text" name="kontak_person" id="kontak_person"
                                                    class="form-control form-control-custom">
                                            </div>
                                            <div class="col-6">
                                                <label
                                                    class="form-label small fw-semibold text-muted-custom">WhatsApp</label>
                                                <input type="text" name="whatsapp_kontak" id="whatsapp_kontak"
                                                    class="form-control form-control-custom">
                                            </div>
                                        </div>

                                        <div class="mb-3 mt-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Email
                                                Kontak</label>
                                            <input type="email" name="email_kontak" id="email_kontak"
                                                class="form-control form-control-custom">
                                        </div>

                                        <div>
                                            <label class="form-label small fw-semibold text-muted-custom">Harga Tiket
                                                (Rp)</label>
                                            <input type="number" name="harga_tiket" id="harga_tiket"
                                                class="form-control form-control-custom" min="0" step="1000">
                                        </div>
                                    </div>
                                </div>

                                <!-- Images & Options -->
                                <div class="card card-custom">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-image-line me-2 text-primary"></i>Media & Opsi
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Gambar
                                                Featured</label>
                                            <div id="currentFeatured" class="mb-2"></div>
                                            <input type="file" name="gambar_featured" id="gambar_featured"
                                                class="form-control form-control-custom" accept="image/*">
                                            <div id="featuredPreview" class="mt-2 d-none">
                                                <img id="previewFeatured" class="img-preview">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Galeri</label>
                                            <div id="currentGallery" class="d-flex flex-wrap gap-2 mb-2"></div>
                                            <input type="file" name="gambar_galeri[]" id="gambar_galeri"
                                                class="form-control form-control-custom" accept="image/*" multiple>
                                            <small class="text-muted-custom">Pilih multiple untuk upload beberapa
                                                gambar</small>
                                        </div>

                                        <hr class="border-custom my-3">

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Status</label>
                                            <select name="status" id="status" class="form-select form-select-custom">
                                                <option value="draft">Draft</option>
                                                <option value="published">Published</option>
                                                <option value="ongoing">Berlangsung</option>
                                                <option value="completed">Selesai</option>
                                                <option value="cancelled">Dibatalkan</option>
                                            </select>
                                        </div>

                                        <div class="form-check form-switch-custom mb-2">
                                            <input type="checkbox" name="is_featured" id="chkFeatured"
                                                class="form-check-input-custom" value="1">
                                            <label class="form-check-label-custom" for="chkFeatured">Featured
                                                Event</label>
                                        </div>
                                        <div class="form-check form-switch-custom">
                                            <input type="checkbox" name="show_in_website" id="chkShowWeb"
                                                class="form-check-input-custom" value="1" checked>
                                            <label class="form-check-label-custom" for="chkShowWeb">Tampilkan di
                                                Website</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitEvent">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Event -->
    <div class="modal fade" id="modalDetailEvent" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="detailNamaEvent">Detail Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="detailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted-custom mb-0">Memuat detail event...</p>
                    </div>
                </div>
                <div class="modal-footer border-top border-custom py-3 px-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-custom" id="btnEditFromDetail">
                        <i class="ri-pencil-line me-1"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

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

        /* ===== EVENT THUMBNAIL ===== */
        .event-thumbnail {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            border: 2px solid var(--bg-surface);
            box-shadow: var(--shadow-sm);
        }

        .event-thumbnail-placeholder {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-blue-hover) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* ===== CATEGORY BADGE ===== */
        .badge-category {
            padding: 0.35em 0.65em;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-draft {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-published {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-ongoing {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-completed {
            background: rgba(139, 92, 246, 0.15);
            color: #7c3aed;
            border: 1px solid rgba(139, 92, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
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

        .form-check-input-custom[type="checkbox"] {
            border-radius: 4px;
        }

        .form-check-label-custom {
            font-size: 0.9rem;
            color: var(--text-primary);
            cursor: pointer;
            margin-left: 0.35rem;
        }

        /* ===== IMAGE PREVIEW ===== */
        .img-preview {
            width: 100%;
            max-width: 280px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        #currentGallery img {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            border: 1px solid var(--border-color);
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

        [data-theme="dark"] .event-thumbnail {
            border-color: var(--bg-sidebar);
        }

        [data-theme="dark"] .event-thumbnail-placeholder {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-blue-hover) 100%);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .alert-custom.success {
            background: rgba(34, 197, 94, 0.15);
        }

        [data-theme="dark"] .alert-custom.error {
            background: rgba(239, 68, 68, 0.15);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .event-thumbnail {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
            }

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
            const modalFormEl = document.getElementById('modalEventForm');
            const modalDetailEl = document.getElementById('modalDetailEvent');

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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;
            let currentEventId = null;

            // ===== EVENT: Add Event =====
            document.getElementById('btnAddEvent')?.addEventListener('click', function() {
                resetForm();
                isEditMode = false;
                document.getElementById('formMethod').value = 'POST';
                document.getElementById('modalEventLabel').textContent = 'Buat Event Baru';
                if (modalForm) modalForm.show();
            });

            // ===== EVENT: Filter =====
            document.getElementById('btnFilter')?.addEventListener('click', function() {
                loadEvents(1);
            });

            // ===== EVENT: Reset Filter =====
            document.getElementById('btnReset')?.addEventListener('click', function() {
                document.getElementById('filter_kategori').value = '';
                document.getElementById('filter_status').value = '';
                document.getElementById('filter_search').value = '';
                @if ($isSuperAdmin)
                    document.getElementById('filter_branch').value = '';
                @endif
                loadEvents(1);
            });

            // ===== EVENT: Search on Enter =====
            document.getElementById('filter_search')?.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadEvents(1);
                }
            });

            @if ($isSuperAdmin)
                document.getElementById('filter_branch')?.addEventListener('change', function() {
                    loadEvents(1);
                });
            @endif

            // ===== EVENT: View Event (delegation) =====
            document.addEventListener('click', function(e) {
                const viewBtn = e.target.closest('.btn-view-event');
                if (viewBtn) {
                    e.preventDefault();
                    showDetailEvent(viewBtn.dataset.id);
                }
            });

            // ===== EVENT: Edit Event (delegation) =====
            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.btn-edit-event');
                if (editBtn) {
                    e.preventDefault();
                    loadEventForEdit(editBtn.dataset.id);
                }
            });

            // ===== EVENT: Delete Event (delegation) =====
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-event');
                if (deleteBtn) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Hapus',
                        text: 'Yakin ingin menghapus event ini?',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) deleteEvent(deleteBtn.dataset.id);
                    });
                }
            });

            // ===== EVENT: Edit from Detail =====
            document.getElementById('btnEditFromDetail')?.addEventListener('click', function() {
                if (modalDetail) modalDetail.hide();
                if (currentEventId) {
                    setTimeout(() => loadEventForEdit(currentEventId), 300);
                }
            });

            // ===== EVENT: Form Submit =====
            const formEvent = document.getElementById('formEvent');
            const btnSave = document.getElementById('btnSubmitEvent');

            formEvent?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!formEvent.checkValidity()) {
                    formEvent.classList.add('was-validated');
                    return;
                }

                const eventId = document.getElementById('eventId').value;
                let url = isEditMode ? `/admin/events/${eventId}` : '/admin/events';

                if (btnSave) {
                    btnSave.disabled = true;
                    btnSave.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

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

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (modalForm) modalForm.hide();
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
                    if (btnSave) {
                        btnSave.disabled = false;
                        btnSave.innerHTML = '<i class="ri-save-line me-1"></i> Simpan';
                    }
                }
            });

            // ===== EVENT: Featured Image Preview =====
            document.getElementById('gambar_featured')?.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewFeatured').src = e.target.result;
                        document.getElementById('featuredPreview').classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // ===== FUNCTION: Load Events =====
            function loadEvents(page = 1) {
                const params = new URLSearchParams({
                    page: page,
                    @if ($isSuperAdmin)
                        branch_id: document.getElementById('filter_branch')?.value || '',
                    @endif
                    kategori_id: document.getElementById('filter_kategori')?.value || '',
                    status: document.getElementById('filter_status')?.value || '',
                    search: document.getElementById('filter_search')?.value?.trim() || '',
                    ajax: 1
                });

                const tableBody = document.getElementById('eventsTableBody');
                if (tableBody) {
                    tableBody.innerHTML = `
                <tr>
                    <td colspan="{{ $isSuperAdmin ? 7 : 6 }}" class="py-5 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted-custom mt-3 mb-0">Memuat data event...</p>
                    </td>
                </tr>`;
                }

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
                        if (newTableBody && tableBody) {
                            tableBody.innerHTML = newTableBody.innerHTML;
                        }

                        // Update pagination
                        const newPagination = doc.querySelector('#paginationContainer');
                        const paginationEl = document.getElementById('paginationContainer');
                        if (newPagination && paginationEl) {
                            paginationEl.innerHTML = newPagination.innerHTML;
                            bindPaginationEvents();
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showAlert('error', 'Gagal memuat data event');
                    });
            }

            // ===== FUNCTION: Bind Pagination Events =====
            function bindPaginationEvents() {
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        const page = url.searchParams.get('page') || 1;
                        loadEvents(page);
                    });
                });
            }

            // ===== FUNCTION: Show Detail Event =====
            function showDetailEvent(id) {
                currentEventId = id;
                const detailContent = document.getElementById('detailContent');

                if (detailContent) {
                    detailContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted-custom mb-0">Memuat detail event...</p>
                </div>`;
                }

                if (modalDetail) modalDetail.show();

                fetch(`/admin/events/${id}?ajax=1`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            showAlert('error', data.message || 'Gagal memuat detail event');
                            return;
                        }

                        const evt = data.event;
                        document.getElementById('detailNamaEvent').textContent = evt.nama_event;

                        const featuredImg = evt.gambar_featured ?
                            `<img src="/storage/${evt.gambar_featured}" class="img-fluid rounded mb-4" alt="${evt.nama_event}">` :
                            '';

                        const categoryBadge =
                            `<span class="badge badge-category" style="background: ${evt.kategori.warna}15; color: ${evt.kategori.warna}; border: 1px solid ${evt.kategori.warna}40;">${evt.kategori.nama_kategori}</span>`;
                        const statusBadge =
                            `<span class="badge badge-status-${evt.status}">${evt.status_label.label}</span>`;

                        const endDate = evt.tanggal_selesai ?
                            `<div class="col-md-6"><p class="small text-muted-custom mb-1">Tanggal Selesai</p><p class="fw-medium">${new Date(evt.tanggal_selesai).toLocaleString('id-ID')}</p></div>` :
                            '';

                        const address = evt.alamat_lengkap ?
                            `<p class="text-muted-custom small">${evt.alamat_lengkap}</p>` :
                            '';

                        const fullDesc = evt.deskripsi_lengkap ?
                            `<div class="mt-4"><p class="small text-muted-custom fw-semibold mb-2">Deskripsi Lengkap</p><div style="line-height: 1.8; color: var(--text-primary);">${evt.deskripsi_lengkap.replace(/\n/g, '<br>')}</div></div>` :
                            '';

                        const speaker = evt.pembicara ?
                            `<div class="col-md-6"><p class="small text-muted-custom mb-1">Pembicara</p><p class="fw-medium">${evt.pembicara}</p></div>` :
                            '';

                        const price = evt.harga_tiket ?
                            `<div class="col-md-6"><p class="small text-muted-custom mb-1">Harga Tiket</p><p class="fw-medium text-primary">Rp ${parseInt(evt.harga_tiket).toLocaleString('id-ID')}</p></div>` :
                            '';

                        const contacts = (evt.kontak_person || evt.email_kontak || evt.whatsapp_kontak) ?
                            `<div class="mt-4 p-3 rounded border" style="background: var(--bg-body); border-color: var(--border-color);">
                    <p class="small fw-semibold mb-3">Kontak Informasi</p>
                    ${evt.kontak_person ? `<p class="mb-2"><i class="ri-user-line me-2 text-muted-custom"></i>${evt.kontak_person}</p>` : ''}
                    ${evt.email_kontak ? `<p class="mb-2"><i class="ri-mail-line me-2 text-muted-custom"></i>${evt.email_kontak}</p>` : ''}
                    ${evt.whatsapp_kontak ? `<p class="mb-0"><i class="ri-whatsapp-line me-2 text-muted-custom"></i>${evt.whatsapp_kontak}</p>` : ''}
                </div>` :
                            '';

                        detailContent.innerHTML = `
                ${featuredImg}
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <p class="small text-muted-custom mb-1">Kategori</p>
                        ${categoryBadge}
                    </div>
                    <div class="col-md-6">
                        <p class="small text-muted-custom mb-1">Status</p>
                        ${statusBadge}
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <p class="small text-muted-custom mb-1"><i class="ri-calendar-line me-1"></i> Tanggal Mulai</p>
                        <p class="fw-medium mb-0">${new Date(evt.tanggal_mulai).toLocaleString('id-ID')}</p>
                    </div>
                    ${endDate}
                </div>

                <div class="mb-4">
                    <p class="small text-muted-custom mb-1"><i class="ri-map-pin-line me-1"></i> Lokasi</p>
                    <p class="fw-medium mb-0">${evt.lokasi}</p>
                    ${address}
                </div>

                <div class="mb-4">
                    <p class="small text-muted-custom mb-2">Deskripsi</p>
                    <p class="mb-0">${evt.deskripsi}</p>
                </div>

                ${fullDesc}

                <div class="row g-4 mb-4">
                    ${speaker}
                    ${price}
                </div>

                ${contacts}
            `;
                    })
                    .catch(err => {
                        console.error(err);
                        showAlert('error', 'Terjadi kesalahan saat memuat detail');
                    });
            }

            // ===== FUNCTION: Load Event for Edit =====
            function loadEventForEdit(id) {
                fetch(`/admin/events/${id}/edit?ajax=1`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            showAlert('error', data.message || 'Gagal memuat data');
                            return;
                        }

                        resetForm();
                        isEditMode = true;
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('modalEventLabel').textContent = 'Edit Event';
                        document.getElementById('eventId').value = data.id;

                        // Fill form fields
                        @if ($isSuperAdmin)
                            document.getElementById('branch_id').value = data.branch_id || '';
                        @endif
                        document.getElementById('nama_event').value = data.nama_event || '';
                        document.getElementById('kategori_id').value = data.kategori_id || '';
                        document.getElementById('deskripsi').value = data.deskripsi || '';
                        document.getElementById('deskripsi_lengkap').value = data.deskripsi_lengkap || '';

                        // Format dates for datetime-local input (YYYY-MM-DDTHH:mm)
                        if (data.tanggal_mulai) {
                            document.getElementById('tanggal_mulai').value = data.tanggal_mulai;
                        }
                        if (data.tanggal_selesai) {
                            document.getElementById('tanggal_selesai').value = data.tanggal_selesai;
                        }

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

                        // Featured image
                        const currentFeatured = document.getElementById('currentFeatured');
                        if (data.gambar_featured) {
                            currentFeatured.innerHTML = `
                    <img src="/storage/${data.gambar_featured}" class="img-preview mb-2">
                    <p class="text-muted-custom small mb-0">Gambar featured saat ini</p>`;
                        } else {
                            currentFeatured.innerHTML =
                                '<p class="text-muted-custom small mb-0">Belum ada gambar featured</p>';
                        }

                        // Gallery images
                        const currentGallery = document.getElementById('currentGallery');
                        if (data.gambar_galeri && data.gambar_galeri.length > 0) {
                            let html = '';
                            data.gambar_galeri.forEach(img => {
                                html += `<img src="/storage/${img}" alt="Gallery">`;
                            });
                            currentGallery.innerHTML = html;
                        } else {
                            currentGallery.innerHTML =
                                '<p class="text-muted-custom small mb-0">Belum ada gambar galeri</p>';
                        }

                        if (modalForm) modalForm.show();
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data event'));
            }

            // ===== FUNCTION: Delete Event =====
            function deleteEvent(id) {
                fetch(`/admin/events/${id}`, {
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

            // ===== FUNCTION: Reset Form =====
            function resetForm() {
                const form = document.getElementById('formEvent');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }

                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');

                document.getElementById('eventId').value = '';
                document.getElementById('currentFeatured').innerHTML = '';
                document.getElementById('currentGallery').innerHTML = '';
                document.getElementById('featuredPreview').classList.add('d-none');
                document.getElementById('chkFeatured').checked = false;
                document.getElementById('chkShowWeb').checked = true;
                document.getElementById('status').value = 'draft';
            }

            // ===== FUNCTION: Show Alert =====
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;

                const alertClass = type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';

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
            loadEvents();
            bindPaginationEvents();
        });
    </script>
@endsection
