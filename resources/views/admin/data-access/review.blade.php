@extends('layouts.admin')
@section('title', 'Review Data Access Request')

@section('content')
    <div class="lux-container" style="padding: 2rem;">
        <div class="lux-card mb-4" style="border-left: 4px solid var(--brand-primary, #4f46e5);">
            <div style="padding: 1rem 1.5rem; display: flex; gap: 0.75rem; align-items: flex-start;">
                <i class="ri-shield-warning-line" style="font-size: 1.5rem; color: var(--brand-primary, #4f46e5);"></i>
                <div>
                    <strong style="display: block; margin-bottom: 0.25rem;">Penting</strong>
                    <span style="opacity: 0.8;">Token akses hanya akan ditampilkan SEKALI setelah approval. Pastikan
                        requester menyimpan token dengan aman.</span>
                </div>
            </div>
        </div>

        <div id="alertContainer" class="mb-4"></div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                {{-- Requester Info --}}
                <div class="lux-card mb-4">
                    <div class="lux-card-header"
                        style="background: var(--brand-primary, #4f46e5); color: white; padding: 1rem 1.5rem;">
                        <h5 class="mb-0" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-user-line"></i> Requester Information
                        </h5>
                    </div>
                    <div class="lux-card-body" style="padding: 1.5rem;">
                        <div style="margin-bottom: 1rem;">
                            <label
                                style="display: block; font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.25rem;">Name</label>
                            <div style="font-weight: 600;">{{ $request->requester_name }}</div>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label
                                style="display: block; font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.25rem;">Email</label>
                            <div style="font-weight: 600;">{{ $request->requester_email }}</div>
                            @if ($request->isVerified())
                                <span class="lux-badge lux-badge-success"
                                    style="margin-top: 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="ri-check-line"></i> Verified
                                </span>
                            @else
                                <span class="lux-badge lux-badge-warning"
                                    style="margin-top: 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="ri-error-warning-line"></i> Not Verified
                                </span>
                            @endif
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.25rem;">IP
                                Address</label>
                            <div style="font-family: monospace; font-size: 0.9rem;">{{ $request->ip_address }}</div>
                        </div>
                        <div>
                            <label
                                style="display: block; font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.25rem;">Submitted</label>
                            <div>{{ $request->created_at->format('d M Y, H:i') }}</div>
                            <small style="opacity: 0.7;">{{ $request->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>

                {{-- Project Details --}}
                <div class="lux-card mb-4">
                    <div class="lux-card-header"
                        style="background: var(--success, #22c55e); color: white; padding: 1rem 1.5rem;">
                        <h5 class="mb-0" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-projector-line"></i> Project Details
                        </h5>
                    </div>
                    <div class="lux-card-body" style="padding: 1.5rem;">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.25rem;">Project
                                Name</label>
                            <div style="font-weight: 600;">{{ $request->project_name ?: 'Not specified' }}</div>
                        </div>
                        <div>
                            <label
                                style="display: block; font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.25rem;">Purpose</label>
                            <div style="background: var(--brand-light, #f8fafc); padding: 1rem; border-radius: 8px;">
                                {{ $request->purpose }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                {{-- Form Container --}}
                <div class="lux-card">
                    <div class="lux-card-header"
                        style="background: var(--warning, #f59e0b); color: white; padding: 1rem 1.5rem;">
                        <h5 class="mb-0" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-building-line"></i> Konfigurasi Akses
                        </h5>
                    </div>
                    <div class="lux-card-body" style="padding: 1.5rem;">

                        {{-- Branch Selection --}}
                        <div style="margin-bottom: 2rem;">
                            <label class="lux-label"
                                style="font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-map-pin-line" style="color: var(--danger, #ef4444);"></i>
                                Branch untuk Filter Data <span style="color: var(--danger, #ef4444);">*</span>
                            </label>
                            <select id="branchSelect" class="lux-select"
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 8px; border: 2px solid #e2e8f0;">
                                <option value="">-- Pilih Branch --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="display: block; margin-top: 0.5rem; opacity: 0.7;">
                                <i class="ri-information-line"></i> Semua data yang diakses akan difilter berdasarkan branch
                                ini (row-level security)
                            </small>
                        </div>

                        {{-- Tables & Columns Selection --}}
                        <div style="margin-bottom: 2rem;">
                            <label class="lux-label"
                                style="font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-database-line"></i> Pilih Tabel & Kolom
                            </label>

                            <input type="text" id="tableSearch" class="lux-input" placeholder="🔍 Cari tabel..."
                                style="max-width: 400px; margin-bottom: 1rem;">

                            <div id="tablesContainer">
                                @php $tableIndex = 0; @endphp
                                @php $tableIndex = 0; @endphp
                                @foreach ($allTables as $tableName => $tableData)
                                    @if (in_array($tableName, $request->requested_tables))
                                        <div class="lux-card mb-3" data-table-name="{{ $tableName }}"
                                            style="border: 1px solid #e2e8f0; border-radius: 12px;">
                                            <div
                                                style="padding: 1rem 1.5rem; background: var(--brand-light, #f8fafc); border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                                                <label
                                                    style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-weight: 600;">
                                                    <input type="checkbox" class="table-selector"
                                                        data-index="{{ $tableIndex }}"
                                                        style="width: 18px; height: 18px; accent-color: var(--brand-primary, #4f46e5);">
                                                    <i class="ri-table-line"
                                                        style="color: var(--brand-primary, #4f46e5);"></i>
                                                    {{ $tableName }}
                                                    <span class="lux-badge lux-badge-secondary"
                                                        style="margin-left: 0.5rem;">{{ count($tableData['columns']) }}
                                                        kolom</span>
                                                </label>
                                            </div>

                                            <div id="columns_{{ $tableIndex }}"
                                                style="padding: 1rem 1.5rem; display: none;">
                                                <input type="hidden" name="grants[{{ $tableIndex }}][table_name]"
                                                    class="grant-table-name" value="{{ $tableName }}">

                                                <div
                                                    style="margin-bottom: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                                    <i class="ri-checkbox-circle-line"
                                                        style="color: var(--success, #22c55e);"></i>
                                                    Centang kolom yang boleh diakses:
                                                </div>

                                                <div
                                                    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem;">
                                                    @foreach ($tableData['columns'] as $column)
                                                        <label
                                                            style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; cursor: pointer;">
                                                            <input type="checkbox" class="column-checkbox"
                                                                name="grants[{{ $tableIndex }}][allowed_columns][]"
                                                                value="{{ $column }}"
                                                                data-index="{{ $tableIndex }}"
                                                                style="width: 16px; height: 16px; accent-color: var(--brand-primary, #4f46e5);">
                                                            <code style="font-size: 0.9rem;">{{ $column }}</code>
                                                        </label>
                                                    @endforeach
                                                </div>

                                                <div style="margin-top: 1rem; font-size: 0.85rem; opacity: 0.7;">
                                                    <i class="ri-information-line"></i>
                                                    <span class="selected-count"
                                                        data-index="{{ $tableIndex }}">0</span> kolom dipilih
                                                </div>
                                            </div>
                                        </div>
                                        @php $tableIndex++; @endphp
                                    @endif
                                @endforeach
                            </div>

                            <small style="display: block; margin-top: 1rem; opacity: 0.7;">
                                <i class="ri-shield-line"></i> Kolom sensitif (password, token, secret, key) sudah otomatis
                                disembunyikan.
                            </small>
                        </div>

                        {{-- Token Configuration --}}
                        <div style="margin-bottom: 2rem;">
                            <label class="lux-label"
                                style="font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-key-line"></i> Konfigurasi Token
                            </label>

                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                                <div>
                                    <label class="lux-label"
                                        style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="ri-time-line"></i> Token Expired
                                    </label>
                                    <input type="datetime-local" id="expiresAt" class="lux-input"
                                        value="{{ now()->addDays(30)->format('Y-m-d\TH:i') }}"
                                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 2px solid #e2e8f0;">
                                    <small style="display: block; margin-top: 0.25rem; opacity: 0.7;">Kosongkan jika tidak
                                        ada batas waktu</small>
                                </div>
                                <div>
                                    <label class="lux-label"
                                        style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="ri-global-line"></i> IP Whitelist (Opsional)
                                    </label>
                                    <input type="text" id="allowedIps" class="lux-input"
                                        placeholder="192.168.1.1, 10.0.0.0/24"
                                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 2px solid #e2e8f0;">
                                    <small style="display: block; margin-top: 0.25rem; opacity: 0.7;">Pisahkan dengan koma
                                        atau kosongkan untuk semua IP</small>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div style="display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
                            <button type="button" class="lux-btn lux-btn-outline" id="btnReject"
                                style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-close-line"></i> Reject Request
                            </button>
                            <button type="button" class="lux-btn lux-btn-primary" id="btnApprove"
                                style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; background: linear-gradient(135deg, var(--brand-primary, #4f46e5), var(--brand-secondary, #06b6d4)); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-check-line"></i> Approve & Generate Tokens
                            </button>
                            <a href="{{ route('admin.data-access.requests') }}" class="lux-btn lux-btn-outline"
                                style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-arrow-left-line"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content lux-card">
                <form id="rejectForm">
                    @csrf
                    <input type="hidden" name="_method" value="POST">
                    <div class="modal-header lux-modal-header" style="background: var(--danger, #ef4444); color: white;">
                        <h5 class="modal-title lux-modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-error-warning-line"></i> Reject Request
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div style="margin-bottom: 1rem;">
                            <label class="lux-label" style="font-weight: 600; margin-bottom: 0.5rem;">Alasan Penolakan
                                (Wajib)</label>
                            <textarea id="rejectionReason" class="lux-input" rows="4"
                                placeholder="Jelaskan mengapa permintaan ini ditolak..." required
                                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 2px solid #e2e8f0; resize: vertical;"></textarea>
                            <small style="display: block; margin-top: 0.25rem; opacity: 0.7;">Minimal 10 karakter</small>
                        </div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="lux-btn lux-btn-danger" id="btnConfirmReject" disabled
                            style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600;">
                            <i class="ri-close-line"></i> Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hidden Inputs for URLs --}}
    <input type="hidden" id="baseUrlApprove"
        value="{{ route('admin.data-access.requests.approve', ['dataRequest' => '__ID__']) }}">
    <input type="hidden" id="baseUrlReject"
        value="{{ route('admin.data-access.requests.reject', ['dataRequest' => '__ID__']) }}">
    <input type="hidden" id="requestId" value="{{ $request->id }}">
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ✅ Config
            const requestId = document.getElementById('requestId')?.value;
            const currentToken =
                '{{ request()->attributes->get('page_token') ?? request()->query('_token_nav') }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const baseUrlApprove = document.getElementById('baseUrlApprove')?.value.replace('__ID__', requestId);
            const baseUrlReject = document.getElementById('baseUrlReject')?.value.replace('__ID__', requestId);

            const modalReject = document.getElementById('rejectModal') ? new bootstrap.Modal(document
                .getElementById('rejectModal')) : null;
            const rejectForm = document.getElementById('rejectForm');
            const rejectionReason = document.getElementById('rejectionReason');
            const btnConfirmReject = document.getElementById('btnConfirmReject');

            // ✅ Toggle columns visibility - PERBAIKAN SELECTOR: .lux-card bukan .table-card
            document.querySelectorAll('.table-selector').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const index = this.dataset.index;
                    const columnsDiv = document.getElementById('columns_' + index);
                    if (!columnsDiv) return;

                    const columnCheckboxes = columnsDiv.querySelectorAll('.column-checkbox');

                    columnsDiv.style.display = this.checked ? 'block' : 'none';

                    columnCheckboxes.forEach(cb => {
                        cb.disabled = !this.checked;
                        if (!this.checked) cb.checked = false;
                    });

                    updateCount(index);
                });
            });

            // ✅ Column checkbox counter
            document.querySelectorAll('.column-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const index = this.dataset.index;
                    updateCount(index);
                });
            });

            function updateCount(index) {
                const checked = document.querySelectorAll(`#columns_${index} .column-checkbox:checked`).length;
                const counter = document.querySelector(`.selected-count[data-index="${index}"]`);
                if (counter) counter.textContent = checked;
            }

            // ✅ Table search filter - PERBAIKAN SELECTOR
            document.getElementById('tableSearch')?.addEventListener('input', function(e) {
                const search = e.target.value.toLowerCase();
                // ✅ Ganti .table-card menjadi [data-table-name]
                document.querySelectorAll('[data-table-name]').forEach(card => {
                    const name = card.dataset.tableName.toLowerCase();
                    card.style.display = name.includes(search) ? 'block' : 'none';
                });
            });

            // ✅ Reject modal: enable button when reason filled
            rejectionReason?.addEventListener('input', function() {
                if (btnConfirmReject) {
                    btnConfirmReject.disabled = this.value.trim().length < 10;
                }
            });

            // ✅ Reject button click
            document.getElementById('btnReject')?.addEventListener('click', function() {
                if (modalReject) modalReject.show();
            });

            // ✅ Reject form submit via AJAX
            rejectForm?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!rejectionReason || rejectionReason.value.trim().length < 10) {
                    showAlert('error', 'Alasan penolakan minimal 10 karakter');
                    return;
                }

                if (btnConfirmReject) {
                    btnConfirmReject.disabled = true;
                    btnConfirmReject.innerHTML =
                        '<span style="width:16px;height:16px;border:2px solid white;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;"></span> Memproses...';
                }

                const url = baseUrlReject + (baseUrlReject.includes('?') ? '&' : '?') + '_token_nav=' +
                    currentToken;

                try {
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('rejection_reason', rejectionReason.value.trim());

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
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
                        if (modalReject) modalReject.hide();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', data.message || 'Gagal menolak permintaan');
                        if (btnConfirmReject) {
                            btnConfirmReject.disabled = false;
                            btnConfirmReject.innerHTML = '<i class="ri-close-line"></i> Reject Request';
                        }
                    }
                } catch (error) {
                    console.error('Reject error:', error);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                    if (btnConfirmReject) {
                        btnConfirmReject.disabled = false;
                        btnConfirmReject.innerHTML = '<i class="ri-close-line"></i> Reject Request';
                    }
                }
            });

            // ✅ Approve button click - PERBAIKAN UTAMA DI SINI
            document.getElementById('btnApprove')?.addEventListener('click', async function() {
                // Validate branch
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect?.value;

                if (!branchId) {
                    showAlert('error', 'Pilih branch terlebih dahulu!');
                    if (branchSelect) branchSelect.focus();
                    return;
                }

                // Validate at least 1 table selected - PERBAIKAN SELECTOR
                const selectedTables = document.querySelectorAll('.table-selector:checked');
                if (selectedTables.length === 0) {
                    showAlert('error', 'Pilih minimal 1 tabel!');
                    return;
                }

                // Validate at least 1 column per selected table - PERBAIKAN SELECTOR
                let valid = true;
                selectedTables.forEach(checkbox => {
                    const index = checkbox.dataset.index;
                    const columnsDiv = document.getElementById('columns_' + index);
                    if (!columnsDiv) return;

                    const columns = columnsDiv.querySelectorAll('.column-checkbox:checked');
                    if (columns.length === 0) {
                        valid = false;
                        // ✅ PERBAIKAN: Gunakan [data-table-name] bukan .table-card
                        const tableName = checkbox.closest('[data-table-name]')?.dataset
                            .tableName || 'unknown';
                        showAlert('error', `Pilih minimal 1 kolom untuk tabel: ${tableName}`);
                        checkbox.closest('[data-table-name]')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                });

                if (!valid) return;

                // Prepare approve data
                const btnApprove = document.getElementById('btnApprove');
                if (btnApprove) {
                    btnApprove.disabled = true;
                    btnApprove.innerHTML =
                        '<span style="width:16px;height:16px;border:2px solid white;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;"></span> Memproses...';
                }

                const url = baseUrlApprove + (baseUrlApprove.includes('?') ? '&' : '?') +
                    '_token_nav=' + currentToken;

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('branch_id', branchId);

                // Build grants array - PERBAIKAN SELECTOR
                selectedTables.forEach(checkbox => {
                    const index = checkbox.dataset.index;
                    // ✅ PERBAIKAN: Cari hidden input dengan selector yang benar
                    const tableCard = checkbox.closest('[data-table-name]');
                    if (!tableCard) return;

                    const tableNameInput = tableCard.querySelector('.grant-table-name');
                    const tableName = tableNameInput?.value || tableCard.dataset.tableName;

                    formData.append(`grants[${index}][table_name]`, tableName);

                    // Allowed columns
                    const columnsDiv = document.getElementById('columns_' + index);
                    if (columnsDiv) {
                        columnsDiv.querySelectorAll('.column-checkbox:checked').forEach(
                            colCb => {
                                formData.append(`grants[${index}][allowed_columns][]`, colCb
                                    .value);
                            });
                    }

                    // Rate limit
                    formData.append(`grants[${index}][rate_limit_per_minute]`, '60');
                });

                // Token config
                const expiresAt = document.getElementById('expiresAt')?.value;
                if (expiresAt) formData.append('expires_at', expiresAt);

                const allowedIps = document.getElementById('allowedIps')?.value;
                if (allowedIps) {
                    allowedIps.split(',').map(ip => ip.trim()).filter(ip => ip).forEach(ip => {
                        formData.append('allowed_ips[]', ip);
                    });
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
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
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', data.message || 'Gagal menyetujui permintaan');
                        if (btnApprove) {
                            btnApprove.disabled = false;
                            btnApprove.innerHTML =
                                '<i class="ri-check-line"></i> Approve & Generate Tokens';
                        }
                    }
                } catch (error) {
                    console.error('Approve error:', error);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                    if (btnApprove) {
                        btnApprove.disabled = false;
                        btnApprove.innerHTML =
                            '<i class="ri-check-line"></i> Approve & Generate Tokens';
                    }
                }
            });

            // ✅ Alert helper
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) {
                    alert(`${type.toUpperCase()}: ${message}`);
                    return;
                }

                const colors = {
                    success: {
                        bg: '#f0fdf4',
                        border: '#22c55e',
                        color: '#166534',
                        icon: 'ri-checkbox-circle-line'
                    },
                    error: {
                        bg: '#fef2f2',
                        border: '#ef4444',
                        color: '#991b1b',
                        icon: 'ri-error-warning-line'
                    },
                    warning: {
                        bg: '#fffbeb',
                        border: '#f59e0b',
                        color: '#92400e',
                        icon: 'ri-alert-line'
                    }
                };
                const c = colors[type] || colors.error;

                container.innerHTML = `
            <div class="lux-card" style="border-left:4px solid ${c.border}; background:${c.bg}; display:flex; align-items:center; gap:10px; padding:12px; animation: slideIn 0.3s ease;">
                <i class="${c.icon}" style="color:${c.border}; font-size:1.5rem;"></i>
                <span style="color:${c.color}; font-weight:600;">${message}</span>
            </div>
        `;

                setTimeout(() => {
                    if (container.firstElementChild) {
                        container.firstElementChild.style.animation = 'slideOut 0.3s ease';
                        setTimeout(() => container.firstElementChild?.remove(), 300);
                    }
                }, 5000);
            }

            // ✅ Add animations if not exist
            if (!document.getElementById('lux-approve-animations')) {
                const style = document.createElement('style');
                style.id = 'lux-approve-animations';
                style.textContent = `
            @keyframes spin { to { transform: rotate(360deg); } }
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        `;
                document.head.appendChild(style);
            }
        });
    </script>
@endpush
