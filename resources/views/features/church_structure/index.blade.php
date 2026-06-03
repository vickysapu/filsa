@extends('layouts.admin')

@section('title', 'Struktur Gereja')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="ri-org-chart me-2"></i>Struktur Gereja</h4>
            <small class="text-muted-custom">Kelola hierarki dan penempatan jemaat dalam struktur organisasi</small>
        </div>
        <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalAdd">
            <i class="ri-add-line me-1"></i>Tambah Posisi
        </button>
    </div>

    <div class="row g-4">
        <div class="col-lg-3">
            <div class="card card-custom p-3">
                <form id="filterForm" class="space-y-3">
                    <div>
                        <label class="form-label small">Cari</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Posisi atau nama..." value="{{ request('search') }}">
                    </div>
                    <div>
                        <label class="form-label small">Level</label>
                        <select name="position_level" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="gembala" {{ request('position_level') == 'gembala' ? 'selected' : '' }}>Gembala
                            </option>
                            <option value="pengurus" {{ request('position_level') == 'pengurus' ? 'selected' : '' }}>
                                Pengurus</option>
                            <option value="pelayan" {{ request('position_level') == 'pelayan' ? 'selected' : '' }}>Pelayan
                            </option>
                            <option value="staff" {{ request('position_level') == 'staff' ? 'selected' : '' }}>Staff
                            </option>
                            <option value="lainnya" {{ request('position_level') == 'lainnya' ? 'selected' : '' }}>Lainnya
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-custom w-100">Filter</button>
                    <a href="{{ route('admin.church_structure.index') }}" class="btn btn-sm btn-light w-100 mt-1">Reset</a>
                </form>
            </div>
            <div class="card card-custom p-3 mt-3">
                <h6 class="mb-3"><i class="ri-info-line me-1"></i>Petunjuk</h6>
                <ul class="small mb-0 ps-3">
                    <li>Drag kotak ke kotak lain untuk menjadikannya bawahan</li>
                    <li>Klik kotak untuk edit atau hapus</li>
                    <li>Struktur tersimpan otomatis setelah drag & drop</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom" id="visualizationCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="ri-tree me-1"></i>Visualisasi Struktur</span>
                    <div class="d-flex gap-2">

                        <button class="btn btn-sm btn-outline-dark" id="btnFullscreen">
                            <i class="ri-fullscreen-line"></i>
                            Perbesar
                        </button>

                        <button class="btn btn-sm btn-outline-secondary" id="btnRefresh">
                            <i class="ri-refresh-line"></i>
                            Refresh
                        </button>

                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="structureChart" class="structure-chart"></div>
                </div>
            </div>

            <div class="card card-custom mt-4 border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0 fw-semibold">
                            <i class="ri-list-check-3 me-2 text-primary"></i>
                            Daftar Struktur Posisi
                        </h5>
                        <small class="text-muted">
                            Klik posisi untuk melihat bawahan
                        </small>
                    </div>

                    <button class="btn btn-sm btn-outline-primary" id="btnReloadAccordion">
                        <i class="ri-refresh-line"></i>
                        Reload
                    </button>
                </div>

                <div class="card-body p-3">
                    <div id="accordionStructureContainer">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            Memuat struktur...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-custom">
                <form id="formAdd" action="{{ route('admin.church_structure.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ri-add-circle-line me-2"></i>Tambah Posisi Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Posisi *</label>
                            <input type="text" name="position_name" class="form-control" required maxlength="100"
                                placeholder="Contoh: Ketua Majelis">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Level *</label>

                                <select name="position_level_select" class="form-select" id="position_level_add_select" required>
                                    <option value="gembala">Gembala</option>
                                    <option value="pengurus">Pengurus</option>
                                    <option value="pelayan">Pelayan</option>
                                    <option value="staff">Staff</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>

                                <input 
                                    type="text"
                                    name="position_level"
                                    id="position_level_add_custom"
                                    class="form-control mt-2 d-none"
                                    placeholder="Masukkan level custom"
                                    maxlength="100"
                                >

                                <input 
                                    type="hidden"
                                    id="position_level_add_hidden"
                                    name="position_level"
                                >
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Induk Posisi (Multi-select)</label>
                                <select name="parent_id[]" class="form-select" id="parent_id" multiple>
                                    <option value="">-- Pilih Posisi Induk --</option>
                                </select>
                                <small class="form-text text-muted d-block mt-1">Gunakan Ctrl+Click untuk memilih multiple</small>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Penanggung Jawab (Jemaat)</label>
                            <select name="jemaat_id" class="form-select" id="jemaat_id_add">
                                <option value="">-- Pilih Jemaat --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="500"
                                placeholder="Tugas dan tanggung jawab..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prioritas</label>

                            <div class="d-flex gap-4 flex-wrap">

                                <div class="form-check">
                                    <input 
                                        class="form-check-input"
                                        type="radio"
                                        name="priority"
                                        id="priority_primary"
                                        value="primer"
                                    >

                                    <label class="form-check-label" for="priority_primary">
                                        ⭐ Primer
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input 
                                        class="form-check-input"
                                        type="radio"
                                        name="priority"
                                        id="priority_secondary"
                                        value="sekunder"
                                    >

                                    <label class="form-check-label" for="priority_secondary">
                                        ⭐ Sekunder
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input 
                                        class="form-check-input"
                                        type="radio"
                                        name="priority"
                                        id="priority_none"
                                        value=""
                                        checked
                                    >

                                    <label class="form-check-label" for="priority_none">
                                        Tidak Diprioritaskan
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                value="1" checked>
                            <label class="form-check-label" for="is_active">Posisi Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom"><i class="ri-save-line me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-custom">
                <form id="formEdit" method="POST">
                    @csrf @method('POST')
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ri-edit-line me-2"></i>Edit Posisi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Posisi *</label>
                            <input type="text" name="position_name" id="edit_position_name" class="form-control"
                                required maxlength="100">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Level *</label>

                                <select name="position_level_select" id="edit_position_level_select" class="form-select" required>
                                    <option value="gembala">Gembala</option>
                                    <option value="pengurus">Pengurus</option>
                                    <option value="pelayan">Pelayan</option>
                                    <option value="staff">Staff</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>

                                <input 
                                    type="text"
                                    id="edit_position_level_custom"
                                    class="form-control mt-2 d-none"
                                    placeholder="Masukkan level custom"
                                    maxlength="100"
                                >

                                <input 
                                    type="hidden"
                                    name="position_level"
                                    id="edit_position_level_hidden"
                                >
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Induk Posisi (Multi-select)</label>
                                <div id="currentParentsInfo" class="small text-primary mt-1"></div>
                                <select name="parent_id[]" id="edit_parent_id" class="form-select" multiple>
                                    <option value="">-- Pilih Posisi Induk --</option>
                                </select>
                                <small class="form-text text-muted d-block mt-1">Gunakan Ctrl+Click untuk memilih multiple</small>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Penanggung Jawab (Jemaat)</label>
                            <select name="jemaat_id" class="form-select" id="edit_jemaat_id">
                                <option value="">-- Pilih Jemaat --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prioritas</label>

                            <div class="d-flex gap-4 flex-wrap">

                                <div class="form-check">
                                    <input 
                                        class="form-check-input"
                                        type="radio"
                                        name="priority"
                                        id="edit_priority_primary"
                                        value="primer"
                                    >

                                    <label class="form-check-label" for="edit_priority_primary">
                                        ⭐ Primer
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input 
                                        class="form-check-input"
                                        type="radio"
                                        name="priority"
                                        id="edit_priority_secondary"
                                        value="sekunder"
                                    >

                                    <label class="form-check-label" for="edit_priority_secondary">
                                        ⭐ Sekunder
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input 
                                        class="form-check-input"
                                        type="radio"
                                        name="priority"
                                        id="edit_priority_none"
                                        value=""
                                    >

                                    <label class="form-check-label" for="edit_priority_none">
                                        Tidak Diprioritaskan
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active"
                                value="1">
                            <label class="form-check-label" for="edit_is_active">Posisi Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom"><i class="ri-save-line me-1"></i>Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .structure-chart {
            min-height: 700px;
            background: #f8fafc;
            position: relative;
            overflow: auto;
            padding: 40px;
            border-radius: 8px;
        }

        .org-node {
            position: absolute;
            width: 200px;
            padding: 10px 12px;
            background: white;
            border: 2px solid #3b82f6;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: move;
            transition: all 0.2s ease;
            z-index: 10;
            user-select: none;
            text-align: center;
        }

        .org-node:hover {
            border-color: #1d4ed8;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .org-node.dragging {
            opacity: 0.7;
            border-style: dashed;
            z-index: 1000;
            cursor: grabbing;
        }

        .org-node-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .org-node-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin: 0 auto;
        }

        .org-node-title {
            font-weight: 600;
            font-size: 0.85rem;
            line-height: 1.3;
            margin-top: 6px;
        }

        .org-node-subtitle {
            font-size: 0.75rem;
            color: #64748b;
        }

        .org-node-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 9999px;
            color: white;
            font-weight: 500;
        }

        .org-connector {
            position: absolute;
            pointer-events: none;
            z-index: 1;
        }

        /* Multi-select styling untuk parent */
        select[multiple] {
            min-height: 120px;
            padding: 8px !important;
        }

        select[multiple] option {
            padding: 6px;
            line-height: 1.5;
        }

        select[multiple] option:checked {
            background: linear-gradient(#3b82f6, #3b82f6);
            background-color: #3b82f6 !important;
            color: white;
        }

        .drop-zone {
            height: 35px;
            border: 2px dashed #94a3b8;
            border-radius: 4px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: #64748b;
            transition: all 0.2s;
            pointer-events: auto;
            cursor: pointer;
            user-select: none;
            background: transparent;
        }

        .drop-zone:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
            border-width: 2px;
        }

        .drop-zone.active {
            border-color: #22c55e;
            background: #f0fdf4;
            color: #16a34a;
            border-width: 2px;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.3);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 500px;
            color: #64748b;
        }

        .org-node.drop-hover {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        .org-node {
            transition: transform 0.1s ease;
        }

        .org-node.drop-hover {
            transform: scale(1.05);
        }

        .structure-accordion .accordion-item {
            border: none;
            margin-bottom: 10px;
            border-radius: 14px !important;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
        }

        .structure-accordion .accordion-button {
            background: white;
            border: none;
            padding: 16px 18px;
            box-shadow: none !important;
        }

        .structure-accordion .accordion-button:not(.collapsed) {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .structure-accordion .accordion-button::after {
            transform: scale(.8);
        }

        .structure-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .structure-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .structure-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .structure-title {
            font-weight: 600;
            font-size: .95rem;
        }

        .structure-subtitle {
            font-size: .8rem;
            color: #64748b;
        }

        .structure-actions {
            display: flex;
            gap: 6px;
        }

        .structure-child {
            margin-left: 22px;
            border-left: 2px dashed #dbeafe;
            padding-left: 16px;
            margin-top: 12px;
        }

        .structure-badge {
            font-size: .7rem;
            padding: 5px 10px;
            border-radius: 999px;
        }

        .empty-children {
            font-size: .8rem;
            color: #94a3b8;
            padding: 8px 12px;
        }

        .browser-fullscreen {
            position: fixed !important;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            z-index: 99999;
            margin: 0 !important;
            width: auto !important;
            height: auto !important;
            background: white;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
        }

        .browser-fullscreen .structure-chart {
            height: calc(100vh - 90px);
            min-height: calc(100vh - 90px);
        }

        .priority-star {
            position: absolute;
            top: -10px;
            left: -10px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
            z-index: 20;
        }

        .priority-primer {
            background: #facc15;
            color: #854d0e;
        }

        .priority-sekunder {
            background: #d1d5db;
            color: #374151;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const churchStructureBaseUrl =
            "{{ url('/admin/church-structure') }}";
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getItemId = (item) => item.id;
            let draggedItem = null;
            let isDragging = false;
            let startX = 0;
            let startY = 0;
            let suppressClick = false;

            let structuresData = [];
            let jemaatsData = [];
            const chartContainer = document.getElementById('structureChart');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const initJemaatSelect = (selectId, selectedId = null) => {

                const select = document.getElementById(selectId);

                if (!select) return;

                select.innerHTML = '<option value="">-- Pilih Jemaat --</option>';

                jemaatsData.forEach(jemaat => {

                    const option = document.createElement('option');

                    option.value = jemaat.id;

                    option.textContent = jemaat.nama_lengkap;

                    if (selectedId && String(selectedId) === String(jemaat.id)) {
                        option.selected = true;
                    }

                    select.appendChild(option);
                });
            };

            const loadJemaats = async () => {
                try {
                    const res = await fetch("{{ route('admin.jemaat.api.list') }}");
                    if (res.ok) jemaatsData = await res.json();
                } catch (e) {
                    console.error(e);
                }
            };

            const handlePriorityValidation = (selectId, prefix = '') => {

                const jemaatSelect = document.getElementById(selectId);

                const primer = document.getElementById(`${prefix}priority_primary`);
                const sekunder = document.getElementById(`${prefix}priority_secondary`);
                const none = document.getElementById(`${prefix}priority_none`);

                const updateState = () => {

                    const hasJemaat = jemaatSelect.value !== '';

                    primer.disabled = !hasJemaat;
                    sekunder.disabled = !hasJemaat;

                    // kalau jemaat kosong -> reset ke none
                    if (!hasJemaat) {

                        none.checked = true;
                    }
                };

                // initial
                updateState();

                // change listener
                jemaatSelect.addEventListener('change', updateState);
            };

            const defaultLevels = [
                'gembala',
                'pengurus',
                'pelayan',
                'staff'
            ];

            const initPositionLevel = ({
                selectId,
                customInputId,
                hiddenInputId,
                currentValue = ''
            }) => {

                const select = document.getElementById(selectId);
                const customInput = document.getElementById(customInputId);
                const hiddenInput = document.getElementById(hiddenInputId);

                const updateValue = () => {

                    if (select.value === 'lainnya') {

                        customInput.classList.remove('d-none');

                        hiddenInput.value = customInput.value;

                    } else {

                        customInput.classList.add('d-none');

                        hiddenInput.value = select.value;
                    }
                };

                // mode edit
                if (currentValue) {

                    if (defaultLevels.includes(currentValue)) {

                        select.value = currentValue;

                        hiddenInput.value = currentValue;

                    } else {

                        select.value = 'lainnya';

                        customInput.classList.remove('d-none');

                        customInput.value = currentValue;

                        hiddenInput.value = currentValue;
                    }
                }

                select.addEventListener('change', updateValue);

                customInput.addEventListener('input', () => {

                    hiddenInput.value = customInput.value;
                });

                updateValue();
            };

            const loadStructure = async () => {
                chartContainer.innerHTML =
                    '<div class="text-center py-5"><i class="ri-loader-4-line ri-spin fs-1"></i><p class="mt-3 text-muted">Memuat struktur...</p></div>';
                try {
                    const res = await fetch("{{ route('admin.church_structure.api.tree') }}", {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    structuresData = data.tree || [];
                    renderOrgChart(structuresData);
                    
                    // Fetch flat data for accordion (tidak difilter duplicate)
                    const flatRes = await fetch("{{ route('admin.church_structure.api.tree') }}?accordion=true", {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const flatData = await flatRes.json();
                    renderAccordionStructure(flatData.flat || [], flatData.roots || []);
                } catch (e) {
                    chartContainer.innerHTML =
                        '<div class="empty-state"><i class="ri-error-warning-line fs-1"></i><p>Gagal memuat struktur</p><button class="btn btn-sm btn-custom mt-3" onclick="loadStructure()">Coba Lagi</button></div>';
                }
            };

            const renderOrgChart = (items) => {
                chartContainer.innerHTML = '';

                if (!items || items.length === 0) {
                    chartContainer.innerHTML =
                        '<div class="empty-state"><i class="ri-tree fs-1"></i><p>Belum ada data struktur gereja</p></div>';
                    return;
                }

                const nodeWidth = 200;
                const nodeHeight = 100;
                const levelHeight = 180;
                const siblingGap = 50;

                const positions = calculatePositions(items, 0, 50, nodeWidth, nodeHeight, levelHeight,
                    siblingGap);

                positions.forEach(pos => {
                    const node = createNodeElement(pos.item, pos.x, pos.y);
                    chartContainer.appendChild(node);
                });

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        drawConnectors(items);
                    });
                });
            };

            const calculatePositions = (items, level, startX, nodeWidth, nodeHeight, levelHeight, siblingGap) => {
                let positions = [];
                let currentX = startX;

                items.forEach((item, index) => {
                    positions.push({
                        item: item,
                        x: currentX,
                        y: level * levelHeight + 40,
                        level: level
                    });

                    if (item.children && item.children.length > 0) {
                        const childPositions = calculatePositions(
                            item.children,
                            level + 1,
                            currentX,
                            nodeWidth,
                            nodeHeight,
                            levelHeight,
                            siblingGap
                        );

                        const lastChildX = childPositions[childPositions.length - 1].x;
                        const totalWidth = lastChildX + nodeWidth - currentX;
                        const parentCenter = currentX + totalWidth / 2;

                        positions[positions.length - 1].x = parentCenter - nodeWidth / 2;

                        childPositions.forEach(cp => {
                            positions.push(cp);
                        });

                        currentX = lastChildX + nodeWidth + siblingGap;
                    } else {
                        currentX += nodeWidth + siblingGap;
                    }
                });

                return positions;
            };
            

            const createNodeElement = (item, x, y) => {

                const node = document.createElement('div');

                node.className = 'org-node';

                node.dataset.id = getItemId(item);

                node.style.left = x + 'px';

                node.style.top = y + 'px';

                const initials = (item.jemaat?.nama_lengkap || '?')
                    .charAt(0)
                    .toUpperCase();

                const badgeClass = item.position_level || 'lainnya';

                const priorityStar =
                item.jemaat?.priority === 'primer'
                ? `
                    <div class="priority-star priority-primer">
                        <i class="ri-star-fill"></i>
                    </div>
                `
                : item.jemaat?.priority === 'sekunder'
                ? `
                    <div class="priority-star priority-sekunder">
                        <i class="ri-star-fill"></i>
                    </div>
                `
                : '';

                node.innerHTML = `

                    ${priorityStar}

                    <span class="org-node-badge ${badgeClass}">
                        ${item.position_level}
                    </span>

                    <div class="position-absolute top-0 start-0 m-1 d-flex gap-1">
                        <button 
                            type="button"
                            class="btn btn-sm btn-light shadow-sm btn-edit-node"
                            title="Edit"
                        >
                            <i class="ri-edit-line"></i>
                        </button>
                    </div>

                    <div class="org-node-header">
                        <div class="org-node-avatar">${initials}</div>

                        <div class="org-node-title">
                            ${item.position_name}
                        </div>

                        <div class="org-node-subtitle">
                            ${item.jemaat?.nama_lengkap || 'Belum diisi'}
                        </div>
                    </div>

                    <div 
                        class="drop-zone"
                        data-parent-id="${getItemId(item)}"
                    >
                        + Tambah Bawahan
                    </div>
                `;

                // =========================
                // DRAG
                // =========================
                node.addEventListener('mousedown', (e) => {

                    if (
                        e.target.closest('.drop-zone') ||
                        e.target.closest('.btn-edit-node')
                    ) {
                        e.preventDefault();
                        return;
                    }

                    startX = e.clientX;
                    startY = e.clientY;

                    isDragging = false;
                    suppressClick = false;

                    const rect = node.getBoundingClientRect();

                    draggedItem = {
                        node: node,
                        id: getItemId(item),
                        offsetX: e.clientX - rect.left,
                        offsetY: e.clientY - rect.top
                    };

                    node.classList.add('dragging');

                    document.addEventListener('mousemove', onMouseMove);

                    document.addEventListener('mouseup', onMouseUp);

                    e.preventDefault();
                });

                // =========================
                // BUTTON EDIT
                // =========================
                const btnEdit = node.querySelector('.btn-edit-node');

                btnEdit.addEventListener('click', (e) => {

                    e.stopPropagation();
                    e.preventDefault();

                    editStructure(item);
                });

                // =========================
                // TAMBAH BAWAHAN
                // =========================
                const dropZone = node.querySelector('.drop-zone');

                dropZone.addEventListener('click', async (e) => {

                    e.stopPropagation();
                    e.preventDefault();

                    window.currentEditItem = null;

                    // Reset form
                    document.getElementById('formEdit').reset();

                    document.getElementById('edit_id').value = '';
                    document.getElementById('edit_position_name').value = '';
                    document.getElementById('edit_description').value = '';
                    document.getElementById('edit_is_active').checked = true;

                    // Reset priority
                    document.querySelectorAll('input[name="priority"]').forEach(r => {
                        r.checked = false;
                    });
                    document.getElementById('edit_priority_none').checked = true;

                    // Load parent options first
                    await loadParentsOptions(
                        'edit_parent_id',
                        [getItemId(item)]
                    );

                    // Initialize form elements after loading parent options
                    setTimeout(() => {
                        // Initialize position level
                        initPositionLevel({
                            selectId: 'edit_position_level_select',
                            customInputId: 'edit_position_level_custom',
                            hiddenInputId: 'edit_position_level_hidden',
                            currentValue: ''
                        });

                        // Initialize jemaat select
                        initJemaatSelect('edit_jemaat_id', null);

                        // Initialize priority validation
                        handlePriorityValidation('edit_jemaat_id', 'edit_');

                        // Show modal
                        const modal = new bootstrap.Modal(
                            document.getElementById('modalEdit')
                        );
                        modal.show();
                    }, 200);
                });

                dropZone.addEventListener('mousedown', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                });

                return node;
            };

            const drawConnectors = (items) => {
                chartContainer.querySelectorAll('.org-connector').forEach(c => c.remove());

                // Create SVG for better arrow rendering
                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.style.position = 'absolute';
                svg.style.top = '0';
                svg.style.left = '0';
                svg.style.width = '100%';
                svg.style.height = '100%';
                svg.style.pointerEvents = 'none';
                svg.style.zIndex = '1';
                
                // Add marker definitions untuk arrows
                const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                
                // Arrow marker untuk primary connection
                const markerPrimary = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
                markerPrimary.setAttribute('id', 'arrowPrimary');
                markerPrimary.setAttribute('markerWidth', '10');
                markerPrimary.setAttribute('markerHeight', '10');
                markerPrimary.setAttribute('refX', '9');
                markerPrimary.setAttribute('refY', '3');
                markerPrimary.setAttribute('orient', 'auto');
                markerPrimary.setAttribute('markerUnits', 'strokeWidth');
                const pathPrimary = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                pathPrimary.setAttribute('d', 'M0,0 L0,6 L9,3 z');
                pathPrimary.setAttribute('fill', '#3b82f6');
                markerPrimary.appendChild(pathPrimary);
                
                // Arrow marker untuk secondary connection
                const markerSecondary = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
                markerSecondary.setAttribute('id', 'arrowSecondary');
                markerSecondary.setAttribute('markerWidth', '10');
                markerSecondary.setAttribute('markerHeight', '10');
                markerSecondary.setAttribute('refX', '9');
                markerSecondary.setAttribute('refY', '3');
                markerSecondary.setAttribute('orient', 'auto');
                markerSecondary.setAttribute('markerUnits', 'strokeWidth');
                const pathSecondary = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                pathSecondary.setAttribute('d', 'M0,0 L0,6 L9,3 z');
                pathSecondary.setAttribute('fill', '#f59e0b');
                markerSecondary.appendChild(pathSecondary);
                
                defs.appendChild(markerPrimary);
                defs.appendChild(markerSecondary);
                svg.appendChild(defs);

                // Helper function to draw a connector between two nodes
                const drawConnector = (parentCenterX, parentBottomY, childCenterX, childTopY, isSecondary = false) => {
                    const midY = parentBottomY + ((childTopY - parentBottomY) / 2);
                    
                    const color = isSecondary ? '#f59e0b' : '#3b82f6';
                    const strokeWidth = isSecondary ? '1.5' : '2';
                    const markerName = isSecondary ? 'arrowSecondary' : 'arrowPrimary';
                    const dashArray = isSecondary ? '5,5' : 'none';

                    // Vertical line from parent
                    const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    path1.setAttribute('x1', parentCenterX);
                    path1.setAttribute('y1', parentBottomY);
                    path1.setAttribute('x2', parentCenterX);
                    path1.setAttribute('y2', midY);
                    path1.setAttribute('stroke', color);
                    path1.setAttribute('stroke-width', strokeWidth);
                    path1.setAttribute('stroke-dasharray', dashArray);
                    svg.appendChild(path1);

                    // Horizontal line
                    const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    path2.setAttribute('x1', Math.min(parentCenterX, childCenterX));
                    path2.setAttribute('y1', midY);
                    path2.setAttribute('x2', Math.max(parentCenterX, childCenterX));
                    path2.setAttribute('y2', midY);
                    path2.setAttribute('stroke', color);
                    path2.setAttribute('stroke-width', strokeWidth);
                    path2.setAttribute('stroke-dasharray', dashArray);
                    svg.appendChild(path2);

                    // Vertical line to child
                    const path3 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    path3.setAttribute('x1', childCenterX);
                    path3.setAttribute('y1', midY);
                    path3.setAttribute('x2', childCenterX);
                    path3.setAttribute('y2', childTopY);
                    path3.setAttribute('stroke', color);
                    path3.setAttribute('stroke-width', strokeWidth);
                    path3.setAttribute('stroke-dasharray', dashArray);
                    path3.setAttribute('marker-end', `url(#${markerName})`);
                    svg.appendChild(path3);
                };

                // Hierarchical tree drawing
                const draw = (nodes) => {
                    nodes.forEach(parent => {

                        if (!parent.children || parent.children.length === 0) {
                            return;
                        }

                        const pNode = document.querySelector(
                            `.org-node[data-id="${getItemId(parent)}"]`
                        );

                        if (!pNode) return;

                        const parentCenterX = pNode.offsetLeft + (pNode.offsetWidth / 2);
                        const parentBottomY = pNode.offsetTop + pNode.offsetHeight;

                        parent.children.forEach(child => {

                            const cNode = document.querySelector(
                                `.org-node[data-id="${getItemId(child)}"]`
                            );

                            if (!cNode) return;

                            const childCenterX = cNode.offsetLeft + (cNode.offsetWidth / 2);
                            const childTopY = cNode.offsetTop;

                            drawConnector(parentCenterX, parentBottomY, childCenterX, childTopY);
                        });

                        draw(parent.children);
                    });
                };

                // Draw the main tree structure
                draw(items);

                // Draw additional connectors for multiple parents
                const drawMultiParentConnectors = (nodes) => {
                    // Collect all nodes in a flat map
                    const allNodes = {};
                    const collectNodes = (nodeList) => {
                        nodeList.forEach(node => {
                            allNodes[getItemId(node)] = node;
                            if (node.children && node.children.length > 0) {
                                collectNodes(node.children);
                            }
                        });
                    };
                    collectNodes(nodes);

                    // For each node, check if it has multiple parents
                    Object.values(allNodes).forEach(node => {
                        const nodeId = getItemId(node);
                        const parentIds = node.parent_id;

                        // Only process if there are multiple parents
                        if (!Array.isArray(parentIds) || parentIds.length <= 1) {
                            return;
                        }

                        const childNode = document.querySelector(`.org-node[data-id="${nodeId}"]`);
                        if (!childNode) return;

                        const childCenterX = childNode.offsetLeft + (childNode.offsetWidth / 2);
                        const childTopY = childNode.offsetTop;

                        // Draw connector from each parent (starting from the second one)
                        parentIds.slice(1).forEach(parentId => {
                            const parentNode = document.querySelector(`.org-node[data-id="${parentId}"]`);
                            if (!parentNode) return;

                            const parentCenterX = parentNode.offsetLeft + (parentNode.offsetWidth / 2);
                            const parentBottomY = parentNode.offsetTop + parentNode.offsetHeight;

                            // Use secondary connector style for additional parent connections
                            drawConnector(parentCenterX, parentBottomY, childCenterX, childTopY, true);
                        });
                    });
                };

                // Append SVG to container
                chartContainer.insertBefore(svg, chartContainer.firstChild);

                drawMultiParentConnectors(items);
            };

            const onMouseMove = (e) => {
                if (!draggedItem) return;

                if (!isDragging) {
                    if (Math.abs(e.clientX - startX) > 5 || Math.abs(e.clientY - startY) > 5) {
                        isDragging = true;
                    }
                }

                const containerRect = chartContainer.getBoundingClientRect();

                const newX = e.clientX - containerRect.left - draggedItem.offsetX + chartContainer.scrollLeft;
                const newY = e.clientY - containerRect.top - draggedItem.offsetY + chartContainer.scrollTop;

                draggedItem.node.style.left = newX + 'px';
                draggedItem.node.style.top = newY + 'px';

                // Hapus semua highlighting
                document.querySelectorAll('.org-node').forEach(n => n.classList.remove('drop-hover'));
                document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('active'));

                const elements = document.elementsFromPoint(e.clientX, e.clientY);

                // Cek drop-zone terlebih dahulu (prioritas utama)
                const dropZone = elements.find(el => el.classList && el.classList.contains('drop-zone'));
                if (dropZone && dropZone.dataset.parentId !== draggedItem.id) {
                    dropZone.classList.add('active');
                    // Highlight parent node juga
                    const parentNode = document.querySelector(`.org-node[data-id="${dropZone.dataset.parentId}"]`);
                    if (parentNode) {
                        parentNode.classList.add('drop-hover');
                    }
                } else {
                    // Fallback: highlight org-node
                    const target = elements
                        .map(el => el.closest('.org-node'))
                        .find(el => el && el.dataset.id !== draggedItem.id);

                    if (target) {
                        target.classList.add('drop-hover');
                    }
                }
            };

            const onMouseUp = (e) => {
                if (!draggedItem) return;

                draggedItem.node.classList.remove('dragging');

                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                const elements = document.elementsFromPoint(e.clientX, e.clientY);

                // Cek apakah drop zone sebagai prioritas utama
                const dropZone = elements.find(el => el.classList && el.classList.contains('drop-zone'));
                
                let dropTarget = null;
                let parentId = null;

                if (dropZone) {
                    // Drop pada drop-zone
                    parentId = dropZone.dataset.parentId;
                    dropTarget = document.querySelector(`.org-node[data-id="${parentId}"]`);
                } else {
                    // Fallback: cari org-node terdekat
                    dropTarget = elements
                        .map(el => el.closest('.org-node'))
                        .find(el => el && el.dataset.id !== draggedItem.id);
                    
                    if (dropTarget) {
                        parentId = dropTarget.dataset.id;
                    }
                }

                if (isDragging && dropTarget && parentId) {

                    if (isDescendant(draggedItem.id, parentId, structuresData)) {
                        showToast('error', '❌ Tidak bisa dipindahkan ke child/descendant-nya sendiri');

                        loadStructure();
                        draggedItem = null;
                        return;
                    }

                    reorderItem(draggedItem.id, parentId);
                } else {
                    loadStructure();
                }

                draggedItem = null;

                setTimeout(() => {
                    suppressClick = false;
                }, 100);
            };

            // Helper function: dapatkan semua descendant IDs dari sebuah node
            const getDescendantIds = (nodeId, nodes) => {
                const descendants = new Set();
                
                const collectDescendants = (id, items) => {
                    for (const item of items) {
                        if (String(item.id) === String(id)) {
                            if (item.children && item.children.length > 0) {
                                item.children.forEach(child => {
                                    descendants.add(String(child.id));
                                    collectDescendants(String(child.id), items);
                                });
                            }
                            return;
                        }
                        
                        if (item.children && item.children.length > 0) {
                            collectDescendants(id, item.children);
                        }
                    }
                };
                
                // Flatten dan cari di seluruh struktur
                const flattenAll = (items) => {
                    let all = [...items];
                    items.forEach(item => {
                        if (item.children && item.children.length > 0) {
                            all = all.concat(flattenAll(item.children));
                        }
                    });
                    return all;
                };
                
                collectDescendants(nodeId, flattenAll(nodes));
                return Array.from(descendants);
            };

            // Helper function: cek apakah itemA adalah ancestor/parent dari itemB (handle multiple parents)
            const isAncestorOf = (potentialAncestorId, targetId, allData) => {
                potentialAncestorId = String(potentialAncestorId);
                targetId = String(targetId);
                
                if (potentialAncestorId === targetId) return false;
                
                // Buat map dari semua items (handle multiple parents scenario)
                const itemMap = new Map();
                const allItems = [];
                
                const flattenItems = (items) => {
                    items.forEach(item => {
                        itemMap.set(String(item.id), item);
                        allItems.push(item);
                        if (item.children && item.children.length > 0) {
                            flattenItems(item.children);
                        }
                    });
                };
                
                flattenItems(allData);
                
                // Cari target dan check apakah potentialAncestor ada di parent chain-nya
                const targetItem = itemMap.get(targetId);
                if (!targetItem) return false;
                
                // Check direct parents (handle multiple parents)
                if (targetItem.parent_id) {
                    const parentIds = Array.isArray(targetItem.parent_id) 
                        ? targetItem.parent_id 
                        : [targetItem.parent_id];
                    
                    // Jika potentialAncestor adalah salah satu parent
                    if (parentIds.includes(potentialAncestorId)) {
                        return true;
                    }
                    
                    // Recursive check: apakah potentialAncestor adalah ancestor dari parents
                    for (const parentId of parentIds) {
                        if (isAncestorOf(potentialAncestorId, parentId, allData)) {
                            return true;
                        }
                    }
                }
                
                return false;
            };

            const isDescendant = (draggedId, targetId, nodes) => {
                // Gunakan isAncestorOf untuk handle multiple parents
                // draggedId tidak boleh menjadi ancestor dari targetId
                return isAncestorOf(draggedId, targetId, nodes);
            };

            const reorderItem = async (itemId, newParentId) => {
                try {
                    const res = await fetch("{{ route('admin.church_structure.reorder') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            items: [{
                                id: itemId,
                                parent_id: [newParentId],
                                sort_order: 0
                            }]
                        })
                    });
                    const result = await res.json();
                    if (result.success) {
                        showToast('success', 'Struktur berhasil diperbarui');
                        loadStructure();
                    } else {
                        showToast('error', result.message || 'Gagal memperbarui');
                        loadStructure();
                    }
                } catch (err) {
                    showToast('error', 'Kesalahan koneksi');
                    loadStructure();
                }
            };

            window.editStructure = (item) => {

                window.currentEditItem = item;
                const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
                document.getElementById('edit_id').value = getItemId(item);
                document.getElementById('edit_position_name').value = item.position_name;
                document.getElementById('edit_description').value = item.description || '';
                document.getElementById('edit_is_active').checked = !!item.is_active;

                // ambil parent id / uuid dengan aman - bisa array atau single value
                const selectedParents = item.parent_id || null;
                loadParentsOptions('edit_parent_id', selectedParents)
                    .then(() => {

                        setTimeout(() => {

                            initJemaatSelect(
                                'edit_jemaat_id',
                                item.jemaat?.id
                            );

                            handlePriorityValidation(
                                'edit_jemaat_id',
                                'edit_'
                            );

                            initPositionLevel({
                                selectId: 'edit_position_level_select',
                                customInputId: 'edit_position_level_custom',
                                hiddenInputId: 'edit_position_level_hidden',
                                currentValue: item.position_level
                            });

                        }, 100);
                    });

                document.querySelectorAll('input[name="priority"]').forEach(r => {
                    r.checked = false;
                });

                if (item.jemaat?.priority === 'primer') {

                    document.getElementById('edit_priority_primary').checked = true;

                } else if (item.jemaat?.priority === 'sekunder') {

                    document.getElementById('edit_priority_secondary').checked = true;

                } else {

                    document.getElementById('edit_priority_none').checked = true;
                }

                modal.show();
            };

            const showToast = (type, message) => {
                const alert = document.createElement('div');
                alert.className =
                    `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed bottom-0 end-0 m-3`;
                alert.style.zIndex = '9999';
                alert.innerHTML =
                    `<i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-fill me-2"></i>${message}<button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>`;
                document.body.appendChild(alert);
                setTimeout(() => alert.remove(), 5000);
            };

            const loadParentsOptions = async (selectId, selectedId = null) => {

                try {

                    const res = await fetch(
                        "{{ route('admin.church_structure.api.tree') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }
                    );

                    const data = await res.json();

                    // =========================
                    // CHECK DESCENDANT
                    // =========================
                    const isChildOfCurrent = (node, currentId) => {

                        if (!window.currentEditItem) return false;

                        const currentNodeId = String(getItemId(window.currentEditItem));

                        // kalau node ini adalah current edit item
                        if (String(getItemId(node)) === currentNodeId) {
                            return true;
                        }

                        // recursive child
                        if (node.children && node.children.length > 0) {

                            for (const child of node.children) {

                                if (isChildOfCurrent(child, currentId)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    };

                    // ambil semua descendant dari current item (handle multiple parents)
                    const getInvalidIds = (nodes, currentId) => {

                        let invalidIds = new Set();
                        
                        // Buat map dari semua items
                        const itemMap = new Map();
                        const flattenItems = (items) => {
                            items.forEach(item => {
                                itemMap.set(String(getItemId(item)), item);
                                if (item.children && item.children.length > 0) {
                                    flattenItems(item.children);
                                }
                            });
                        };
                        flattenItems(nodes);

                        // Item itu sendiri tidak boleh dipilih
                        invalidIds.add(String(currentId));

                        // Kumpulkan semua descendants
                        const collectAllDescendants = (id) => {
                            const item = itemMap.get(String(id));
                            if (!item) return;
                            
                            if (item.children && item.children.length > 0) {
                                item.children.forEach(child => {
                                    const childId = String(getItemId(child));
                                    invalidIds.add(childId);
                                    collectAllDescendants(childId);
                                });
                            }
                        };

                        collectAllDescendants(currentId);

                        return Array.from(invalidIds);
                    };

                    let invalidIds = [];

                    if (window.currentEditItem) {

                        invalidIds = getInvalidIds(
                            data.tree || [],
                            getItemId(window.currentEditItem)
                        );
                    }

                    // =========================
                    // BUILD OPTION
                    // =========================
                    const build = (nodes, prefix = '') => {

                        let html = '';

                        nodes.forEach(n => {

                            const optionValue = String(getItemId(n));
                            
                            // Handle both single selectedId and array of selectedIds
                            let isSelected = false;
                            if (Array.isArray(selectedId)) {
                                isSelected = selectedId.some(id => String(id) === optionValue);
                            } else if (selectedId) {
                                isSelected = optionValue === String(selectedId);
                            }

                            // disable dirinya sendiri + seluruh child
                            const disabled = invalidIds.includes(optionValue);

                            html += `
                                <option 
                                    value="${optionValue}"
                                    ${isSelected ? 'selected' : ''}
                                    ${disabled ? 'disabled' : ''}
                                >
                                    ${isSelected ? '✓ ' : ''}
                                    ${prefix}${n.position_name}
                                    ${disabled ? ' (tidak valid)' : ''}
                                </option>
                            `;

                            if (n.children && n.children.length > 0) {

                                html += build(
                                    n.children,
                                    prefix + '─ '
                                );
                            }
                        });

                        return html;
                    };

                    const select = document.getElementById(selectId);
                    const info = document.getElementById('currentParentsInfo');

                    if (select) {

                        select.innerHTML =
                            '<option value="">-- Tidak ada parent (Bisa dipilih multiple atau kosongkan) --</option>' +
                            build(data.tree || []);

                        const selectedParents = selectedId
                            ? (Array.isArray(selectedId) ? selectedId : [selectedId])
                            : [];

                        if (selectedParents.length > 0) {

                            const parentNames = [];

                            selectedParents.forEach(id => {

                                const option = select.querySelector(`option[value="${id}"]`);

                                if (option) {

                                    option.selected = true;

                                    parentNames.push(option.textContent.trim());
                                }
                            });

                            if (info) {
                                info.innerHTML =
                                    `<strong>Parent saat ini:</strong> ${parentNames.join(', ')}`;
                            }

                        } else {

                            if (info) {
                                info.innerHTML =
                                    '<span class="text-muted">Parent saat ini: Tidak ada</span>';
                            }
                        }
                    }

                    return true;

                } catch (e) {

                    console.error(e);

                    return false;
                }
            };

            document.getElementById('formAdd').addEventListener('submit', async function(e) {
                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Menyimpan...';

                try {
                    const res = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: new FormData(this)
                    });
                    const result = await res.json();
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalAdd')).hide();
                        this.reset();
                        showToast('success', result.message);
                        loadStructure();
                    } else {
                        showToast('error', Object.values(result.errors || {}).flat().join(', ') ||
                            'Gagal menyimpan');
                    }
                } catch (err) {
                    showToast('error', 'Kesalahan koneksi');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-save-line me-1"></i>Simpan';
                }
            });

            document.getElementById('formEdit').addEventListener('submit', async function(e) {

                e.preventDefault();

                const id = document.getElementById('edit_id').value;
                
                // Validasi: cek apakah parent yang dipilih adalah ancestor/descendant dari item ini
                const parentSelects = document.getElementById('edit_parent_id');
                if (parentSelects && id && id !== 'new-child') {
                    const selectedParents = Array.from(parentSelects.selectedOptions).map(opt => opt.value).filter(v => v);
                    
                    // Check apakah item ini adalah ancestor dari salah satu selected parent
                    const hasInvalidParent = selectedParents.some(parentId => {
                        return isAncestorOf(String(id), String(parentId), structuresData);
                    });
                    
                    if (hasInvalidParent) {
                        showToast('error', '❌ Tidak boleh memindahkan ke child/descendant-nya sendiri');
                        return;
                    }
                }

                const btn = this.querySelector('button[type="submit"]');

                btn.disabled = true;

                btn.innerHTML =
                    '<i class="ri-loader-4-line ri-spin"></i> Menyimpan...';

                try {

                    let url = '';
                    const formData = new FormData(this);

                    if (id && id !== 'new-child') {

                        url = `${churchStructureBaseUrl}/${id}`;

                        formData.append('_method', 'PUT');

                    } else {

                        url = "{{ route('admin.church_structure.store') }}";
                    }

                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const result = await res.json();

                    if (result.success) {

                        bootstrap.Modal
                            .getInstance(document.getElementById('modalEdit'))
                            .hide();

                        showToast('success', result.message);

                        loadStructure();

                    } else {

                        showToast(
                            'error',
                            Object.values(result.errors || {})
                            .flat()
                            .join(', ') || 'Gagal'
                        );
                    }

                } catch (err) {

                    console.error(err);

                    showToast('error', 'Kesalahan koneksi');

                } finally {

                    btn.disabled = false;

                    btn.innerHTML =
                        '<i class="ri-save-line me-1"></i>Update';
                }
            });

            window.deleteStructure = async (id) => {
                if (!confirm('Hapus posisi ini beserta anak-anaknya?')) return;
                try {
                    const res = await fetch(`/admin/church-structure/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const result = await res.json();
                    if (result.success) {
                        showToast('success', result.message);
                        loadStructure();
                    } else {
                        showToast('error', result.message || 'Gagal menghapus');
                    }
                } catch (err) {
                    showToast('error', 'Kesalahan koneksi');
                }
            };

            document.getElementById('modalAdd').addEventListener('shown.bs.modal', async () => {

                await loadParentsOptions('parent_id');

                setTimeout(() => {

                    initJemaatSelect('jemaat_id_add');

                    handlePriorityValidation('jemaat_id_add');

                    initPositionLevel({
                        selectId: 'position_level_add_select',
                        customInputId: 'position_level_add_custom',
                        hiddenInputId: 'position_level_add_hidden'
                    });

                }, 100);
            });

            document.getElementById('modalEdit').addEventListener('shown.bs.modal', async () => {

                // await loadParentsOptions('edit_parent_id');

                setTimeout(() => {

                    initJemaatSelect(
                        'edit_jemaat_id',
                        window.currentEditItem?.jemaat?.id
                    );

                    handlePriorityValidation(
                        'edit_jemaat_id',
                        'edit_'
                    );

                    initPositionLevel({
                        selectId: 'edit_position_level_select',
                        customInputId: 'edit_position_level_custom',
                        hiddenInputId: 'edit_position_level_hidden',
                        currentValue: window.currentEditItem?.position_level
                    });

                    // Tambahkan handler untuk clear parent selection
                    const editParentSelect = document.getElementById('edit_parent_id');
                    if (editParentSelect) {
                        // Add clear button next to label jika belum ada
                        const label = editParentSelect.previousElementSibling;
                        let clearBtn = document.getElementById('btnClearParentSelect');
                        
                        if (!clearBtn && label) {
                            clearBtn = document.createElement('button');
                            clearBtn.id = 'btnClearParentSelect';
                            clearBtn.type = 'button';
                            clearBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
                            clearBtn.innerHTML = '<i class="ri-close-line me-1"></i>Hapus Semua';
                            clearBtn.onclick = (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                Array.from(editParentSelect.options).forEach(opt => {
                                    opt.selected = false;
                                });
                                editParentSelect.focus();
                                showToast('success', 'Seleksi parent dihapus');
                            };
                            
                            if (label) {
                                label.parentElement.insertBefore(clearBtn, label.nextSibling);
                            }
                        }
                    }

                }, 100);
            });

            const levelBadgeClass = (level) => {

                const map = {
                    gembala: 'bg-purple-subtle text-dark',
                    pengurus: 'bg-primary-subtle text-primary',
                    pelayan: 'bg-success-subtle text-success',
                    staff: 'bg-secondary-subtle text-secondary',
                    lainnya: 'bg-warning-subtle text-warning'
                };

                return map[level] || 'bg-light text-dark';
            };

            const renderAccordionStructure = (allStructures, roots) => {

                const container = document.getElementById(
                    'accordionStructureContainer'
                );

                if (!allStructures || !allStructures.length) {

                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            Belum ada struktur organisasi
                        </div>
                    `;

                    return;
                }

                // Create a map of structures by ID for easy lookup
                const structureMap = {};
                allStructures.forEach(s => {
                    structureMap[s.id] = s;
                });

                const buildAccordion = (nodeIds, depth = 0) => {

                    let html = `<div class="accordion structure-accordion">`;

                    nodeIds.forEach(nodeId => {
                        const node = structureMap[nodeId];
                        if (!node) return;

                        const nodeAccId = `acc_${node.id}`;

                        const initials = (
                                node.jemaat?.nama_lengkap || '?'
                            )
                            .charAt(0)
                            .toUpperCase();

                        // Find all children of this node (children that have this node in their parent_id)
                        const childrenIds = [];
                        allStructures.forEach(s => {
                            if (s.parent_id) {
                                const parentIds = Array.isArray(s.parent_id) ? s.parent_id : [s.parent_id];
                                if (parentIds.includes(node.id)) {
                                    childrenIds.push(s.id);
                                }
                            }
                        });

                        const hasChildren = childrenIds.length > 0;

                        const priorityBadge =
                            node.jemaat?.priority === 'primer'
                            ? `
                                <span class="badge bg-warning text-dark">
                                    <i class="ri-star-fill"></i>
                                </span>
                            `
                            : node.jemaat?.priority === 'sekunder'
                            ? `
                                <span class="badge bg-secondary">
                                    <i class="ri-star-fill"></i>
                                </span>
                            `
                            : '';

                        html += `
                            <div class="accordion-item">

                                <h2 class="accordion-header">

                                    <button
                                        class="accordion-button collapsed"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#${nodeAccId}"
                                    >

                                        <div class="structure-card">

                                            <div class="structure-left">

                                                <div class="structure-avatar">
                                                    ${initials}
                                                </div>

                                                <div>

                                                    <div class="d-flex align-items-center gap-2 flex-wrap">

                                                        <div class="d-flex align-items-center gap-2 flex-wrap">

                                                            <div class="structure-title">
                                                                ${node.position_name}
                                                            </div>

                                                            ${priorityBadge}

                                                        </div>

                                                        <span class="badge structure-badge ${levelBadgeClass(node.position_level)}">
                                                            ${node.position_level}
                                                        </span>

                                                        ${
                                                            node.is_active
                                                            ? `<span class="badge bg-success-subtle text-success">
                                                                        Aktif
                                                                </span>`
                                                            : `<span class="badge bg-danger-subtle text-danger">
                                                                        Nonaktif
                                                                </span>`
                                                        }

                                                    </div>

                                                    <div class="structure-subtitle">
                                                        ${node.jemaat?.nama_lengkap || 'Belum ada penanggung jawab'}
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </button>

                                </h2>

                                <div
                                    id="${nodeAccId}"
                                    class="accordion-collapse collapse"
                                >

                                    <div class="accordion-body">

                                        <div class="d-flex justify-content-end mb-3 structure-actions">

                                            <button
                                                class="btn btn-sm btn-light"
                                                onclick='editStructure(${JSON.stringify(node)})'
                                            >
                                                <i class="ri-edit-line"></i>
                                            </button>

                                            <button
                                                class="btn btn-sm btn-light text-danger"
                                                onclick="deleteStructure('${node.id}')"
                                            >
                                                <i class="ri-delete-bin-line"></i>
                                            </button>

                                        </div>

                                        ${
                                            node.description
                                            ? `
                                                    <div class="small text-muted mb-3">
                                                        ${node.description}
                                                    </div>
                                                `
                                            : ''
                                        }

                                        ${
                                            hasChildren
                                            ? `
                                                    <div class="structure-child">
                                                        ${buildAccordion(childrenIds, depth + 1)}
                                                    </div>
                                                `
                                            : `
                                                    <div class="empty-children">
                                                        Tidak ada bawahan
                                                    </div>
                                                `
                                        }

                                    </div>

                                </div>

                            </div>
                        `;
                    });

                    html += `</div>`;

                    return html;
                };

                // Build accordion starting from root nodes
                container.innerHTML = buildAccordion(roots);
            };


            document.getElementById('btnRefresh').addEventListener('click', loadStructure);

            const btnFullscreen = document.getElementById('btnFullscreen');
            const visualizationCard = document.getElementById('visualizationCard');

            let isExpanded = false;

            btnFullscreen.addEventListener('click', () => {

                isExpanded = !isExpanded;

                visualizationCard.classList.toggle(
                    'browser-fullscreen',
                    isExpanded
                );

                if (isExpanded) {

                    btnFullscreen.innerHTML = `
                        <i class="ri-fullscreen-exit-line"></i>
                        Kecilkan
                    `;

                    document.body.style.overflow = 'hidden';

                } else {

                    btnFullscreen.innerHTML = `
                        <i class="ri-fullscreen-line"></i>
                        Perbesar
                    `;

                    document.body.style.overflow = '';
                }

                setTimeout(() => {
                    drawConnectors(structuresData);
                }, 200);
            });

            document.getElementById('btnReloadAccordion').addEventListener('click', loadStructure);

            loadJemaats().then(() => loadStructure());
        });
    </script>
@endpush
