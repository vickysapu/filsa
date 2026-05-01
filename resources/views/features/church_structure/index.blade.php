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
            <div class="card card-custom">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="ri-tree me-1"></i>Visualisasi Struktur</span>
                    <button class="btn btn-sm btn-outline-secondary" id="btnRefresh">
                        <i class="ri-refresh-line"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div id="structureChart" class="structure-chart"></div>
                </div>
            </div>

            <div class="card card-custom mt-4">
                <div class="card-header"><i class="ri-list-check me-1"></i>Daftar Posisi</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Posisi</th>
                                    <th>Level</th>
                                    <th>Jemaat</th>
                                    <th width="100">Status</th>
                                    <th width="120" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($structures as $index => $structure)
                                    <tr>
                                        <td>{{ $structures->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-500">{{ $structure->position_name }}</div>
                                            @if ($structure->description)
                                                <small
                                                    class="text-muted-custom d-block">{{ Str::limit($structure->description, 40) }}</small>
                                            @endif
                                        </td>
                                        <td><span
                                                class="badge bg-{{ ['gembala' => 'purple', 'pengurus' => 'primary', 'pelayan' => 'success', 'staff' => 'secondary', 'lainnya' => 'warning'][$structure->position_level] ?? 'secondary' }}-subtle text-{{ ['gembala' => 'purple', 'pengurus' => 'primary', 'pelayan' => 'success', 'staff' => 'secondary', 'lainnya' => 'warning'][$structure->position_level] ?? 'secondary' }}">{{ $structure->position_level }}</span>
                                        </td>
                                        <td>{{ $structure->jemaat?->nama_lengkap ?? '<span class="text-muted">Belum diisi</span>' }}
                                        </td>
                                        <td><span
                                                class="badge bg-{{ $structure->is_active ? 'success' : 'danger' }}-subtle text-{{ $structure->is_active ? 'success' : 'danger' }}">{{ $structure->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-light"
                                                onclick="editStructure({{ json_encode($structure->load('jemaat')) }})"><i
                                                    class="ri-edit-line"></i></button>
                                            <button class="btn btn-sm btn-light text-danger"
                                                onclick="deleteStructure('{{ $structure->id }}')"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </tr>
                                    @if ($structure->children->isNotEmpty())
                                        @foreach ($structure->children as $child)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td>
                                                    <div class="fw-500"><i
                                                            class="ri-arrow-right-s-line me-1"></i>{{ $child->position_name }}
                                                    </div>
                                                </td>
                                                <td><span
                                                        class="badge bg-{{ ['gembala' => 'purple', 'pengurus' => 'primary', 'pelayan' => 'success', 'staff' => 'secondary', 'lainnya' => 'warning'][$child->position_level] ?? 'secondary' }}-subtle text-{{ ['gembala' => 'purple', 'pengurus' => 'primary', 'pelayan' => 'success', 'staff' => 'secondary', 'lainnya' => 'warning'][$child->position_level] ?? 'secondary' }}">{{ $child->position_level }}</span>
                                                </td>
                                                <td>{{ $child->jemaat?->nama_lengkap ?? '<span class="text-muted">Belum diisi</span>' }}
                                                </td>
                                                <td><span
                                                        class="badge bg-{{ $child->is_active ? 'success' : 'danger' }}-subtle text-{{ $child->is_active ? 'success' : 'danger' }}">{{ $child->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-light"
                                                        onclick="editStructure({{ json_encode($child->load('jemaat')) }})"><i
                                                            class="ri-edit-line"></i></button>
                                                    <button class="btn btn-sm btn-light text-danger"
                                                        onclick="deleteStructure('{{ $child->id }}')"><i
                                                            class="ri-delete-bin-line"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data struktur
                                            gereja</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if ($structures->hasPages())
                            <div class="card-footer">{{ $structures->withQueryString()->links() }}</div>
                        @endif
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
                                <select name="position_level" class="form-select" required>
                                    <option value="gembala">Gembala</option>
                                    <option value="pengurus">Pengurus</option>
                                    <option value="pelayan">Pelayan</option>
                                    <option value="staff">Staff</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
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
                            <select name="jemaat_id" class="form-select select2-jemaat" id="jemaat_id_add"
                                style="width:100%">
                                <option value="">-- Pilih Jemaat --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="500"
                                placeholder="Tugas dan tanggung jawab..."></textarea>
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
                    @csrf @method('PUT')
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
                                <select name="position_level" id="edit_position_level" class="form-select" required>
                                    <option value="gembala">Gembala</option>
                                    <option value="pengurus">Pengurus</option>
                                    <option value="pelayan">Pelayan</option>
                                    <option value="staff">Staff</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
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
                            <select name="jemaat_id" id="edit_jemaat_id" class="form-select select2-jemaat"
                                style="width:100%">
                                <option value="">-- Pilih Jemaat --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2" maxlength="500"></textarea>
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
    </style>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let structuresData = [];
            let draggedItem = null;
            let jemaatsData = [];
            const chartContainer = document.getElementById('structureChart');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const initSelect2 = (elementId, selectedId = null) => {
                const element = document.getElementById(elementId);
                if (!element) return;
                if ($(element).hasClass('select2-hidden-accessible')) $(element).select2('destroy');

                $(element).select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Pilih Jemaat --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $(element).closest('.modal'),
                    data: jemaatsData.map(j => ({
                        id: j.id,
                        text: j.nama_lengkap
                    }))
                });

                if (selectedId) $(element).val(selectedId).trigger('change');
            };

            const loadJemaats = async () => {
                try {
                    const res = await fetch("{{ route('admin.jemaat.api.list') }}");
                    if (res.ok) jemaatsData = await res.json();
                } catch (e) {
                    console.error(e);
                }
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

                setTimeout(() => {
                    drawConnectors(items);
                }, 100);
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
                node.dataset.id = item.id;
                node.style.left = x + 'px';
                node.style.top = y + 'px';

                const initials = (item.jemaat?.nama_lengkap || '?').charAt(0).toUpperCase();
                const badgeClass = item.position_level || 'lainnya';

                node.innerHTML = `
                    <span class="org-node-badge ${badgeClass}">${item.position_level}</span>
                    <div class="org-node-header">
                        <div class="org-node-avatar">${initials}</div>
                        <div class="org-node-title">${item.position_name}</div>
                        <div class="org-node-subtitle">${item.jemaat?.nama_lengkap || 'Belum diisi'}</div>
                    </div>
                    <div class="drop-zone" data-parent-id="${item.id}">+ Tambah Bawahan</div>
                `;

                node.addEventListener('mousedown', (e) => {
                    if (e.target.closest('.drop-zone')) return;
                    const rect = node.getBoundingClientRect();
                    const containerRect = chartContainer.getBoundingClientRect();
                    draggedItem = {
                        node: node,
                        id: item.id,
                        offsetX: e.clientX - rect.left,
                        offsetY: e.clientY - rect.top
                    };
                    node.classList.add('dragging');
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                    e.preventDefault();
                });

                node.addEventListener('click', (e) => {
                    if (e.target.closest('.drop-zone') || draggedItem) return;
                    editStructure(item);
                });

                const dropZone = node.querySelector('.drop-zone');
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.classList.add('active');
                });
                dropZone.addEventListener('dragleave', () => dropZone.classList.remove('active'));
                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('active');
                    if (draggedItem && draggedItem.id !== item.id) {
                        reorderItem(draggedItem.id, item.id);
                    }
                });

                return node;
            };

            const drawConnectors = (items) => {
                const existingConnectors = chartContainer.querySelectorAll('.org-connector');
                existingConnectors.forEach(c => c.remove());

                items.forEach(parent => {
                    if (!parent.children?.length) return;
                    const pNode = document.querySelector(`.org-node[data-id="${parent.id}"]`);
                    if (!pNode) return;

                    const pRect = pNode.getBoundingClientRect();
                    const contRect = chartContainer.getBoundingClientRect();
                    const parentCenterX = pRect.left + pRect.width / 2 - contRect.left + chartContainer
                        .scrollLeft;
                    const parentBottomY = pRect.bottom - contRect.top + chartContainer.scrollTop;

                    parent.children.forEach((child, index) => {
                        const cNode = document.querySelector(
                        `.org-node[data-id="${child.id}"]`);
                        if (!cNode) return;

                        const cRect = cNode.getBoundingClientRect();
                        const childCenterX = cRect.left + cRect.width / 2 - contRect.left +
                            chartContainer.scrollLeft;
                        const childTopY = cRect.top - contRect.top + chartContainer.scrollTop;

                        const connector = document.createElement('div');
                        connector.className = 'org-connector';

                        const midY = parentBottomY + (childTopY - parentBottomY) / 2;

                        if (parent.children.length === 1) {
                            connector.innerHTML = `
                                <div style="position:absolute; width:2px; background:#3b82f6; left:${parentCenterX}px; top:${parentBottomY}px; height:${childTopY - parentBottomY}px"></div>
                            `;
                        } else {
                            connector.innerHTML = `
                                <div style="position:absolute; width:2px; background:#3b82f6; left:${parentCenterX}px; top:${parentBottomY}px; height:${midY - parentBottomY}px"></div>
                                <div style="position:absolute; height:2px; background:#3b82f6; left:${Math.min(parentCenterX, childCenterX)}px; top:${midY}px; width:${Math.abs(childCenterX - parentCenterX)}px"></div>
                                <div style="position:absolute; width:2px; background:#3b82f6; left:${childCenterX}px; top:${midY}px; height:${childTopY - midY}px"></div>
                            `;
                        }

                        chartContainer.appendChild(connector);
                    });
                });
            };

            const onMouseMove = (e) => {
                if (!draggedItem) return;
                const containerRect = chartContainer.getBoundingClientRect();
                const newX = e.clientX - containerRect.left - draggedItem.offsetX + chartContainer.scrollLeft;
                const newY = e.clientY - containerRect.top - draggedItem.offsetY + chartContainer.scrollTop;
                draggedItem.node.style.left = newX + 'px';
                draggedItem.node.style.top = newY + 'px';
            };

            const onMouseUp = (e) => {
                if (!draggedItem) return;
                draggedItem.node.classList.remove('dragging');
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                const dropTarget = document.elementFromPoint(e.clientX, e.clientY)?.closest('.org-node');
                if (dropTarget && dropTarget.dataset.id !== draggedItem.id) {
                    reorderItem(draggedItem.id, dropTarget.dataset.id);
                } else {
                    loadStructure();
                }
                draggedItem = null;
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
                document.getElementById('edit_id').value = item.id;
                document.getElementById('edit_position_name').value = item.position_name;
                document.getElementById('edit_position_level').value = item.position_level;
                document.getElementById('edit_description').value = item.description || '';
                document.getElementById('edit_is_active').checked = !!item.is_active;

                loadParentsOptions('edit_parent_id', item.parent_id).then(() => {
                    initSelect2('edit_jemaat_id', item.jemaat?.id);
                });
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
                    const res = await fetch("{{ route('admin.church_structure.api.tree') }}", {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    const build = (nodes, prefix = '') => {
                        let html = '';
                        nodes.forEach(n => {
                            html +=
                                `<option value="${n.id}" ${n.id == selectedId ? 'selected' : ''}>${prefix}${n.position_name}</option>`;
                            if (n.children?.length) html += build(n.children, prefix + '─ ');
                        });
                        return html;
                    };
                    const select = document.getElementById(selectId);
                    if (select) select.innerHTML = '<option value="">Root / Tidak ada induk</option>' +
                        build(data.tree || []);
                } catch (e) {
                    console.error(e);
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
                btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Menyimpan...';

                try {
                    const res = await fetch(`/admin/church-structure/${id}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(this)
                    });
                    const result = await res.json();
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalEdit')).hide();
                        showToast('success', result.message);
                        loadStructure();
                    } else {
                        showToast('error', Object.values(result.errors || {}).flat().join(', ') ||
                            'Gagal update');
                    }
                } catch (err) {
                    showToast('error', 'Kesalahan koneksi');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-save-line me-1"></i>Update';
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

            document.getElementById('modalAdd').addEventListener('shown.bs.modal', () => {
                loadParentsOptions('parent_id');
                initSelect2('jemaat_id_add');
            });

            document.getElementById('modalEdit').addEventListener('shown.bs.modal', () => {
                if (window.currentEditItem) {
                    loadParentsOptions('edit_parent_id', window.currentEditItem.parent_id);
                    initSelect2('edit_jemaat_id', window.currentEditItem.jemaat?.id);
                }
            });

            document.getElementById('btnRefresh').addEventListener('click', loadStructure);

            loadJemaats().then(() => loadStructure());
        });
    </script>
@endpush
