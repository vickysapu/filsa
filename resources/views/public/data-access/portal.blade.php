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

        {{-- Token Info Bar --}}
        <div class="lux-card"
            style="background: white; border-radius: 24px; padding: 1rem 1.5rem; margin-bottom: 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span class="lux-badge"
                    style="font-family: monospace; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1)); padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.875rem; font-weight: 600; color: var(--brand-primary);">
                    <i class="ri-key-2-line me-1"></i>{{ $token_prefix }}...
                </span>
                @if ($expires_at)
                    <span class="lux-badge"
                        style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1)); padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.875rem; font-weight: 600; color: var(--brand-accent);">
                        <i class="ri-time-line me-1"></i>Exp: {{ \Carbon\Carbon::parse($expires_at)->format('d M Y') }}
                    </span>
                @endif
            </div>
            <button type="button" onclick="copyFullToken()" class="hero-btn"
                style="background: var(--brand-light); color: var(--brand-primary); border: 2px solid var(--brand-primary); padding: 0.5rem 1.25rem; border-radius: 50px; font-weight: 600; font-size: 0.875rem; text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <i class="ri-file-copy-line"></i>Salin Token
            </button>
        </div>

        @if (session('success'))
            <div class="lux-card"
                style="border-left: 4px solid #22c55e; background: #f0fdf4; padding: 1rem 1.5rem; margin-bottom: 2rem; color: #166534; display: flex; align-items: center; gap: 0.75rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);">
                <i class="ri-checkbox-circle-fill" style="font-size: 1.5rem;"></i>
                <span style="font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Main Content Grid --}}
        <div style="display: grid; grid-template-columns: 320px 1fr; gap: 2rem; align-items: start;">

            {{-- Sidebar: List Tables --}}
            <div class="lux-card"
                style="background: white; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); overflow: hidden;">
                <div
                    style="padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; background: linear-gradient(135deg, #f8fafc, #fff);">
                    <h3
                        style="margin: 0; font-size: 1.125rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-database-2-line" style="color: var(--brand-primary);"></i>Tabel Tersedia
                    </h3>
                </div>
                <div style="padding: 1rem;">
                    @foreach ($tables as $table)
                        <button class="table-btn" data-table="{{ $table['table_name'] }}"
                            style="width: 100%; text-align: left; padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 0.5rem; background: var(--brand-light); border: 2px solid transparent; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: space-between;"
                            onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 20px rgba(79, 70, 229, 0.15)'"
                            onmouseout="this.style.borderColor='transparent'; this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                            <span
                                style="font-weight: 600; color: #1e293b; font-size: 0.9375rem;">{{ $table['table_name'] }}</span>
                            <span class="lux-badge"
                                style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1)); font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600; color: var(--brand-primary);">
                                {{ count($table['allowed_columns']) }} kolom
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Main: Data Viewer --}}
            <div>
                <div class="lux-card" id="dataViewer"
                    style="background: white; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); min-height: 600px; display: flex; flex-direction: column; overflow: hidden;">

                    {{-- Empty State --}}
                    <div id="dataViewerEmpty"
                        style="flex: 1; padding: 4rem 2rem; display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <div style="text-align: center;">
                            <i class="ri-table-2"
                                style="font-size: 4rem; opacity: 0.2; display: block; margin-bottom: 1.25rem; color: var(--brand-primary);"></i>
                            <p style="margin: 0; font-size: 1rem; color: #94a3b8;">Pilih tabel di sidebar untuk melihat data
                            </p>
                        </div>
                    </div>

                    {{-- Data Content --}}
                    <div id="dataViewerContent" style="display: none; flex-direction: column; height: 100%;">
                        <div id="dataViewerHeader"
                            style="padding: 1.25rem 2rem; border-bottom: 1px solid #e2e8f0; background: linear-gradient(135deg, #f8fafc, #fff);">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;"
                                    id="currentTableName">
                                    <i class="ri-table-line me-2" style="color: var(--brand-primary);"></i><span
                                        id="tableNameDisplay">Pilih tabel</span>
                                </h3>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button id="btnRefresh" class="lux-btn"
                                        style="background: white; color: var(--brand-primary); border: 2px solid var(--brand-primary); padding: 0.5rem 1rem; border-radius: 50px; font-weight: 600; font-size: 0.875rem; transition: all 0.3s ease;">
                                        <i class="ri-refresh-line me-1"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- API Endpoint Info with Full Token --}}
                        <div style="padding: 1rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <i class="ri-code-s-slash-line"
                                    style="color: var(--brand-primary); font-size: 1.125rem;"></i>
                                <span style="font-weight: 600; color: #1e293b; font-size: 0.875rem;">API Endpoint</span>
                            </div>
                            <div
                                style="background: #1e293b; border-radius: 12px; padding: 0.875rem 1rem; overflow-x: auto; position: relative; max-width: 55rem">
                                <code id="apiEndpointUrl"
                                    style="font-family: 'Courier New', monospace; font-size: 0.8125rem; color: #e2e8f0; white-space: nowrap;"></code>
                                <button onclick="copyApiUrl()"
                                    style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(255,255,255,0.1); border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">
                                    <i class="ri-file-copy-line"></i> Copy
                                </button>
                            </div>
                            <div
                                style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                <span class="lux-badge"
                                    style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 50px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1)); color: var(--brand-primary); font-weight: 600;">
                                    <i class="ri-key-line me-1"></i>Token:
                                </span>
                                <code id="tokenDisplay"
                                    style="font-family: 'Courier New', monospace; font-size: 0.75rem; color: #1e293b; background: white; padding: 0.25rem 0.5rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    {{ session('portal_token') }}
                                </code>
                                <button onclick="toggleTokenVisibility()" class="lux-btn"
                                    style="background: var(--brand-light); color: var(--brand-primary); border: 1px solid var(--brand-primary); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                                    <i class="ri-eye-line"></i>
                                </button>
                                <span class="lux-badge"
                                    style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 50px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1)); color: var(--brand-primary); font-weight: 600;">
                                    <i class="ri-server-line me-1"></i>Accept: application/json
                                </span>
                            </div>
                        </div>

                        <div id="dataViewerBody" style="flex: 1; padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; max-width: 59rem">
                        </div>

                        <div
                            style="padding: 1rem 2rem; border-top: 1px solid #e2e8f0; font-size: 0.875rem; color: #64748b; background: #f8fafc;">
                            <span id="paginationInfo"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CSS Styles --}}
    <style>
        @media (max-width: 1024px) {
            div[style*="grid-template-columns: 320px 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 768px) {
            .lux-container {
                padding: 1rem 1rem 3rem !important;
            }

            #dataViewerHeader,
            div[style*="padding: 1rem 2rem; border-top"] {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            min-width: 800px;
        }

        .data-table thead tr {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            position: sticky;
            top: 0;
            z-index: 10;
        }

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
            font-size: 0.875rem;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .data-table tbody tr:hover {
            background: #f1f5f9 !important;
        }

        @media (max-width: 768px) {
            .data-table {
                min-width: 600px;
                font-size: 0.75rem;
            }

            .data-table th,
            .data-table td {
                padding: 0.625rem 0.75rem;
                font-size: 0.75rem;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        const portalConfig = {
            baseUrl: "",
            fetchUrl: "{{ route('data-portal.data.fetch', ['table' => '__TABLE__']) }}",
            tables: @json($tables),
            fullToken: "{{ session('portal_token') }}",
        };

        let currentTable = null;
        let tokenVisible = false;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔑 Token available:', !!portalConfig.fullToken);

            // Set initial token display (masked)
            updateTokenDisplay();

            document.querySelectorAll('.table-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const table = this.dataset.table;
                    loadTableData(table);

                    document.querySelectorAll('.table-btn').forEach(b => {
                        b.style.background = 'var(--brand-light)';
                        b.style.borderColor = 'transparent';
                        b.style.transform = 'translateX(0)';
                        b.style.boxShadow = 'none';
                    });
                    this.style.background = 'var(--brand-primary)';
                    this.style.color = 'white';
                    this.style.borderColor = 'var(--brand-primary)';
                    this.style.boxShadow = '0 4px 20px rgba(79, 70, 229, 0.3)';
                });
            });

            document.getElementById('btnRefresh')?.addEventListener('click', function() {
                if (currentTable) loadTableData(currentTable);
            });
        });

        function updateTokenDisplay() {
            const tokenEl = document.getElementById('tokenDisplay');
            if (!tokenEl) return;

            if (tokenVisible) {
                tokenEl.textContent = portalConfig.fullToken;
            } else {
                const prefix = portalConfig.fullToken.substring(0, 15);
                const suffix = portalConfig.fullToken.substring(portalConfig.fullToken.length - 10);
                tokenEl.textContent = `${prefix}...${suffix}`;
            }
        }

        function toggleTokenVisibility() {
            tokenVisible = !tokenVisible;
            updateTokenDisplay();
        }

        function copyFullToken() {
            navigator.clipboard.writeText(portalConfig.fullToken).then(() => {
                alert('✅ Token berhasil disalin!');
            }).catch(() => {
                alert('❌ Gagal menyalin token');
            });
        }

        function copyApiUrl() {
            const urlEl = document.getElementById('apiEndpointUrl');
            if (urlEl && currentTable) {
                const url =
                    `${window.location.origin}/v1/data/${currentTable}?token=${encodeURIComponent(portalConfig.fullToken)}`;
                navigator.clipboard.writeText(url).then(() => {
                    alert('✅ URL API berhasil disalin!');
                }).catch(() => {
                    alert('❌ Gagal menyalin URL');
                });
            }
        }

        async function loadTableData(tableName) {
            currentTable = tableName;

            document.getElementById('dataViewerEmpty').style.display = 'none';
            document.getElementById('dataViewerContent').style.display = 'flex';
            document.getElementById('dataViewerBody').innerHTML =
                '<div style="text-align: center; padding: 3rem; color: #64748b;"><div style="width: 40px; height: 40px; border: 3px solid var(--brand-primary); border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div><p style="margin: 0.5rem 0 0;">Memuat data...</p></div>';

            try {
                if (!portalConfig.fullToken || portalConfig.fullToken.length < 50) {
                    throw new Error('Token tidak valid. Silakan logout dan login kembali.');
                }

                const url = `/v1/data/${tableName}?token=${encodeURIComponent(portalConfig.fullToken)}`;
                console.log('📡 Fetching:', window.location.origin + url);

                // Update API endpoint display with full token
                document.getElementById('apiEndpointUrl').textContent =
                    `${window.location.origin}/v1/data/${tableName}?token=${portalConfig.fullToken}`;

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    let errorData = await response.json().catch(() => ({
                        error: response.statusText
                    }));
                    throw new Error(errorData.error || `HTTP ${response.status}`);
                }

                const result = await response.json();
                console.log('✅ Data received:', result);

                renderDataTable(result);

            } catch (error) {
                console.error('💥 Error:', error);
                document.getElementById('dataViewerBody').innerHTML = `
            <div style="text-align: center; color: #ef4444; padding: 3rem;">
                <i class="ri-error-warning-line" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                <p style="margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600;">${error.message}</p>
                <button onclick="loadTableData('${tableName}')" class="hero-btn" style="padding: 0.625rem 1.5rem; border-radius: 50px; font-weight: 600;">Coba Lagi</button>
            </div>`;
            }
        }

        function renderDataTable(result) {
            const {
                data,
                meta
            } = result;

            if (!meta || !meta.columns_returned) {
                document.getElementById('dataViewerBody').innerHTML = `<div style="text-align: center; color: #ef4444; padding: 3rem;">
            <i class="ri-error-warning-line" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
            <p style="margin: 0; font-size: 1rem;">Format response API tidak valid</p>
        </div>`;
                return;
            }

            document.getElementById('tableNameDisplay').textContent = meta.table;
            const container = document.getElementById('dataViewerBody');

            if (!data || data.length === 0) {
                container.innerHTML = `<div style="text-align: center; color: #94a3b8; padding: 3rem;">
            <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
            <p style="margin: 0; font-size: 1rem;">Tidak ada data ditemukan</p>
        </div>`;
                return;
            }

            const columns = meta.columns_returned;

            let html =
                '<div class="table-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">';
            html +=
                '<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem; min-width: 800px;">';

            html +=
                '<thead><tr style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); position: sticky; top: 0; z-index: 10;">';
            columns.forEach((col, index) => {
                const width = index === 0 ? '250px' : 'auto';
                html +=
                    `<th style="padding: 1rem; text-align: left; border-bottom: 2px solid #cbd5e1; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.025em; white-space: nowrap; font-size: 0.75rem; min-width: ${width};">${col}</th>`;
            });
            html += '</tr></thead><tbody>';

            data.forEach((row, index) => {
                const bgClass = index % 2 === 0 ? '' : 'background: #f8fafc;';
                html +=
                    `<tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s; ${bgClass}" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='${index % 2 === 0 ? '' : '#f8fafc'}'">`;
                columns.forEach(col => {
                    const value = row[col] ?? '';
                    const displayValue = String(value).length > 40 ? String(value).substring(0, 40) + '…' :
                        value;
                    html +=
                        `<td style="padding: 0.875rem 1rem; color: #1e293b; vertical-align: top; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.875rem;" title="${escapeHtml(String(value))}">${escapeHtml(displayValue)}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;

            document.getElementById('paginationInfo').textContent =
                `Total ${meta.total_records} record • ${meta.table} • ${new Date(meta.timestamp).toLocaleString('id-ID')}`;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        if (!document.getElementById('portal-animations')) {
            const style = document.createElement('style');
            style.id = 'portal-animations';
            style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
            document.head.appendChild(style);
        }
    </script>
@endpush
