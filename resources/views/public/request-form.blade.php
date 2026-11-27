@extends('layouts.public')
@section('title', 'Ajukan Akses Data')

@php
    $hiddenTables = [
        'api_access_tokens',
        'branches',
        'data_access_grants',
        'data_access_logs',
        'data_access_requests',
        'financial_report_details',
        'financial_report_forwards',
        'financial_reports',
        'financial_transactions',
        'login_activities',
        'notifications',
        'pemasukans',
        'pengeluarans',
        'permissions',
        'request_verifications',
        'role_permissions',
        'roles',
        'security_token_controllers',
        'transaction_categories',
        'user_roles',
    ];

    $normalizeTable = function ($value) {
        return preg_replace('/[\s\-]+/', '_', strtolower(trim((string) $value)));
    };

    $visibleTables = collect($availableTables)->reject(function ($name, $id) use ($hiddenTables, $normalizeTable) {
        return in_array($normalizeTable($name), $hiddenTables, true) ||
            in_array($normalizeTable($id), $hiddenTables, true);
    });

    $selectedTables = old('requested_tables', []);
@endphp

@section('content')
    <style>
        .da-wrap {
            padding: 3rem 1.5rem 4rem;
        }

        .da-inner {
            max-width: 760px;
            margin: 0 auto;
            width: 100%;
        }

        .da-notice {
            display: flex;
            gap: 0.9rem;
            align-items: flex-start;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            border-radius: 16px;
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #78350f;
        }

        .da-notice i {
            font-size: 1.5rem;
            line-height: 1;
            color: #d97706;
        }

        .da-notice strong {
            display: block;
            margin-bottom: 0.15rem;
        }

        .da-notice span {
            font-size: 0.92rem;
            line-height: 1.55;
        }

        .da-flash {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            padding: 0.9rem 1.25rem;
            margin-bottom: 1.25rem;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .da-flash--success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .da-flash--error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .da-card {
            background: #fff;
            border: 1px solid var(--zaitun-border, #E4DCBF);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(52, 67, 44, 0.08);
        }

        .da-card-head {
            padding: 1.5rem 2rem;
            color: #fff;
            background: linear-gradient(135deg, var(--zaitun-deep, #34432C), var(--zaitun-primary, #526E3B));
        }

        .da-card-head h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .da-card-head p {
            margin: 0.35rem 0 0;
            font-size: 0.9rem;
            opacity: 0.85;
        }

        .da-card-body {
            padding: 2rem;
        }

        .da-section {
            padding-bottom: 1.75rem;
            margin-bottom: 1.75rem;
            border-bottom: 1px solid #eef0e8;
        }

        .da-section:last-of-type {
            border-bottom: none;
            margin-bottom: 1.25rem;
            padding-bottom: 0;
        }

        .da-section-title {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin: 0 0 1.1rem;
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--zaitun-deep, #34432C);
        }

        .da-section-title i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--zaitun-sand, #FAF7EF);
            border: 1px solid var(--zaitun-border, #E4DCBF);
            font-size: 1.1rem;
        }

        .da-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.1rem 1.25rem;
        }

        .da-field {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            min-width: 0;
        }

        .da-field--full {
            grid-column: 1 / -1;
        }

        .da-label {
            font-weight: 600;
            font-size: 0.92rem;
            color: #1f2937;
        }

        .da-label b {
            color: #ef4444;
            font-weight: 700;
        }

        .da-control {
            width: 100%;
            padding: 0.72rem 1rem;
            border-radius: 12px;
            border: 1.5px solid #dfe3d8;
            background: #fff;
            font-size: 0.97rem;
            font-family: inherit;
            color: #111827;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            box-sizing: border-box;
        }

        .da-control:focus {
            outline: none;
            border-color: var(--zaitun-primary, #526E3B);
            box-shadow: 0 0 0 4px rgba(82, 110, 59, 0.15);
        }

        textarea.da-control {
            resize: vertical;
            min-height: 120px;
            line-height: 1.55;
        }

        .da-hint {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.82rem;
            color: #6b7280;
        }

        .da-error {
            font-size: 0.82rem;
            color: #dc2626;
        }

        .da-tables-toolbar {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 0.9rem;
        }

        .da-search {
            position: relative;
            flex: 1 1 220px;
        }

        .da-search i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .da-search input {
            padding-left: 2.4rem;
        }

        .da-mini-btn {
            padding: 0.6rem 1rem;
            border-radius: 50px;
            border: 1.5px solid var(--zaitun-border, #E4DCBF);
            background: var(--zaitun-sand, #FAF7EF);
            color: var(--zaitun-deep, #34432C);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.2s ease;
        }

        .da-mini-btn:hover {
            background: #f1ecd9;
        }

        .da-counter {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
            background: #f3f4f6;
            white-space: nowrap;
        }

        .da-counter.is-active {
            background: #ecf3e4;
            color: var(--zaitun-deep, #34432C);
        }

        .da-table-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.7rem;
        }

        .da-table-option {
            position: relative;
            display: block;
            cursor: pointer;
        }

        .da-table-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .da-table-box {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-height: 52px;
            padding: 0.7rem 0.85rem;
            border-radius: 14px;
            border: 1.5px solid #dfe3d8;
            background: #fff;
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
            transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .da-table-box .da-tick {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 7px;
            border: 1.5px solid #cbd5c0;
            background: #fff;
            color: transparent;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .da-table-box .da-name {
            min-width: 0;
            overflow-wrap: anywhere;
            line-height: 1.3;
        }

        .da-table-option:hover .da-table-box {
            border-color: var(--zaitun-primary, #526E3B);
        }

        .da-table-option input:focus-visible+.da-table-box {
            box-shadow: 0 0 0 4px rgba(82, 110, 59, 0.2);
            border-color: var(--zaitun-primary, #526E3B);
        }

        .da-table-option input:checked+.da-table-box {
            border-color: var(--zaitun-primary, #526E3B);
            background: #f3f8ee;
            color: var(--zaitun-deep, #34432C);
            font-weight: 600;
        }

        .da-table-option input:checked+.da-table-box .da-tick {
            background: var(--zaitun-primary, #526E3B);
            border-color: var(--zaitun-primary, #526E3B);
            color: #fff;
        }

        .da-empty {
            display: none;
            padding: 1.25rem;
            text-align: center;
            border-radius: 14px;
            border: 1.5px dashed #dfe3d8;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .da-empty.is-visible {
            display: block;
        }

        .da-terms {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            padding: 1.1rem 1.25rem;
            border-radius: 16px;
            border: 1px solid var(--zaitun-border, #E4DCBF);
            background: var(--zaitun-sand, #FAF7EF);
            cursor: pointer;
        }

        .da-terms input {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            accent-color: var(--zaitun-primary, #526E3B);
            flex: 0 0 auto;
        }

        .da-terms strong {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.93rem;
        }

        .da-terms ul {
            margin: 0;
            padding-left: 1.15rem;
            font-size: 0.88rem;
            line-height: 1.65;
            color: #4b5563;
        }

        .da-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.75rem;
        }

        .da-actions-right {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .da-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .da-btn--primary {
            background: var(--zaitun-deep, #34432C);
            color: #fff;
            border: none;
            box-shadow: 0 8px 20px rgba(52, 67, 44, 0.25);
        }

        .da-btn--primary:hover:not(:disabled) {
            transform: translateY(-1px);
            background: var(--zaitun-primary, #526E3B);
        }

        .da-btn--primary:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }

        .da-btn--ghost {
            background: transparent;
            color: #4b5563;
            border: 1.5px solid #dfe3d8;
        }

        .da-btn--ghost:hover {
            background: #f9fafb;
        }

        .da-btn--sand {
            background: var(--zaitun-sand, #FAF7EF);
            color: var(--zaitun-deep, #34432C);
            border: 1.5px solid var(--zaitun-border, #E4DCBF);
        }

        .da-btn--sand:hover {
            background: #f1ecd9;
        }

        .da-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: da-spin 0.6s linear infinite;
        }

        @keyframes da-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 720px) {
            .da-wrap {
                padding: 2rem 1rem 3rem;
            }

            .da-card-head,
            .da-card-body {
                padding: 1.25rem;
            }

            .da-grid {
                grid-template-columns: 1fr;
            }

            .da-table-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .da-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .da-actions-right {
                flex-direction: column-reverse;
            }

            .da-btn {
                width: 100%;
            }
        }

        @media (max-width: 420px) {
            .da-table-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .da-spinner {
                animation: none;
            }

            .da-btn,
            .da-table-box,
            .da-control {
                transition: none;
            }
        }

        .da-card-head h3,
        .da-card-head h3 * {
            color: #fff !important;
        }
    </style>

    <header class="page-hero public-page-hero public-page-hero--events">
        <div class="page-hero-container fade-in">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <span>Ajukan Akses Data</span>
            </nav>
            <span class="page-kicker">Permohonan Terbatas</span>
            <h1 class="page-hero-title">Ajukan Akses Data</h1>
            <p class="page-hero-desc">Sampaikan kebutuhan data dan tujuan penggunaannya untuk ditinjau oleh tim keamanan.</p>
        </div>
        <span class="page-image-note">Foto ilustrasi · bukan dokumentasi Gereja Filadelfia</span>
    </header>

    <div class="lux-container da-wrap">
        <div class="da-inner">

            <div class="da-notice">
                <i class="ri-shield-warning-line"></i>
                <div>
                    <strong>Data Sensitif</strong>
                    <span>Permintaan ini akan ditinjau oleh tim keamanan. Pastikan tujuan penggunaan data jelas dan sesuai
                        kebijakan.</span>
                </div>
            </div>

            @if (session('success'))
                <div class="da-flash da-flash--success" role="status">
                    <i class="ri-checkbox-circle-line"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="da-flash da-flash--error" role="alert">
                    <i class="ri-error-warning-line"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="da-card">
                <div class="da-card-head">
                    <h3 style="color: #fff !important;"><i class="ri-file-list-3-line" style="color: #fff !important;"></i>
                        <span style="color: #fff !important;">Form Permintaan Akses Data</span></h3>
                    <p style="color: #fff !important;">Kolom bertanda * wajib diisi.</p>
                </div>

                <div class="da-card-body">
                    <form action="{{ route('public.data-access.store') }}" method="POST"
                        id="requestForm" novalidate>
                        @csrf
                        <input type="hidden" name="_token_access" value="{{ request('token') }}">

                        <section class="da-section">
                            <h4 class="da-section-title"><i class="ri-user-3-line"></i> Data Pemohon</h4>
                            <div class="da-grid">
                                <div class="da-field">
                                    <label class="da-label" for="requester_name">Nama Lengkap <b>*</b></label>
                                    <input type="text" id="requester_name" name="requester_name" class="da-control"
                                        value="{{ old('requester_name') }}" placeholder="Contoh: John Doe" required>
                                    @error('requester_name')
                                        <div class="da-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="da-field">
                                    <label class="da-label" for="requester_email">Email <b>*</b></label>
                                    <input type="email" id="requester_email" name="requester_email" class="da-control"
                                        value="{{ old('requester_email') }}" placeholder="contoh@email.com" required>
                                    @error('requester_email')
                                        <div class="da-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="da-section">
                            <h4 class="da-section-title"><i class="ri-window-line"></i> Informasi Proyek</h4>
                            <div class="da-grid">
                                <div class="da-field">
                                    <label class="da-label" for="project_name">Nama Proyek / Aplikasi</label>
                                    <input type="text" id="project_name" name="project_name" class="da-control"
                                        value="{{ old('project_name') }}" placeholder="Contoh: Dashboard Sales Q1 2026">
                                    @error('project_name')
                                        <div class="da-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="da-field">
                                    <label class="da-label" for="project_url">URL Proyek / Aplikasi <b>*</b></label>
                                    <input type="url" id="project_url" name="project_url" class="da-control"
                                        value="{{ old('project_url') }}" placeholder="https://contoh.com/proyek" required>
                                    <div class="da-hint"><i class="ri-information-line"></i> Boleh localhost jika belum
                                        memiliki domain.</div>
                                    @error('project_url')
                                        <div class="da-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="da-field da-field--full">
                                    <label class="da-label" for="branch_id">Cabang / Lokasi Web <b>*</b></label>
                                    <select id="branch_id" name="branch_id" class="da-control" required>
                                        <option value="" disabled {{ !old('branch_id') ? 'selected' : '' }}>Pilih
                                            cabang lokasi...</option>
                                        @foreach ($branches as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ old('branch_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="da-hint"><i class="ri-map-pin-line"></i> Cabang tempat web atau aplikasi
                                        Anda akan di-deploy.</div>
                                    @error('branch_id')
                                        <div class="da-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="da-section">
                            <h4 class="da-section-title"><i class="ri-database-2-line"></i> Tabel yang Dibutuhkan <b
                                    style="color:#ef4444;">*</b></h4>

                            <div class="da-tables-toolbar">
                                <div class="da-search">
                                    <i class="ri-search-line"></i>
                                    <input type="text" id="tableSearch" class="da-control"
                                        placeholder="Cari tabel..." autocomplete="off" aria-label="Cari tabel">
                                </div>
                                <button type="button" class="da-mini-btn" id="selectAllTables">Pilih semua</button>
                                <button type="button" class="da-mini-btn" id="clearAllTables">Hapus pilihan</button>
                                <span class="da-counter" id="tableCounter">0 dipilih</span>
                            </div>

                            <div class="da-table-grid" id="tableGrid">
                                @foreach ($visibleTables as $id => $name)
                                    <label class="da-table-option" data-name="{{ strtolower($name) }}">
                                        <input type="checkbox" name="requested_tables[]" value="{{ $id }}"
                                            {{ in_array($id, $selectedTables) ? 'checked' : '' }}>
                                        <span class="da-table-box">
                                            <span class="da-tick"><i class="ri-check-line"></i></span>
                                            <span class="da-name">{{ $name }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="da-empty {{ $visibleTables->isEmpty() ? 'is-visible' : '' }}" id="tableEmpty">
                                {{ $visibleTables->isEmpty() ? 'Belum ada tabel yang tersedia untuk diajukan.' : 'Tidak ada tabel yang cocok dengan pencarian.' }}
                            </div>

                            <div class="da-hint" style="margin-top: 0.75rem;">
                                <i class="ri-information-line"></i> Pilih minimal 1 tabel. Hanya tabel aktif yang tersedia.
                            </div>
                            <div class="da-error" id="tableError" style="display:none; margin-top: 0.35rem;">Pilih
                                minimal 1 tabel.</div>
                            @error('requested_tables')
                                <div class="da-error" style="margin-top: 0.35rem;">{{ $message }}</div>
                            @enderror
                        </section>

                        <section class="da-section">
                            <h4 class="da-section-title"><i class="ri-edit-box-line"></i> Tujuan Penggunaan</h4>
                            <div class="da-field">
                                <label class="da-label" for="purpose">Tujuan Penggunaan Data <b>*</b></label>
                                <textarea id="purpose" name="purpose" class="da-control" rows="5" minlength="50" maxlength="1000"
                                    placeholder="Jelaskan secara detail: untuk apa data digunakan, siapa yang mengakses, dan bagaimana hasilnya..."
                                    required>{{ old('purpose') }}</textarea>
                                <div style="display:flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                                    <span class="da-hint">Minimal 50 karakter.</span>
                                    <small id="charCount" style="font-size: 0.8rem; color: #6b7280;">0/1000
                                        karakter</small>
                                </div>
                                @error('purpose')
                                    <div class="da-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </section>

                        <section class="da-section">
                            <label class="da-terms">
                                <input type="checkbox" name="agreed_terms" required>
                                <span>
                                    <strong>Saya menyetujui bahwa:</strong>
                                    <ul>
                                        <li>Data hanya digunakan sesuai tujuan yang dinyatakan</li>
                                        <li>Tidak akan membagikan data ke pihak ketiga tanpa izin</li>
                                        <li>Akses akan dicatat dan diaudit secara berkala</li>
                                    </ul>
                                </span>
                            </label>
                            @error('agreed_terms')
                                <div class="da-error" style="margin-top: 0.5rem;">{{ $message }}</div>
                            @enderror
                        </section>

                        <div class="da-actions">
                            <a href="{{ route('data-portal.verify') }}" class="da-btn da-btn--sand">
                                <i class="ri-key-line"></i> Telah Memiliki Token?
                            </a>
                            <div class="da-actions-right">
                                <a href="/" class="da-btn da-btn--ghost">
                                    <i class="ri-arrow-left-line"></i> Batal
                                </a>
                                <button type="submit" class="da-btn da-btn--primary" id="submitBtn">
                                    <i class="ri-send-plane-line"></i> Ajukan Permintaan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('requestForm');
            const submitBtn = document.getElementById('submitBtn');
            const purpose = document.getElementById('purpose');
            const charCount = document.getElementById('charCount');
            const options = Array.from(document.querySelectorAll('.da-table-option'));
            const checkboxes = Array.from(document.querySelectorAll('input[name="requested_tables[]"]'));
            const counter = document.getElementById('tableCounter');
            const search = document.getElementById('tableSearch');
            const empty = document.getElementById('tableEmpty');
            const tableError = document.getElementById('tableError');
            const selectAll = document.getElementById('selectAllTables');
            const clearAll = document.getElementById('clearAllTables');
            const emptyDefault = empty ? empty.textContent.trim() : '';

            function updateCounter() {
                const total = checkboxes.filter(function(c) {
                    return c.checked;
                }).length;
                counter.textContent = total + ' dipilih';
                counter.classList.toggle('is-active', total > 0);
                if (total > 0 && tableError) {
                    tableError.style.display = 'none';
                }
            }

            function applyFilter() {
                const keyword = search.value.trim().toLowerCase();
                let shown = 0;
                options.forEach(function(option) {
                    const match = option.dataset.name.indexOf(keyword) !== -1;
                    option.style.display = match ? '' : 'none';
                    if (match) {
                        shown++;
                    }
                });
                if (options.length > 0) {
                    empty.textContent = 'Tidak ada tabel yang cocok dengan pencarian.';
                    empty.classList.toggle('is-visible', shown === 0);
                } else {
                    empty.textContent = emptyDefault;
                    empty.classList.add('is-visible');
                }
            }

            checkboxes.forEach(function(c) {
                c.addEventListener('change', updateCounter);
            });

            if (search) {
                search.addEventListener('input', applyFilter);
            }

            if (selectAll) {
                selectAll.addEventListener('click', function() {
                    options.forEach(function(option) {
                        if (option.style.display !== 'none') {
                            option.querySelector('input').checked = true;
                        }
                    });
                    updateCounter();
                });
            }

            if (clearAll) {
                clearAll.addEventListener('click', function() {
                    checkboxes.forEach(function(c) {
                        c.checked = false;
                    });
                    updateCounter();
                });
            }

            if (purpose && charCount) {
                const updateCount = function() {
                    const len = purpose.value.length;
                    charCount.textContent = len + '/1000 karakter' + (len < 50 ? ' (minimal 50)' : ' ✓');
                    charCount.style.color = len < 50 ? '#d97706' : '#16a34a';
                };
                purpose.addEventListener('input', updateCount);
                updateCount();
            }

            updateCounter();
            applyFilter();

            if (form) {
                form.addEventListener('submit', function(event) {
                    const hasTable = checkboxes.some(function(c) {
                        return c.checked;
                    });

                    if (!hasTable) {
                        event.preventDefault();
                        tableError.style.display = 'block';
                        document.getElementById('tableGrid').scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return;
                    }

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="da-spinner"></span> Mengirim...';
                });
            }
        });
    </script>
@endpush
