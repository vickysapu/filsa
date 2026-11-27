@extends('layouts.public')
@section('title', 'Portal Data - ' . ($branch->name ?? 'Dashboard'))

@php
    $portalTokens = (object) $table_tokens;
    $portalAddUrl = route('data-portal.token.add');
    $portalCsrf = csrf_token();
@endphp

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

    <div class="portal-page">

        {{-- Info bar --}}
        <div class="portal-bar">
            <div class="portal-bar-left">
                <span class="portal-pill"><i class="ri-key-2-line"></i><span id="tokenCount"></span></span>
                @if ($expires_at)
                    <span class="portal-pill portal-pill--warn">
                        <i class="ri-time-line"></i>Exp: {{ \Carbon\Carbon::parse($expires_at)->format('d M Y') }}
                    </span>
                @else
                    <span class="portal-pill" style="background:#eef7ee;color:#2e6930;border:1px solid #b7dfb9;">
                        <i class="ri-shield-check-line"></i>Aktif Selamanya
                    </span>
                @endif
            </div>
            <button type="button" onclick="copyTableToken()" class="portal-btn">
                <i class="ri-file-copy-line"></i>Salin token tabel ini
            </button>
        </div>

        @if (session('success'))
            <div class="portal-flash" role="status">
                <i class="ri-checkbox-circle-fill"></i><span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="portal-grid">

            {{-- Sidebar --}}
            <aside class="portal-side">
                <div class="portal-side-head">
                    <h3><i class="ri-database-2-line"></i>Tabel Tersedia</h3>
                    <p>Semua tabel yang disetujui dalam permohonan ini dapat diakses langsung.</p>
                </div>
                <div class="portal-side-list">
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
            </aside>

            {{-- Viewer --}}
            <main class="portal-main">
                <div class="portal-viewer" id="dataViewer">

                    <div id="dataViewerEmpty" class="portal-empty">
                        <div>
                            <i class="ri-table-2"></i>
                            <p>Pilih tabel di sidebar untuk melihat data</p>
                        </div>
                    </div>

                    <div id="dataViewerContent" style="display:none;flex-direction:column;flex:1;min-width:0;">
                        <div class="portal-viewer-head" id="dataViewerHeader">
                            <h3><i class="ri-table-line"></i><span id="tableNameDisplay">Pilih tabel</span></h3>
                            <button id="btnRefresh" type="button" class="portal-btn">
                                <i class="ri-refresh-line"></i>Refresh
                            </button>
                        </div>

                        {{-- Gate token --}}
                        <div id="tokenGate" style="display:none;">
                            <div class="gate-box">
                                <i class="ri-lock-2-line gate-icon"></i>
                                <h4>Token diperlukan untuk tabel <code id="gateTableName"></code></h4>
                                <p>Tempel token khusus tabel ini. Token dikirim lewat email saat permintaan disetujui.</p>
                                <textarea id="tableTokenInput" rows="3" placeholder="Paste token tabel ini di sini..."></textarea>
                                <div id="tokenGateMsg" role="alert" style="display:none;"></div>
                                <button id="btnSubmitTableToken" type="button" class="portal-btn portal-btn--solid">
                                    <i class="ri-lock-unlock-line"></i>Verifikasi token
                                </button>
                            </div>
                        </div>

                        {{-- Info API --}}
                        <div id="apiInfoBar" class="api-info">
                            <div class="api-info-title"><i class="ri-code-s-slash-line"></i>API Endpoint</div>
                            <div class="api-code">
                                <code id="apiEndpointUrl"></code>
                                <button type="button" onclick="copyApiUrl()"><i class="ri-file-copy-line"></i> Copy</button>
                            </div>
                            <div class="api-token-row">
                                <span class="portal-pill portal-pill--sm"><i class="ri-key-line"></i>Token:</span>
                                <code id="tokenDisplay"></code>
                                <button type="button" onclick="toggleTokenVisibility()" class="portal-icon-btn"
                                    aria-label="Tampilkan atau sembunyikan token"><i class="ri-eye-line"></i></button>
                                <span class="portal-pill portal-pill--sm"><i class="ri-server-line"></i>Accept: application/json</span>
                            </div>
                        </div>

                        <div id="dataViewerBody"></div>

                        <div id="dataViewerFooter">
                            <span id="paginationInfo"></span>
                            <span id="nullInfo"></span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- Lightbox gambar --}}
    <dialog id="imgDialog" class="img-dialog" aria-label="Pratinjau gambar">
        <button type="button" id="imgDialogClose" class="portal-icon-btn img-dialog-close" aria-label="Tutup"><i class="ri-close-line"></i></button>
        <img id="imgDialogImg" alt="">
        <p id="imgDialogCaption"></p>
    </dialog>

    <style>
        .portal-page {
            --p-primary: var(--zaitun-primary, #526E3B);
            --p-deep: var(--zaitun-deep-dark, #1E2619);
            --p-sand: var(--zaitun-sand, #FAF7EF);
            --p-cream: var(--zaitun-cream, #F3EEDF);
            --p-border: var(--zaitun-border, #DDD6C3);
            --p-muted: var(--zaitun-text-secondary, #6B6F62);
            --p-gold: var(--zaitun-gold, #C08A3E);
            max-width: 1280px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 5rem;
        }

        .portal-bar {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;
            padding: 0.9rem 0; margin-bottom: 1.5rem;
            border-top: 1px solid var(--p-border); border-bottom: 1px solid var(--p-border);
        }
        .portal-bar-left { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

        .portal-pill {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.4rem 0.85rem; border-radius: 2px;
            font-size: 0.78rem; font-weight: 600; letter-spacing: 0.03em;
            color: var(--p-primary); background: rgba(82, 110, 59, 0.1);
        }
        .portal-pill--sm { padding: 0.2rem 0.55rem; font-size: 0.7rem; }
        .portal-pill--warn { color: var(--p-gold); background: rgba(192, 138, 62, 0.14); }

        .portal-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem; border-radius: 2px; cursor: pointer;
            font-size: 0.85rem; font-weight: 600;
            background: transparent; color: var(--p-deep); border: 1px solid var(--p-deep);
            transition: background 0.2s, color 0.2s;
        }
        .portal-btn:hover:not(:disabled) { background: var(--p-deep); color: var(--p-sand); }
        .portal-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .portal-btn--solid { width: 100%; margin-top: 1rem; padding: 0.85rem; background: var(--p-deep); color: var(--p-sand); }
        .portal-btn--solid:hover:not(:disabled) { background: var(--p-primary); border-color: var(--p-primary); }

        .portal-icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 2px; cursor: pointer;
            background: transparent; color: var(--p-deep); border: 1px solid var(--p-border);
        }
        .portal-icon-btn:hover { background: var(--p-deep); color: var(--p-sand); }

        .portal-btn:focus-visible, .portal-icon-btn:focus-visible, .table-btn:focus-visible,
        .thumb:focus-visible, #tableTokenInput:focus-visible {
            outline: 2px solid var(--p-primary); outline-offset: 2px;
        }

        .portal-flash {
            display: flex; align-items: center; gap: 0.7rem; margin-bottom: 1.5rem;
            padding: 0.9rem 1.2rem; border-left: 3px solid var(--p-primary);
            background: rgba(82, 110, 59, 0.08); color: var(--p-deep); font-weight: 600; font-size: 0.92rem;
        }
        .portal-flash i { font-size: 1.3rem; color: var(--p-primary); }

        .portal-grid { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 2rem; align-items: start; }
        .portal-main { min-width: 0; }

        /* Sidebar */
        .portal-side { border: 1px solid var(--p-border); background: #fff; border-radius: 2px; overflow: hidden; }
        .portal-side-head { padding: 1.25rem 1.25rem 1rem; border-bottom: 1px solid var(--p-border); background: var(--p-cream); }
        .portal-side-head h3 {
            margin: 0; display: flex; align-items: center; gap: 0.5rem;
            font-family: 'Playfair Display', Georgia, serif; font-size: 1.15rem; font-weight: 600; color: var(--p-deep);
        }
        .portal-side-head h3 i { color: var(--p-primary); }
        .portal-side-head p { margin: 0.4rem 0 0; font-size: 0.78rem; color: var(--p-muted); line-height: 1.5; }
        .portal-side-list { padding: 0.5rem; max-height: 70vh; overflow-y: auto; }

        .table-btn {
            width: 100%; text-align: left; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
            padding: 0.8rem 0.9rem; margin-bottom: 2px; border: 0; border-left: 3px solid transparent;
            border-radius: 2px; background: transparent; cursor: pointer; transition: background 0.2s, border-color 0.2s;
        }
        .table-btn:hover { background: var(--p-cream); border-left-color: var(--p-border); }
        .table-btn-name { font-weight: 600; font-size: 0.9rem; color: var(--p-deep); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-btn-meta { display: inline-flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
        .lock-icon { color: #9a9a8c; font-size: 1.05rem; }
        .table-btn.unlocked .lock-icon { color: var(--p-primary); }
        .table-btn.active { background: var(--p-deep); border-left-color: var(--p-gold); }
        .table-btn.active .table-btn-name, .table-btn.active .lock-icon { color: #fff; }
        .table-btn.active .portal-pill { background: rgba(255, 255, 255, 0.14); color: #fff; }

        /* Viewer */
        .portal-viewer {
            min-height: 560px; display: flex; flex-direction: column; min-width: 0;
            border: 1px solid var(--p-border); background: #fff; border-radius: 2px; overflow: hidden;
        }
        .portal-empty { flex: 1; display: flex; align-items: center; justify-content: center; padding: 4rem 2rem; text-align: center; }
        .portal-empty i { display: block; font-size: 3.5rem; color: var(--p-primary); opacity: 0.25; margin-bottom: 1rem; }
        .portal-empty p { margin: 0; color: var(--p-muted); }

        .portal-viewer-head {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;
            padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--p-border); background: var(--p-cream);
        }
        .portal-viewer-head h3 {
            margin: 0; display: flex; align-items: center; gap: 0.6rem;
            font-family: 'Playfair Display', Georgia, serif; font-size: 1.3rem; font-weight: 600; color: var(--p-deep);
        }
        .portal-viewer-head h3 i { color: var(--p-primary); }

        /* Gate */
        #tokenGate { padding: 2.5rem 1.5rem; }
        .gate-box { max-width: 560px; margin: 0 auto; text-align: center; }
        .gate-icon { font-size: 2.4rem; color: var(--p-primary); }
        .gate-box h4 { margin: 0.5rem 0 0.3rem; font-family: 'Playfair Display', Georgia, serif; font-size: 1.2rem; color: var(--p-deep); }
        .gate-box p { margin: 0 0 1.25rem; font-size: 0.9rem; color: var(--p-muted); }
        #tableTokenInput {
            width: 100%; padding: 0.75rem; border: 1px solid var(--p-border); border-radius: 2px;
            font-family: ui-monospace, 'Courier New', monospace; font-size: 0.82rem; resize: vertical; background: var(--p-sand);
        }
        #tokenGateMsg { margin-top: 0.75rem; padding: 0.7rem 1rem; border-radius: 2px; font-size: 0.85rem; text-align: left; }

        /* API info */
        .api-info { padding: 1rem 1.5rem; background: var(--p-sand); border-bottom: 1px solid var(--p-border); min-width: 0; }
        .api-info-title { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.6rem; font-weight: 700; font-size: 0.8rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--p-deep); }
        .api-info-title i { color: var(--p-primary); }
        .api-code { position: relative; background: var(--p-deep); border-radius: 2px; padding: 0.8rem 5rem 0.8rem 1rem; overflow-x: auto; }
        .api-code code { font-family: ui-monospace, 'Courier New', monospace; font-size: 0.78rem; color: #e8e4d4; white-space: nowrap; }
        .api-code button {
            position: absolute; top: 0.45rem; right: 0.45rem; padding: 0.25rem 0.6rem; border: 0; border-radius: 2px;
            background: rgba(255, 255, 255, 0.12); color: #fff; font-size: 0.72rem; cursor: pointer;
        }
        .api-code button:hover { background: rgba(255, 255, 255, 0.25); }
        .api-token-row { margin-top: 0.7rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .api-token-row code {
            font-family: ui-monospace, 'Courier New', monospace; font-size: 0.72rem; color: var(--p-deep);
            background: #fff; padding: 0.25rem 0.5rem; border: 1px solid var(--p-border); border-radius: 2px; word-break: break-all;
        }

        /* Tabel data */
        #dataViewerBody { flex: 1; min-width: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; min-width: 640px; }
        .data-table thead th {
            position: sticky; top: 0; z-index: 2; padding: 0.85rem 1rem; text-align: left; white-space: nowrap;
            background: var(--p-cream); border-bottom: 1px solid var(--p-border);
            font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--p-muted);
        }
        .data-table td {
            padding: 0.7rem 1rem; vertical-align: middle; color: var(--p-deep);
            border-bottom: 1px solid #efeadb; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .data-table tbody tr:hover { background: var(--p-sand); }

        .cell-null {
            display: inline-block; padding: 0.1rem 0.45rem; border-radius: 2px;
            font-family: ui-monospace, 'Courier New', monospace; font-size: 0.7rem;
            color: #b4532a; background: rgba(200, 90, 50, 0.1);
        }

        .thumb {
            display: inline-flex; padding: 0; border: 1px solid var(--p-border); border-radius: 2px;
            background: var(--p-cream); cursor: zoom-in; overflow: hidden; vertical-align: middle;
        }
        .thumb img { width: 44px; height: 44px; object-fit: cover; object-position: center 25%; display: block; }
        .thumb-broken {
            display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px;
            border: 1px dashed #c9733f; border-radius: 2px; color: #c9733f; font-size: 1.2rem; background: rgba(200, 90, 50, 0.06);
        }

        #dataViewerFooter {
            display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
            padding: 0.85rem 1.5rem; border-top: 1px solid var(--p-border);
            background: var(--p-sand); font-size: 0.82rem; color: var(--p-muted);
        }
        #nullInfo { color: #b4532a; }

        .state-box { text-align: center; padding: 3rem 1.5rem; color: var(--p-muted); }
        .state-box i { display: block; font-size: 2.6rem; margin-bottom: 0.75rem; }
        .state-box--error { color: #b4532a; }
        .spinner {
            width: 36px; height: 36px; margin: 0 auto 1rem; border-radius: 50%;
            border: 3px solid var(--p-primary); border-top-color: transparent; animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Lightbox */
        .img-dialog {
            position: fixed; inset: 0; margin: auto; padding: 1rem; border: 0; border-radius: 2px;
            max-width: min(720px, calc(100% - 2rem)); background: var(--zaitun-sand, #FAF7EF); text-align: center;
        }
        .img-dialog::backdrop { background: rgba(30, 38, 25, 0.7); }
        .img-dialog img { max-width: 100%; max-height: 70vh; display: block; margin: 0 auto; border-radius: 2px; }
        .img-dialog p { margin: 0.75rem 0 0; font-family: ui-monospace, monospace; font-size: 0.75rem; color: #6B6F62; word-break: break-all; }
        .img-dialog-close { position: absolute; top: 0.5rem; right: 0.5rem; background: #fff; }

        @media (max-width: 1024px) { .portal-grid { grid-template-columns: 1fr; } .portal-side-list { max-height: 260px; } }
        @media (max-width: 768px) {
            .portal-page { padding: 1.5rem 1rem 3rem; }
            .portal-viewer-head, .api-info, #tokenGate, #dataViewerFooter { padding-left: 1rem; padding-right: 1rem; }
            .data-table { font-size: 0.78rem; }
            .data-table th, .data-table td { padding: 0.6rem 0.7rem; }
        }
    </style>
@endsection

@push('scripts')
    <script>
        const portalConfig = {
            addTokenUrl: @json($portalAddUrl),
            csrf: @json($portalCsrf),
            tables: @json($tables),
            tokens: @json($portalTokens), // { nama_tabel: rawToken }
        };

        let currentTable = null;
        let tokenVisible = false;

        const el = id => document.getElementById(id);
        const tokenFor = table => portalConfig.tokens[table] || null;

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        // ===== GAMBAR =====
        const IMG_EXT = /\.(jpe?g|png|gif|webp|avif|svg)(\?.*)?$/i;
        const IMG_COL = /(foto|photo|gambar|image|img|avatar|logo|cover|thumbnail|banner)/i;
        const IMG_PATH = /^(https?:)?\/\/|^\/?(public\/|storage\/|assets\/|images\/|uploads\/)/i;

        function isImageCell(col, text) {
            if (!text) return false;
            return IMG_EXT.test(text) || (IMG_COL.test(col) && IMG_PATH.test(text));
        }

        function imageSrc(text) {
            if (/^(https?:)?\/\//i.test(text) || text.startsWith('data:')) return text;
            const p = text.replace(/^\/?public\//, '').replace(/^\/+/, '');
            if (/^(storage|assets|images|uploads)\//.test(p)) return `${location.origin}/${p}`;
            return `${location.origin}/storage/${p}`;
        }

        // ===== STATE TAMPILAN =====
        function setView(view) {
            const isGate = view === 'gate';
            el('tokenGate').style.display = isGate ? 'block' : 'none';
            el('apiInfoBar').style.display = isGate ? 'none' : 'block';
            el('dataViewerBody').style.display = isGate ? 'none' : 'block';
            el('dataViewerFooter').style.display = isGate ? 'none' : 'flex';
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
            const styles = { error: ['#fdf0ea', '#9a3f1c'], info: ['#eef3e8', '#3d5429'] };
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
            body.innerHTML = '<div class="state-box"><div class="spinner"></div><p style="margin:0">Memuat data...</p></div>';
            el('paginationInfo').textContent = '';
            el('nullInfo').textContent = '';

            el('apiEndpointUrl').textContent = `${window.location.origin}/v1/data/${tableName}?token=${token}`;

            try {
                const response = await fetch(`/v1/data/${tableName}?token=${encodeURIComponent(token)}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    const err = await response.json().catch(() => ({ error: response.statusText }));
                    const message = err.error || err.message || `HTTP ${response.status}`;

                    if ([401, 403].includes(response.status) || /not allowed/i.test(message)) {
                        showGate(tableName, `Token untuk tabel ini ditolak (${message}). Masukkan token yang benar.`);
                        return;
                    }
                    throw new Error(message);
                }

                renderDataTable(await response.json());
            } catch (error) {
                body.innerHTML = `
                    <div class="state-box state-box--error">
                        <i class="ri-error-warning-line"></i>
                        <p style="margin:0 0 1rem;font-size:1.05rem;font-weight:600;">${escapeHtml(error.message)}</p>
                        <button type="button" id="btnRetry" class="portal-btn">Coba Lagi</button>
                    </div>`;
                el('btnRetry')?.addEventListener('click', () => loadTableData(tableName));
            }
        }

        function renderCell(col, value) {
            if (value === null || value === undefined || value === '') {
                return '<span class="cell-null">null</span>';
            }

            const text = String(value);

            if (isImageCell(col, text)) {
                const src = imageSrc(text);
                return `<button type="button" class="thumb" data-src="${escapeHtml(src)}" data-raw="${escapeHtml(text)}" title="${escapeHtml(text)}">
                            <img src="${escapeHtml(src)}" alt="${escapeHtml(col)}" loading="lazy">
                        </button>`;
            }

            const shown = text.length > 60 ? text.substring(0, 60) + '…' : text;
            return `<span title="${escapeHtml(text)}">${escapeHtml(shown)}</span>`;
        }

        function renderDataTable(result) {
            const data = result.data;
            const meta = result.meta || {};
            const container = el('dataViewerBody');
            const columns = meta.columns_returned || meta.columns;

            if (!columns) {
                container.innerHTML = `<div class="state-box state-box--error"><i class="ri-error-warning-line"></i>
                    <p style="margin:0">Format response API tidak valid</p></div>`;
                return;
            }

            if (meta.table) el('tableNameDisplay').textContent = meta.table;

            if (!data || data.length === 0) {
                container.innerHTML = `<div class="state-box"><i class="ri-inbox-line" style="opacity:.35"></i>
                    <p style="margin:0">Tidak ada data ditemukan</p></div>`;
                return;
            }

            let html = '<table class="data-table"><thead><tr>';
            columns.forEach(col => { html += `<th>${escapeHtml(col)}</th>`; });
            html += '</tr></thead><tbody>';

            const imageNulls = {};

            data.forEach(row => {
                html += '<tr>';
                columns.forEach(col => {
                    const value = row[col];
                    if (IMG_COL.test(col) && (value === null || value === undefined || value === '')) {
                        imageNulls[col] = (imageNulls[col] || 0) + 1;
                    }
                    html += `<td>${renderCell(col, value)}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;

            const total = meta.total_records ?? meta.total ?? data.length;
            const parts = [`Total ${total} record`, meta.table || currentTable];
            const when = meta.timestamp ? new Date(meta.timestamp) : null;
            if (when && !isNaN(when)) parts.push(when.toLocaleString('id-ID'));
            if (meta.current_page && meta.last_page) parts.push(`Halaman ${meta.current_page}/${meta.last_page}`);
            el('paginationInfo').textContent = parts.join(' • ');

            const nullMsgs = Object.entries(imageNulls)
                .map(([col, n]) => `Kolom "${col}": ${n} dari ${data.length} baris kosong (null)`);
            el('nullInfo').textContent = nullMsgs.join(' • ');
        }

        // ===== LIGHTBOX + GAMBAR RUSAK =====
        function initImageHandlers() {
            const body = el('dataViewerBody');
            const dialog = el('imgDialog');

            body.addEventListener('click', e => {
                const thumb = e.target.closest('.thumb');
                if (!thumb) return;
                el('imgDialogImg').src = thumb.dataset.src;
                el('imgDialogCaption').textContent = thumb.dataset.raw;
                dialog.showModal();
            });

            // error event tidak bubble, jadi pakai capture
            body.addEventListener('error', e => {
                const img = e.target;
                if (img.tagName !== 'IMG') return;
                const holder = img.closest('.thumb');
                if (!holder) return;
                const broken = document.createElement('span');
                broken.className = 'thumb-broken';
                broken.title = `Gambar tidak ditemukan: ${holder.dataset.src}`;
                broken.innerHTML = '<i class="ri-image-line"></i>';
                holder.replaceWith(broken);
            }, true);

            el('imgDialogClose').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', function() {
            refreshLocks();
            initImageHandlers();

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