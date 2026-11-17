@extends('layouts.admin')

@section('title', 'Transaksi Keuangan')

@section('content')
    <div class="page-wrapper">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Transaksi Keuangan</h1>
                <p class="text-muted-custom mb-0">
                    <i class="ri-building-line me-1"></i> {{ auth()->user()->roles->first()?->branch->name ?? '' }}
                </p>
            </div>
            @if (auth()->user()->hasPermission('finance.transaction.create'))
                <button type="button" class="btn btn-custom" onclick="openTransactionModal()">
                    <i class="ri-add-line me-1"></i> Tambah Transaksi
                </button>
            @endif
        </div>

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Tipe Transaksi</label>
                        <select id="filterType" class="form-select form-select-custom" onchange="loadTransactions()">
                            <option value="">Semua Tipe</option>
                            <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Pengeluaran
                            </option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                        <select id="filterStatus" class="form-select form-select-custom" onchange="loadTransactions()">
                            <option value="">Semua Status</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Dari Tanggal</label>
                        <input type="date" id="filterStartDate" class="form-control form-control-custom"
                            value="{{ request('start_date') }}" onchange="loadTransactions()">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Sampai Tanggal</label>
                        <input type="date" id="filterEndDate" class="form-control form-control-custom"
                            value="{{ request('end_date') }}" onchange="loadTransactions()">
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Kategori</label>
                        <select id="filterCategory" class="form-select form-select-custom" onchange="loadTransactions()">
                            <option value="">Semua Kategori</option>
                            @foreach ($groups as $gKey => $g)
                                <optgroup label="{{ $g['title'] }}">
                                    @foreach ($g['cats'] as $catCode)
                                        @php
                                            $catName =
                                                $categories['income'][$catCode]['name'] ??
                                                ($categories['expense'][$gKey][$catCode]['name'] ?? $catCode);
                                        @endphp
                                        <option value="{{ $catCode }}"
                                            {{ request('category') === $catCode ? 'selected' : '' }}>
                                            {{ $catName }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="button" class="btn btn-outline-custom w-100" onclick="resetFilters()">
                            <i class="ri-refresh-line me-1"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-custom">
            {{-- DataTable Toolbar --}}
            <div class="dt-toolbar">
                <div class="dt-entries">
                    <label>Tampilkan</label>
                    <select onchange="var p=new URLSearchParams(window.location.search);p.set('per_page',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString()">
                        @foreach([10,25,50,100] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 10) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    <label>entri</label>
                </div>
                <div class="dt-search">
                    <div class="dt-search-wrap">
                        <i class="ri-search-line"></i>
                        <input type="text" id="dtSearchTx" placeholder="Cari transaksi..." value="{{ request('search') }}"
                            onkeydown="if(event.key==='Enter'){var p=new URLSearchParams(window.location.search);p.set('search',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString();}">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tanggal</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold" style="min-width:240px">Deskripsi</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kategori</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end" style="min-width:150px">Jumlah</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4 date-cell">
                                    <span class="fw-medium">{{ $tx->transaction_date?->format('d M Y') }}</span>
                                </td>
                                <td class="py-3 px-4 wrap-cell">
                                    <div class="fw-semibold">{{ $tx->description }}</div>
                                    @if ($tx->reference_number)
                                        <small class="text-muted-custom">Ref: {{ $tx->reference_number }}</small>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-secondary-custom">{{ $tx->category_name }}</span>
                                </td>
                                <td class="py-3 px-4 amount-cell text-end">
                                    <span class="fw-bold {{ $tx->type === 'income' ? 'text-success' : 'text-danger' }}" style="white-space:nowrap">
                                        Rp&nbsp;{{ number_format($tx->amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="badge {{ $tx->type === 'income' ? 'badge-type-income' : 'badge-type-expense' }}">
                                        {{ $tx->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="badge {{ $tx->approved_at ? 'badge-status-approved' : 'badge-status-pending' }}">
                                        {{ $tx->approved_at ? 'Disetujui' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if (!$tx->approved_at)
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" data-bs-display="static"
                                                    aria-expanded="false">
                                                    Aksi
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @if (auth()->user()->hasPermission('finance.transaction.edit'))
                                                        <li>
                                                            <button class="dropdown-item"
                                                                onclick="editTransaction('{{ $tx->id }}')">
                                                                <i class="ri-pencil-line me-2"></i> Edit
                                                            </button>
                                                        </li>
                                                    @endif

                                                    @if (auth()->user()->hasPermission('finance.transaction.view'))
                                                        <li>
                                                            <button class="dropdown-item"
                                                                onclick="showTransaction('{{ $tx->id }}')">
                                                                <i class="ri-eye-line me-2"></i> Lihat
                                                            </button>
                                                        </li>
                                                    @endif

                                                    @if (auth()->user()->hasPermission('finance.transaction.approve'))
                                                        <li>
                                                            <button class="dropdown-item text-success"
                                                                onclick="approveTransaction('{{ $tx->id }}')">
                                                                <i class="ri-check-line me-2"></i> Setujui
                                                            </button>
                                                        </li>
                                                    @endif

                                                    @if (auth()->user()->hasPermission('finance.transaction.delete'))
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item text-danger"
                                                                onclick="deleteTransaction('{{ $tx->id }}')">
                                                                <i class="ri-delete-bin-line me-2"></i> Hapus
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-exchange-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Tidak ada transaksi</h6>
                                        <p class="text-muted-custom small mb-0">Mulai tambahkan transaksi keuangan pertama
                                            Anda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($transactions->hasPages())
                <div class="dt-footer">
                    <span class="dt-info">
                        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }} data
                    </span>
                    <div>
                        {{ $transactions->withQueryString()->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="dt-footer">
                    <span class="dt-info">Menampilkan {{ $transactions->count() }} data</span>
                </div>
            @endif
        </div>

    </div>

    <!-- Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <form id="transactionForm">
                    <input type="hidden" id="transactionId">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTitle">Tambah Transaksi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">Tanggal <span
                                        class="text-danger">*</span></label>
                                <input type="date" id="txDate" name="transaction_date"
                                    class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">No. Referensi</label>
                                <input type="text" id="txRef" name="reference_number"
                                    class="form-control form-control-custom" maxlength="50">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="txDesc" name="description"
                                class="form-control form-control-custom" required maxlength="255">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted-custom">Tipe <span
                                        class="text-danger">*</span></label>
                                <select id="txType" name="type" class="form-select form-select-custom" required
                                    onchange="loadCategoriesByType()">
                                    <option value="income">Pemasukan</option>
                                    <option value="expense">Pengeluaran</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted-custom">Kategori <span
                                        class="text-danger">*</span></label>
                                <select id="txCategory" name="category_code" class="form-select form-select-custom"
                                    required>
                                    <option value="">Pilih Kategori</option>
                                </select>
                                <input type="hidden" id="txCategoryName" name="category_name">
                                <input type="hidden" id="txCategoryGroup" name="category_group">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted-custom">Jumlah (Rp) <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="txAmount" name="amount"
                                    class="form-control form-control-custom text-end" required
                                    oninput="formatCurrency(this)">
                            </div>
                        </div>

                        <hr class="border-custom my-4">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">Metode Pembayaran</label>
                                <input type="text" id="txPayment" name="payment_method"
                                    class="form-control form-control-custom" maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">Nama Penerima</label>
                                <input type="text" id="txRecipient" name="recipient_name"
                                    class="form-control form-control-custom" maxlength="255">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-semibold text-muted-custom">Catatan</label>
                            <textarea id="txNotes" name="notes" class="form-control form-control-custom textarea-custom" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitTransaction">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
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

        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
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

        .form-control-custom.text-end {
            text-align: right !important;
        }

        .textarea-custom {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        /* ===== BADGES ===== */
        .badge-type-income {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-type-expense {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-approved {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-pending {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-secondary-custom {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.25em 0.5em;
            font-size: 0.75rem;
            font-weight: 500;
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

        .btn-action-primary {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-primary:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-success {
            border-color: #22c55e;
            color: #22c55e;
        }

        .btn-action-success:hover {
            background: #22c55e;
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

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 900px;
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
            // ===== MODAL INSTANCE =====
            let txModal;
            const modalEl = document.getElementById('transactionModal');
            if (modalEl && bootstrap.Modal) {
                txModal = new bootstrap.Modal(modalEl);
            }

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== CATEGORY DATA =====
            const categories = @json($categories);

            // ===== CURRENCY FORMATTER =====
            const fmt = n => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(n);

            function formatCurrency(el) {
                const v = el.value.replace(/\D/g, '');
                el.value = v ? fmt(v) : '';
            }

            // ===== LOAD CATEGORIES BY TYPE =====
            function loadCategoriesByType(selectedCode = '') {
                const type = document.getElementById('txType').value;
                const sel = document.getElementById('txCategory');
                const data = categories[type === 'income' ? 'income' : 'expense'];

                sel.innerHTML = '<option value="">Pilih Kategori</option>';

                Object.entries(data).forEach(([grpName, cats]) => {
                    const opt = document.createElement('optgroup');
                    opt.label = grpName.toUpperCase();
                    cats.forEach(c => {
                        const o = document.createElement('option');
                        o.value = c.code;
                        o.textContent = c.name;
                        o.dataset.name = c.name;
                        o.dataset.group = grpName;
                        if (c.code === selectedCode) o.selected = true;
                        opt.appendChild(o);
                    });
                    sel.appendChild(opt);
                });
            }

            // ===== FILTER FUNCTIONS =====
            window.loadTransactions = function() {
                const params = new URLSearchParams();
                const t = document.getElementById('filterType')?.value;
                const s = document.getElementById('filterStatus')?.value;
                const sd = document.getElementById('filterStartDate')?.value;
                const ed = document.getElementById('filterEndDate')?.value;
                const c = document.getElementById('filterCategory')?.value;

                if (t) params.append('type', t);
                if (s) params.append('status', s);
                if (sd) params.append('start_date', sd);
                if (ed) params.append('end_date', ed);
                if (c) params.append('category', c);

                window.location.href = '{{ route('admin.finance.transactions.index') }}?' + params.toString();
            }

            window.resetFilters = function() {
                window.location.href = '{{ route('admin.finance.transactions.index') }}';
            }

            // ===== MODAL FUNCTIONS =====
            window.openTransactionModal = function(tx = null) {
                document.getElementById('modalTitle').textContent = tx ? 'Edit Transaksi' : 'Tambah Transaksi';
                document.getElementById('transactionId').value = tx?.id || '';
                document.getElementById('txDate').value = tx?.transaction_date?.split('T')[0] || '';
                document.getElementById('txRef').value = tx?.reference_number || '';
                document.getElementById('txDesc').value = tx?.description || '';
                document.getElementById('txType').value = tx?.type || 'income';
                document.getElementById('txAmount').value = tx?.amount ? fmt(tx.amount) : '';
                document.getElementById('txPayment').value = tx?.payment_method || '';
                document.getElementById('txRecipient').value = tx?.recipient_name || '';
                document.getElementById('txNotes').value = tx?.notes || '';

                loadCategoriesByType(tx?.category_code || '');

                if (tx) {
                    document.getElementById('txCategoryName').value = tx.category_name;
                    document.getElementById('txCategoryGroup').value = tx.category_group;
                }

                if (txModal) txModal.show();
            }

            window.closeModal = function() {
                if (txModal) txModal.hide();
                document.getElementById('transactionForm').reset();
            }

            // ===== CATEGORY CHANGE HANDLER =====
            document.getElementById('txCategory')?.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                document.getElementById('txCategoryName').value = opt.dataset.name || '';
                document.getElementById('txCategoryGroup').value = opt.dataset.group || '';
            });

            // ===== FORM SUBMIT =====
            const txForm = document.getElementById('transactionForm');
            const btnSubmit = document.getElementById('btnSubmitTransaction');

            txForm?.addEventListener('submit', function(e) {
                e.preventDefault();

                const id = document.getElementById('transactionId').value;

                let url, method;
                if (id) {
                    url = `/admin/finance/transactions/${id}`;
                    method = 'PUT';
                } else {
                    url = "{{ route('admin.finance.transactions.store') }}";
                    method = 'POST';
                }

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                const data = {
                    transaction_date: document.getElementById('txDate').value,
                    reference_number: document.getElementById('txRef').value,
                    description: document.getElementById('txDesc').value,
                    type: document.getElementById('txType').value,
                    category_code: document.getElementById('txCategory').value,
                    category_name: document.getElementById('txCategoryName').value,
                    category_group: document.getElementById('txCategoryGroup').value,
                    amount: document.getElementById('txAmount').value.replace(/\D/g, ''),
                    payment_method: document.getElementById('txPayment').value,
                    recipient_name: document.getElementById('txRecipient').value,
                    notes: document.getElementById('txNotes').value,
                    _token: document.querySelector('meta[name="csrf-token"]')?.content || ''
                };

                fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': data._token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(r => {
                        if (!r.ok) {
                            throw new Error(`HTTP error! status: ${r.status}`);
                        }
                        return r.json();
                    })
                    .then(json => {
                        if (json.success) {
                            showToast('success', 'Berhasil', json.message);
                            if (txModal) txModal.hide();
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast('error', 'Gagal', json.message || 'Validasi gagal');
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                        showToast('error', 'Error',
                            'Terjadi kesalahan saat menyimpan. Cek console untuk detail.');
                    })
                    .finally(() => {
                        if (btnSubmit) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="ri-save-line me-1"></i> Simpan';
                        }
                    });
            });

            // ===== EDIT TRANSACTION =====
            window.editTransaction = function(id) {
                fetch(`/admin/finance/transactions/${id}/edit`)
                    .then(r => r.json())
                    .then(tx => {
                        if (tx.success) openTransactionModal(tx.data);
                        else showToast('error', 'Error', 'Gagal memuat data transaksi');
                    })
                    .catch(() => showToast('error', 'Error', 'Terjadi kesalahan jaringan'));
            }

            window.showTransaction = function(id) {
                fetch(`/admin/finance/transactions/show/${id}`)
                    .then(r => r.json())
                    .then(tx => {
                        if (tx.success) openTransactionModal(tx.data);
                        else showToast('error', 'Error', 'Gagal memuat data transaksi');
                    })
                    .catch(() => showToast('error', 'Error', 'Terjadi kesalahan jaringan'));
            }

            // ===== DELETE TRANSACTION =====
            window.deleteTransaction = function(id) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus transaksi?',
                        text: 'Transaksi ini akan dihapus permanen.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) performDelete(id);
                    });
                } else {
                    if (confirm('Hapus transaksi ini?')) performDelete(id);
                }
            }

            function performDelete(id) {
                fetch(`/admin/finance/transactions/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('success', 'Terhapus', res.message);
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast('error', 'Gagal', res.message);
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Gagal menghapus transaksi'));
            }

            // ===== APPROVE TRANSACTION =====
            window.approveTransaction = function(id) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Setujui transaksi?',
                        text: 'Transaksi ini akan ditandai sebagai disetujui.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#22c55e',
                        confirmButtonText: 'Ya, Setujui',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) performApprove(id);
                    });
                } else {
                    if (confirm('Setujui transaksi ini?')) performApprove(id);
                }
            }

            function performApprove(id) {
                fetch(`/admin/finance/transactions/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('success', 'Disetujui', res.message);
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast('error', 'Gagal', res.message);
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Gagal menyetujui transaksi'));
            }

            // ===== TOAST NOTIFICATION =====
            function showToast(type, title, message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type,
                        title: title,
                        text: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    alert(`${title}: ${message}`);
                }
            }

            // ===== INIT =====
            loadCategoriesByType();
        });
    </script>
@endsection
