@extends('layouts.public')
@section('title', 'Portal Data - ' . ($branch->name ?? 'Dashboard'))

@section('content')
    <header class="page-hero public-page-hero public-page-hero--about">
        <div class="page-hero-container fade-in">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <span>Portal Data</span>
            </nav>
            <span class="page-kicker">Data Access Portal</span>
            <h1 class="page-hero-title">{{ $branch->name }}</h1>
            <p class="page-hero-desc">Branch Code: <strong>{{ $branch->code }}</strong></p>
        </div>
        <span class="page-image-note">Foto ilustrasi · bukan dokumentasi Gereja Filadelfia</span>
    </header>

    <div class="lux-container"
        style="padding: 2rem 2rem 4rem; min-height: 100vh; background: var(--zaitun-sand, #FAF7EF);">

        {{-- Info Bar --}}
        <div class="lux-card portal-card"
            style="padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span class="portal-pill">
                    <i class="ri-key-2-line me-1"></i><span id="tokenCount"></span>
                </span>
                @if ($expires_at)
                    <span class="portal-pill portal-pill--warn">
                        <i class="ri-time-line me-1"></i>Exp: {{ \Carbon\Carbon::parse($expires_at)->format('d M Y') }}
                    </span>
                @endif
            </div>
            <button type="button" onclick="copyTableToken()" class="hero-btn portal-outline-btn">
                <i class="ri-file-copy-line"></i>Salin token tabel ini
            </button>
        </div>

        @if (session('success'))
            <div class="lux-card"
                style="border-left: 4px solid #22c55e; background: #f0fdf4; padding: 1rem 1.5rem; margin-bottom: 2rem; color: #166534; display: flex; align-items: center; gap: 0.75rem; border-radius: 16px;">
                <i class="ri-checkbox-circle-fill" style="font-size: 1.5rem;"></i>
                <span style="font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <div class="portal-grid">

            {{-- Sidebar: daftar tabel --}}
            <div class="lux-card portal-card" style="overflow: hidden;">
                <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; background: linear-gradient(135deg, #f8fafc, #fff);">
                    <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-database-2-line" style="color: var(--brand-primary);"></i>Tabel Tersedia
                    </h3>
                    <p style="margin: 0.5rem 0 0; font-size: 0.8rem; color: #64748b;">
                        Setiap tabel punya token sendiri. Tabel bergembok perlu token.
                    </p>
                </div>
                <div style="padding: 1rem;">
                    @foreach ($tables as $table)
                        <button type="button" class="table-btn" data-table="{{ $table['table_name'] }}">
                            <span class="table-btn-name">{{ $table['table_name'] }}</span>
                            <span class="table-btn-meta">
                                <span class="portal-pill portal-pill--sm">{{ count($table['allowed_columns']) }} kolom</span>
                                <i class="lock-icon ri-lock-line"></i>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Viewer --}}
            <div>
                <div class="lux-card portal-card" id="dataViewer"
                    style="min-height: 600px; display: flex; flex-direction: column; overflow: hidden;">

                    {{-- Empty state --}}
                    <div id="dataViewerEmpty"
                        style="flex: 1; padding: 4rem 2rem; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center;">
                            <i class="ri-table-2" style="font-size: 4rem; opacity: 0.2; display: block; margin-bottom: 1.25rem; color: var(--brand-primary);"></i>
                            <p style="margin: 0; font-size: 1rem; color: #94a3b8;">Pilih tabel di sidebar untuk melihat data</p>
                        </div>
                    </div>

                    {{-- Konten --}}
                    <div id="dataViewerContent" style="display: none; flex-direction: column; flex: 1;">
                        <div id="dataViewerHeader"
                            style="padding: 1.25rem 2rem; border-bottom: 1px solid #e2e8f0; background: linear-gradient(135deg, #f8fafc, #fff);">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;">
                                    <i class="ri-table-line me-2" style="color: var(--brand-primary);"></i><span id="tableNameDisplay">Pilih tabel</span>
                                </h3>
                                <button id="btnRefresh" type="button" class="lux-btn portal-outline-btn">
                                    <i class="ri-refresh-line me-1"></i>Refresh
                                </button>
                            </div>
                        </div>

                        {{-- Form token untuk tabel yang belum punya token --}}
                        <div id="tokenGate" style="display: none; padding: 2.5rem 2rem;">
                            <div style="max-width: 560px; margin: 0 auto;">
                                <div style="text-align: center; margin-bottom: 1.5rem;">
                                    <i class="ri-lock-2-line" style="font-size: 2.5rem; color: var(--brand-primary);"></i>
                                    <h4 style="margin: 0.5rem 0 0.25rem; font-weight: 700; color: #1e293b;">
                                        Token diperlukan untuk tabel <code id="gateTableName"></code>
                                    </h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: #64748b;">
                                        Tempel token khusus tabel ini. Token dikirim lewat email saat permintaan disetujui.
                                    </p>
                                </div>

                                <textarea id="tableTokenInput" rows="3" placeholder="Paste token tabel ini di sini..."
                                    style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 2px solid #e2e8f0; font-family: monospace; font-size: 0.85rem;"></textarea>

                                <div id="tokenGateMsg" role="alert"
                                    style="display: none; margin-top: 0.75rem; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.875rem;"></div>

                                <button id="btnSubmitTableToken" type="button" class="lux-btn lux-btn-primary"
                                    style="width: 100%; margin-top: 1rem; padding: 0.875rem; border-radius: 50px; font-weight: 600; background: var(--zaitun-deep, #34432C); color: #fff; border: none; cursor: pointer;">
                                    <i class="ri-lock-unlock-line me-2"></i>Verifikasi token
                                </button>
                            </div>
                        </div>

                        {{-- Info API --}}
                        <div id="apiInfoBar" style="padding: 1rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <i class="ri-code-s-slash-line" style="color: var(--brand-primary); font-size: 1.125rem;"></i>
                                <span style="font-weight: 600; color: #1e293b; font-size: 0.875rem;">API Endpoint</span>
                            </div>
                            <div style="background: #1e293b; border-radius: 12px; padding: 0.875rem 1rem; overflow-x: auto; position: relative; max-width: 55rem;">
                                <code id="apiEndpointUrl"
                                    style="font-family: 'Courier New', monospace; font-size: 0.8125rem; color: #e2e8f0; white-space: nowrap; padding-right: 4rem; display: inline-block;"></code>
                                <button type="button" onclick="copyApiUrl()"
                                    style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(255,255,255,0.1); border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">
                                    <i class="ri-file-copy-line"></i> Copy
                                </button>
                            </div>
                            <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                <span class="portal-pill portal-pill--sm"><i class="ri-key-line me-1"></i>Token:</span>
                                <code id="tokenDisplay"
                                    style="font-family: 'Courier New', monospace; font-size: 0.75rem; color: #1e293b; background: white; padding: 0.25rem 0.5rem; border-radius: 4px; border: 1px solid #e2e8f0; word-break: break-all;"></code>
                                <button type="button" onclick="toggleTokenVisibility()" class="lux-btn"
                                    style="background: var(--brand-light); color: var(--brand-primary); border: 1px solid var(--brand-primary); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;"
                                    aria-label="Tampilkan atau sembunyikan token">
                                    <i class="ri-eye-line"></i>
                                </button>
                                <span class="portal-pill portal-pill--sm"><i class="ri-server-line me-1"></i>Accept: application/json</span>
                            </div>
                        </div>

                        <div id="dataViewerBody" style="flex: 1; padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; max-width: 59rem;"></div>

                        <div id="dataViewerFooter"
                            style="padding: 1rem 2rem; border-top: 1px solid #e2e8f0; font-size: 0.875rem; color: #64748b; background: #f8fafc;">
                            <span id="paginationInfo"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .portal-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .portal-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .portal-pill {
            display: inline-flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            color: var(--brand-primary);
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));
        }

        .portal-pill--sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
        }

        .portal-pill--warn {
            color: var(--brand-accent);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        }

        .portal-outline-btn {
            background: #fff;
            color: var(--brand-primary);
            border: 2px solid var(--brand-primary);
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        /* Tombol tabel */
        .table-btn {
            width: 100%;
            text-align: left;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            margin-bottom: 0.5rem;
            background: var(--brand-light);
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .table-btn:hover {
            border-color: var(--brand-primary);
            transform: translateX(4px);
        }

        .table-btn-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9375rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-btn-meta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .lock-icon {
            color: #94a3b8;
            font-size: 1.05rem;
        }

        .table-btn.unlocked .lock-icon {
            color: #16a34a;
        }

        .table-btn.active {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
        }

        .table-btn.active .table-btn-name,
        .table-btn.active .lock-icon {
            color: #fff;
        }

        /* Tabel data */
        .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; min-width: 800px; }

        .data-table thead tr { background: linear-gradient(135deg, #f8fafc, #e2e8f0); position: sticky; top: 0; z-index: 10; }

        .data-table th {
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            white-space: nowrap;
            font-size: 0.75rem;
        }

        .data-table td {
            padding: 0.875rem 1rem;
            color: #1e293b;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .data-table tbody tr:nth-child(even) { background: #f8fafc; }
        .data-table tbody tr:hover { background: #f1f5f9; }

        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 1024px) {
            .portal-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .lux-container { padding: 1rem 1rem 3rem !important; }
            #dataViewerHeader, #apiInfoBar, #dataViewerFooter, #tokenGate { padding-left: 1rem !important; padding-right: 1rem !important; }
            .data-table { min-width: 600px; font-size: 0.75rem; }
            .data-table th, .data-table td { padding: 0.625rem 0.75rem; font-size: 0.75rem; }
        }
    </style>
@endsection

@push('scripts')
    <script>
        const portalConfig = {
            addTokenUrl: @json(route('data-portal.token.add')),
            csrf: @json(csrf_token()),
            tables: @json($tables),
            tokens: @json((object) $table_tokens), // { nama_tabel: rawToken }
        };

        let currentTable = null;
        let tokenVisible = false;

        const el = id => document.getElementById(id);
        const tokenFor = table => portalConfig.tokens[table] || null;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ===== STATE TAMPILAN =====
        function setView(view) {
            const isGate = view === 'gate';
            el('tokenGate').style.display = isGate ? 'block' : 'none';
            el('apiInfoBar').style.display = isGate ? 'none' : 'block';
            el('dataViewerBody').style.display = isGate ? 'none' : 'block';
            el('dataViewerFooter').style.display = isGate ? 'none' : 'block';
            el('btnRefresh').style.display = isGate ? 'none' : 'inline-flex';
        }

        function refreshLocks() {
            let open = 0;
            document.querySelectorAll('.table-btn').forEach(btn => {
                const has = !!tokenFor(btn.dataset.table);
                if (has) open++;
                btn.classList.toggle('unlocked', has);
                btn.title = has ? 'Token tersedia' : 'Token belum dimasukkan';
                const icon = btn.querySelector('.lock-icon');
                if (icon) icon.className = 'lock-icon ' + (has ? 'ri-lock-unlock-line' : 'ri-lock-line');
            });
            el('tokenCount').textContent = `${open} dari ${portalConfig.tables.length} tabel terbuka`;
        }

        function updateTokenDisplay() {
            const token = tokenFor(currentTable) || '';
            el('tokenDisplay').textContent = tokenVisible || token.length < 30 ?
                token :
                `${token.substring(0, 15)}...${token.substring(token.length - 10)}`;
        }

        function toggleTokenVisibility() {
            tokenVisible = !tokenVisible;
            updateTokenDisplay();
        }

        function copyText(text, okMsg) {
            if (!text) return;
            navigator.clipboard.writeText(text)
                .then(() => alert(okMsg))
                .catch(() => alert('Gagal menyalin'));
        }

        function copyTableToken() {
            const token = tokenFor(currentTable);
            if (!token) {
                alert('Pilih tabel yang sudah memiliki token terlebih dahulu.');
                return;
            }
            copyText(token, 'Token berhasil disalin!');
        }

        function copyApiUrl() {
            const token = tokenFor(currentTable);
            if (!currentTable || !token) return;
            copyText(`${window.location.origin}/v1/data/${currentTable}?token=${encodeURIComponent(token)}`,
                'URL API berhasil disalin!');
        }

        // ===== GATE TOKEN =====
        function setGateMessage(message, type = 'error') {
            const box = el('tokenGateMsg');
            if (!message) {
                box.style.display = 'none';
                return;
            }
            const styles = {
                error: ['#fef2f2', '#991b1b'],
                info: ['#eff6ff', '#1e40af']
            };
            const [bg, color] = styles[type] || styles.error;
            box.style.background = bg;
            box.style.color = color;
            box.textContent = message;
            box.style.display = 'block';
        }

        function showGate(table, message, type) {
            setView('gate');
            el('gateTableName').textContent = table;
            el('tableTokenInput').value = '';
            setGateMessage(message, type);
            el('tableTokenInput').focus();
        }

        async function submitTableToken() {
            const input = el('tableTokenInput');
            const btn = el('btnSubmitTableToken');
            const token = input.value.trim();

            if (token.length < 50) {
                setGateMessage('Token terlalu pendek. Periksa kembali token Anda.');
                return;
            }

            btn.disabled = true;
            setGateMessage('');

            try {
                const res = await fetch(portalConfig.addTokenUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': portalConfig.csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ token })
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok || !data.success) {
                    setGateMessage(data.message || 'Token tidak dapat diverifikasi. Muat ulang halaman lalu coba lagi.');
                    return;
                }

                portalConfig.tokens[data.table] = data.token;
                refreshLocks();

                if (data.table === currentTable) {
                    setView('data');
                    loadTableData(currentTable);
                } else {
                    input.value = '';
                    setGateMessage(
                        `Token itu untuk tabel "${data.table}", bukan "${currentTable}". Tabel "${data.table}" sekarang terbuka. Masukkan token untuk tabel "${currentTable}".`,
                        'info');
                }
            } catch (e) {
                setGateMessage('Terjadi kesalahan koneksi.');
            } finally {
                btn.disabled = false;
            }
        }

        // ===== PILIH TABEL =====
        function selectTable(table) {
            currentTable = table;
            tokenVisible = false;

            document.querySelectorAll('.table-btn').forEach(b =>
                b.classList.toggle('active', b.dataset.table === table));

            el('dataViewerEmpty').style.display = 'none';
            el('dataViewerContent').style.display = 'flex';
            el('tableNameDisplay').textContent = table;

            if (tokenFor(table)) {
                setView('data');
                loadTableData(table);
            } else {
                showGate(table);
            }
        }

        // ===== LOAD DATA =====
        async function loadTableData(tableName) {
            const token = tokenFor(tableName);
            if (!token) {
                showGate(tableName);
                return;
            }

            setView('data');
            updateTokenDisplay();

            const body = el('dataViewerBody');
            body.innerHTML =
                '<div style="text-align:center;padding:3rem;color:#64748b;"><div style="width:40px;height:40px;border:3px solid var(--brand-primary);border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 1rem;"></div><p style="margin:0.5rem 0 0;">Memuat data...</p></div>';

            el('apiEndpointUrl').textContent = `${window.location.origin}/v1/data/${tableName}?token=${token}`;

            try {
                const response = await fetch(`/v1/data/${tableName}?token=${encodeURIComponent(token)}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    const err = await response.json().catch(() => ({ error: response.statusText }));
                    const message = err.error || err.message || `HTTP ${response.status}`;

                    // Token tidak cocok / ditolak untuk tabel ini -> minta token yang benar
                    if ([401, 403].includes(response.status) || /not allowed/i.test(message)) {
                        showGate(tableName, `Token untuk tabel ini ditolak (${message}). Masukkan token yang benar.`);
                        return;
                    }
                    throw new Error(message);
                }

                renderDataTable(await response.json());
            } catch (error) {
                body.innerHTML = `
                    <div style="text-align:center;color:#ef4444;padding:3rem;">
                        <i class="ri-error-warning-line" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                        <p style="margin:0 0 0.5rem;font-size:1.125rem;font-weight:600;">${escapeHtml(error.message)}</p>
                        <button type="button" id="btnRetry" class="hero-btn" style="padding:0.625rem 1.5rem;border-radius:50px;font-weight:600;">Coba Lagi</button>
                    </div>`;
                el('btnRetry')?.addEventListener('click', () => loadTableData(tableName));
            }
        }

        function renderDataTable(result) {
            const { data, meta } = result;
            const container = el('dataViewerBody');

            if (!meta || !meta.columns_returned) {
                container.innerHTML = `<div style="text-align:center;color:#ef4444;padding:3rem;">
                    <i class="ri-error-warning-line" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                    <p style="margin:0;font-size:1rem;">Format response API tidak valid</p></div>`;
                return;
            }

            el('tableNameDisplay').textContent = meta.table;

            if (!data || data.length === 0) {
                container.innerHTML = `<div style="text-align:center;color:#94a3b8;padding:3rem;">
                    <i class="ri-inbox-line" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:1rem;"></i>
                    <p style="margin:0;font-size:1rem;">Tidak ada data ditemukan</p></div>`;
                return;
            }

            const columns = meta.columns_returned;
            let html = '<div class="table-wrapper"><table class="data-table"><thead><tr>';
            columns.forEach(col => { html += `<th>${escapeHtml(String(col))}</th>`; });
            html += '</tr></thead><tbody>';

            data.forEach(row => {
                html += '<tr>';
                columns.forEach(col => {
                    const value = row[col] ?? '';
                    const text = String(value);
                    const shown = text.length > 40 ? text.substring(0, 40) + '…' : text;
                    html += `<td title="${escapeHtml(text)}">${escapeHtml(shown)}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;

            el('paginationInfo').textContent =
                `Total ${meta.total_records} record • ${meta.table} • ${new Date(meta.timestamp).toLocaleString('id-ID')}`;
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', function() {
            refreshLocks();

            document.querySelectorAll('.table-btn').forEach(btn => {
                btn.addEventListener('click', () => selectTable(btn.dataset.table));
            });

            el('btnRefresh')?.addEventListener('click', () => {
                if (currentTable) loadTableData(currentTable);
            });

            el('btnSubmitTableToken')?.addEventListener('click', submitTableToken);

            el('tableTokenInput')?.addEventListener('keydown', e => {
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) submitTableToken();
            });
        });
    </script>
@endpush