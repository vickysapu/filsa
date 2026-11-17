@extends('layouts.admin')

@section('title', 'Data Item')

@section('content')
    <style>
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 1.05rem;
            line-height: 1;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease, transform .1s ease;
        }

        .btn-action:active {
            transform: scale(.94);
        }

        .btn-action:focus-visible {
            outline: 2px solid var(--bs-primary);
            outline-offset: 2px;
        }

        /* Edit, Hubungkan, Migrasi */
        .btn-action-primary {
            color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), .10);
            border-color: rgba(var(--bs-primary-rgb), .20);
        }

        .btn-action-primary:hover {
            color: #fff;
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        /* Hapus */
        .btn-action-danger {
            color: var(--bs-danger);
            background-color: rgba(var(--bs-danger-rgb), .10);
            border-color: rgba(var(--bs-danger-rgb), .20);
        }

        .btn-action-danger:hover {
            color: #fff;
            background-color: var(--bs-danger);
            border-color: var(--bs-danger);
        }

        .btn-action:disabled {
            opacity: .5;
            pointer-events: none;
        }
    </style>
    <div class="page-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">Data Item</h1>
                <p class="text-muted-custom small mb-0">
                    <i class="ri-building-line me-1"></i> {{ auth()->user()->roles->first()?->branch->name ?? '' }}
                </p>
            </div>
            <button type="button" id="btnAdd" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#churchModal"
                onclick="resetForm(currentType)">
                <i class="ri-add-line me-1"></i> <span id="btnAddLabel">Tambah Gereja</span>
            </button>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="itemTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-church-btn" data-bs-toggle="tab" data-bs-target="#tab-church"
                    type="button" role="tab" data-type="church">
                    <i class="ri-building-line me-1"></i> Data Gereja
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-item-btn" data-bs-toggle="tab" data-bs-target="#tab-item" type="button"
                    role="tab" data-type="item">
                    <i class="ri-price-tag-3-line me-1"></i> Data Item
                </button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: GEREJA --}}
            <div class="tab-pane fade show active" id="tab-church" role="tabpanel">
                <div class="card-custom">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">No
                                    </th>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Nama
                                        Gereja / Pendeta</th>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">
                                        Gembala
                                        / PIC</th>
                                    <th
                                        class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($churches as $index => $church)
                                    <tr class="border-bottom border-custom">
                                        <td class="py-3 px-4">{{ $churches->firstItem() + $index }}</td>
                                        <td class="py-3 px-4 fw-medium">{{ $church->name }}</td>
                                        <td class="py-3 px-4">{{ $church->pic_name ?? '-' }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <button type="button" class="btn-action btn-action-primary"
                                                    onclick="editChurch({{ json_encode(['id' => $church->id, 'name' => $church->name, 'pic_name' => $church->pic_name, 'type' => 'church']) }})"
                                                    title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </button>
                                                <button type="button" class="btn-action btn-action-danger"
                                                    onclick="deleteChurch('{{ $church->id }}')" title="Hapus">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-5 text-center text-muted-custom">
                                            <div class="empty-state-icon mb-3 mx-auto"
                                                style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                <i class="ri-building-line"></i>
                                            </div>
                                            <h6 class="fw-semibold mb-2">Belum ada data gereja</h6>
                                            <p class="small mb-0">Silakan tambahkan data gereja/pendeta pertama Anda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($churches->hasPages())
                        <div class="d-flex justify-content-center p-4 border-top border-custom">
                            {{ $churches->fragment('tab-church')->links('pagination::simple-bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB 2: ITEM --}}
            <div class="tab-pane fade" id="tab-item" role="tabpanel">
                <div class="card-custom">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">No
                                    </th>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Nama
                                        Item</th>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Gereja
                                        (Opsional)</th>
                                    <th
                                        class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr class="border-bottom border-custom">
                                        <td class="py-3 px-4">{{ $items->firstItem() + $index }}</td>
                                        <td class="py-3 px-4 fw-medium">{{ $item->name }}</td>
                                        <td class="py-3 px-4">{{ $item->parent_name ?? '-' }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                @if (auth()->user()->hasPermission('finance.report.edit'))
                                                    <button type="button" class="btn-action btn-action-primary"
                                                        onclick="openLink({{ json_encode(['id' => $item->id, 'name' => $item->name, 'church_id' => $item->parent_id]) }})"
                                                        title="Hubungkan ke gereja">
                                                        <i class="ri-links-line"></i>
                                                    </button>
                                                    <button type="button" class="btn-action btn-action-primary"
                                                        onclick="openMigrate({{ json_encode(['id' => $item->id, 'name' => $item->name]) }})"
                                                        title="Migrasi menjadi gereja">
                                                        <i class="ri-arrow-right-up-line"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn-action btn-action-primary"
                                                    onclick="editChurch({{ json_encode(['id' => $item->id, 'name' => $item->name, 'church_id' => $item->parent_id, 'type' => 'item']) }})"
                                                    title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </button>
                                                <button type="button" class="btn-action btn-action-danger"
                                                    onclick="deleteChurch('{{ $item->id }}')" title="Hapus">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-5 text-center text-muted-custom">
                                            <div class="empty-state-icon mb-3 mx-auto"
                                                style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                <i class="ri-price-tag-3-line"></i>
                                            </div>
                                            <h6 class="fw-semibold mb-2">Belum ada data item</h6>
                                            <p class="small mb-0">Contoh: TAHAKA, bisa milik gereja tertentu atau mandiri.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($items->hasPages())
                        <div class="d-flex justify-content-center p-4 border-top border-custom">
                            {{ $items->fragment('tab-item')->links('pagination::simple-bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="churchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="churchForm">
                    @csrf
                    <input type="hidden" id="churchId" name="id">
                    <input type="hidden" id="formType" name="type" value="church">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTitle">Tambah Gereja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom" id="labelName">Nama Gereja /
                                Pendeta <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="churchName"
                                class="form-control form-control-custom" required
                                placeholder="Contoh: GSPDI Immanuel / Pdt. John Doe">
                        </div>

                        {{-- Khusus gereja --}}
                        <div class="mb-3" id="groupPic">
                            <label class="form-label small fw-semibold text-muted-custom">Gembala / Penanggung
                                Jawab</label>
                            <input type="text" name="pic_name" id="churchPic"
                                class="form-control form-control-custom" placeholder="Opsional">
                        </div>

                        {{-- Khusus item --}}
                        <div class="mb-3 d-none" id="groupChurch">
                            <label class="form-label small fw-semibold text-muted-custom">Gereja (Opsional)</label>
                            <select name="church_id" id="itemChurch" class="form-select form-control-custom">
                                <option value="">- Tidak ada / mandiri -</option>
                                @foreach ($churchOptions as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitChurch">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="linkModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="linkForm">
                    <input type="hidden" id="linkId">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold">Hubungkan Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Item</label>
                            <input type="text" id="linkItemName" class="form-control form-control-custom" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Hubungkan ke Gereja</label>
                            <select id="linkChurch" class="form-select form-control-custom">
                                <option value="">- Tidak ada / mandiri -</option>
                                @foreach ($churchOptions as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitLink">
                            <i class="ri-links-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="migrateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="migrateForm">
                    <input type="hidden" id="migrateId">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold">Migrasi Item menjadi Gereja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info small">
                            Nama dan Gembala / PIC terisi otomatis dari teks item. Periksa dan perbaiki bila perlu sebelum
                            disimpan.
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Gereja <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="migrateName" class="form-control form-control-custom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Gembala / Penanggung
                                Jawab</label>
                            <input type="text" id="migratePic" class="form-control form-control-custom">
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitMigrate">
                            <i class="ri-arrow-right-up-line me-1"></i> Migrasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <style> Anda yang lama tetap dipakai di sini --}}

    <script>
        let currentType = 'church';

        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('churchModal');
            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('churchForm');
            const btnSubmit = document.getElementById('btnSubmitChurch');

            // ---- Tab handling ----
            const btnAddLabel = document.getElementById('btnAddLabel');

            function setType(type) {
                currentType = type;
                btnAddLabel.textContent = type === 'item' ? 'Tambah Item' : 'Tambah Gereja';
            }

            document.querySelectorAll('#itemTabs [data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', e => {
                    setType(e.target.dataset.type);
                    history.replaceState(null, '', '#' + e.target.dataset.bsTarget.substring(1));
                });
            });

            // Buka tab sesuai hash (mis. setelah pindah halaman pagination item)
            if (location.hash === '#tab-item' || new URLSearchParams(location.search).has('item_page')) {
                bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-item-btn')).show();
            }

            // ---- Form helpers ----
            function applyType(type) {
                document.getElementById('formType').value = type;
                document.getElementById('groupPic').classList.toggle('d-none', type === 'item');
                document.getElementById('groupChurch').classList.toggle('d-none', type !== 'item');
                document.getElementById('labelName').innerHTML = type === 'item' ?
                    'Nama Item <span class="text-danger">*</span>' :
                    'Nama Gereja / Pendeta <span class="text-danger">*</span>';
                document.getElementById('churchName').placeholder = type === 'item' ?
                    'Contoh: TAHAKA' :
                    'Contoh: GSPDI Immanuel / Pdt. John Doe';
            }

            window.resetForm = function(type = 'church') {
                form.reset();
                document.getElementById('churchId').value = '';
                applyType(type);
                document.getElementById('modalTitle').textContent = type === 'item' ? 'Tambah Item' :
                    'Tambah Gereja';
            };

            window.editChurch = function(data) {
                form.reset();
                applyType(data.type);
                document.getElementById('churchId').value = data.id;
                document.getElementById('churchName').value = data.name;
                document.getElementById('churchPic').value = data.pic_name || '';
                document.getElementById('itemChurch').value = data.church_id || '';
                document.getElementById('modalTitle').textContent = data.type === 'item' ? 'Edit Item' :
                    'Edit Gereja';
                modal.show();
            };

            window.deleteChurch = function(id) {
                if (confirm(
                        'Apakah Anda yakin ingin menghapus data ini? Data yang sudah terikat pada laporan tidak akan hilang, hanya status aktifnya yang berubah.'
                    )) {
                    fetch(`/admin/finance/churches/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Gagal: ' + data.message);
                            }
                        });
                }
            };

            const linkModal = new bootstrap.Modal(document.getElementById('linkModal'));
            const migrateModal = new bootstrap.Modal(document.getElementById('migrateModal'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            async function postJson(url, payload, btn, idleHtml) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    let data = {};
                    try {
                        data = await res.json();
                    } catch (e) {}
                    if (res.ok && data.success) {
                        location.reload();
                        return;
                    }
                    let msg = data.message || 'Terjadi kesalahan (status ' + res.status + ')';
                    if (res.status === 422 && data.errors) {
                        msg = Object.values(data.errors).flat().join('\n');
                    }
                    alert('Gagal: ' + msg);
                } catch (e) {
                    alert('Terjadi kesalahan jaringan');
                }
                btn.disabled = false;
                btn.innerHTML = idleHtml;
            }

            window.openLink = function(data) {
                document.getElementById('linkId').value = data.id;
                document.getElementById('linkItemName').value = data.name;
                document.getElementById('linkChurch').value = data.church_id || '';
                linkModal.show();
            };

            window.openMigrate = function(data) {
                document.getElementById('migrateId').value = data.id;
                document.getElementById('migrateName').value = data.name;
                document.getElementById('migratePic').value = data.name;
                migrateModal.show();
            };

            document.getElementById('linkForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('linkId').value;
                postJson(`/admin/finance/churches/${id}/link`, {
                        church_id: document.getElementById('linkChurch').value || null
                    }, document.getElementById('btnSubmitLink'),
                    '<i class="ri-links-line me-1"></i> Simpan');
            });

            document.getElementById('migrateForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('migrateId').value;
                postJson(`/admin/finance/churches/${id}/migrate`, {
                        name: document.getElementById('migrateName').value,
                        pic_name: document.getElementById('migratePic').value || null
                    }, document.getElementById('btnSubmitMigrate'),
                    '<i class="ri-arrow-right-up-line me-1"></i> Migrasi');
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('churchId').value;
                const url = id ? `/admin/finance/churches/${id}` : '/admin/finance/churches';

                btnSubmit.disabled = true;
                btnSubmit.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';

                const formData = new FormData(form);
                if (id) {
                    formData.append('_method', 'PUT');
                }

                fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // hash tab tetap dipertahankan
                        } else {
                            alert('Gagal: ' + (data.message || 'Validasi gagal'));
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="ri-save-line me-1"></i> Simpan';
                        }
                    })
                    .catch(() => {
                        alert('Terjadi kesalahan jaringan');
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="ri-save-line me-1"></i> Simpan';
                    });
            });
        });
    </script>
@endsection
