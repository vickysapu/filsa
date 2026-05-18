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
                                <label class="form-label">Induk Posisi</label>
                                <select name="parent_id" class="form-select" id="parent_id">
                                    <option value="">Root / Tidak ada induk</option>
                                </select>
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
                                <label class="form-label">Induk Posisi</label>
                                <select name="parent_id" id="edit_parent_id" class="form-select">
                                    <option value="">Root / Tidak ada induk</option>
                                </select>
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
        }

        .drop-zone.active {
            border-color: #3b82f6;
            background: #eff6ff;
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
                    renderAccordionStructure(structuresData);
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
                    ) return;

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

                    editStructure(item);
                });

                // =========================
                // TAMBAH BAWAHAN
                // =========================
                const dropZone = node.querySelector('.drop-zone');

                dropZone.addEventListener('click', async (e) => {

                    e.stopPropagation();

                    const modal = new bootstrap.Modal(
                        document.getElementById('modalEdit')
                    );

                    window.currentEditItem = null;

                    // mode create child
                    document.getElementById('formEdit').reset();

                    document.getElementById('edit_id').value = '';

                    document.getElementById('edit_position_name').value = '';

                    document.getElementById('edit_position_level').value = '';

                    document.getElementById('edit_description').value = '';

                    document.getElementById('edit_is_active').checked = true;

                    // parent otomatis = item yang dipilih
                    await loadParentsOptions(
                        'edit_parent_id',
                        getItemId(item)
                    );

                    setTimeout(() => {
                        initJemaatSelect('edit_jemaat_id', item.jemaat?.id);
                    }, 150);

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
                });

                return node;
            };

            const drawConnectors = (items) => {
                chartContainer.querySelectorAll('.org-connector').forEach(c => c.remove());

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

                            const connector = document.createElement('div');
                            connector.className = 'org-connector';

                            const midY = parentBottomY + ((childTopY - parentBottomY) / 2);

                            connector.innerHTML = `
                                <div style="
                                    position:absolute;
                                    width:2px;
                                    background:#3b82f6;
                                    left:${parentCenterX}px;
                                    top:${parentBottomY}px;
                                    height:${midY - parentBottomY}px;
                                "></div>

                                <div style="
                                    position:absolute;
                                    height:2px;
                                    background:#3b82f6;
                                    left:${Math.min(parentCenterX, childCenterX)}px;
                                    top:${midY}px;
                                    width:${Math.abs(childCenterX - parentCenterX)}px;
                                "></div>

                                <div style="
                                    position:absolute;
                                    width:2px;
                                    background:#3b82f6;
                                    left:${childCenterX}px;
                                    top:${midY}px;
                                    height:${childTopY - midY}px;
                                "></div>
                            `;

                            chartContainer.appendChild(connector);
                        });

                        draw(parent.children);
                    });
                };

                draw(items);
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

                document.querySelectorAll('.org-node').forEach(n => n.classList.remove('drop-hover'));

                const elements = document.elementsFromPoint(e.clientX, e.clientY);

                const target = elements
                    .map(el => el.closest('.org-node'))
                    .find(el => el && el.dataset.id !== draggedItem.id);

                if (target) {
                    target.classList.add('drop-hover');
                }
            };

            const onMouseUp = (e) => {
                if (!draggedItem) return;

                draggedItem.node.classList.remove('dragging');

                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                const elements = document.elementsFromPoint(e.clientX, e.clientY);

                const dropTarget = elements
                    .map(el => el.closest('.org-node'))
                    .find(el => el && el.dataset.id !== draggedItem.id);

                if (isDragging && dropTarget) {

                    if (isDescendant(draggedItem.id, dropTarget.dataset.id, structuresData)) {
                        showToast('error', 'Tidak bisa memindahkan ke dalam bawahan sendiri');

                        loadStructure();
                        draggedItem = null;
                        return;
                    }

                    reorderItem(draggedItem.id, dropTarget.dataset.id);
                } else {
                    loadStructure();
                }

                draggedItem = null;

                setTimeout(() => {
                    suppressClick = false;
                }, 100);
            };

            const isDescendant = (draggedId, targetId, nodes) => {

                draggedId = String(draggedId);
                targetId = String(targetId);

                // cari node yang sedang dipindah
                const findNode = (items, id) => {

                    for (const item of items) {

                        if (String(item.id) === id) {
                            return item;
                        }

                        if (item.children && item.children.length) {

                            const found = findNode(item.children, id);

                            if (found) return found;
                        }
                    }

                    return null;
                };

                // cek apakah target ada di bawah node tersebut
                const containsChild = (node, id) => {

                    if (!node.children) return false;

                    for (const child of node.children) {

                        if (String(child.id) === id) {
                            return true;
                        }

                        if (containsChild(child, id)) {
                            return true;
                        }
                    }

                    return false;
                };

                const draggedNode = findNode(nodes, draggedId);

                if (!draggedNode) return false;

                return containsChild(draggedNode, targetId);
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
                                parent_id: newParentId,
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

                // ambil parent id / uuid dengan aman
                const selectedParent =
                    item.parent_id ||
                    item.parent?.id ||
                    null;
                loadParentsOptions('edit_parent_id', selectedParent)
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

                    // ambil semua descendant dari current item
                    const getInvalidIds = (nodes, currentId) => {

                        let invalidIds = [];

                        const findCurrent = (node) => {

                            if (String(getItemId(node)) === String(currentId)) {

                                collectChildren(node);

                                return true;
                            }

                            if (node.children) {

                                for (const child of node.children) {

                                    if (findCurrent(child)) {
                                        return true;
                                    }
                                }
                            }

                            return false;
                        };

                        const collectChildren = (node) => {

                            invalidIds.push(String(getItemId(node)));

                            if (node.children) {

                                node.children.forEach(child => {
                                    collectChildren(child);
                                });
                            }
                        };

                        nodes.forEach(node => findCurrent(node));

                        return invalidIds;
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
                            const selectedValue = selectedId ? String(selectedId) : '';

                            // disable dirinya sendiri + seluruh child
                            const disabled = invalidIds.includes(optionValue);

                            html += `
                                <option 
                                    value="${optionValue}"
                                    ${optionValue === selectedValue ? 'selected' : ''}
                                    ${disabled ? 'disabled' : ''}
                                >
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

                    if (select) {

                        select.innerHTML =
                            '<option value="">Root / Tidak ada induk</option>' +
                            build(data.tree || []);
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

            const renderAccordionStructure = (items) => {

                const container = document.getElementById(
                    'accordionStructureContainer'
                );

                if (!items || !items.length) {

                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            Belum ada struktur organisasi
                        </div>
                    `;

                    return;
                }

                const buildAccordion = (nodes, depth = 0) => {

                    let html = `
                        <div class="accordion structure-accordion">
                    `;

                    nodes.forEach(node => {

                        const nodeId = `acc_${node.id}`;

                        const initials = (
                                node.jemaat?.nama_lengkap || '?'
                            )
                            .charAt(0)
                            .toUpperCase();

                        const hasChildren =
                            node.children &&
                            node.children.length > 0;

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
                                        data-bs-target="#${nodeId}"
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
                                    id="${nodeId}"
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
                                                        ${buildAccordion(node.children, depth + 1)}
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

                container.innerHTML = buildAccordion(items);
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
