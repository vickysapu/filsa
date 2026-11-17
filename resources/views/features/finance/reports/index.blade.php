@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('content')
    @php
        $myBranchId = auth()->user()?->roles?->first()?->branch_id;
        $activeTab = request('tab') === 'received' ? 'received' : 'sent';
        $statusColors = [
            'draft' => 'warning',
            'submitted' => 'info',
            'reviewed' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
        $can = fn($p) => auth()->user()->hasPermission($p);
        $otherBranches = $branches->filter(fn($b) => (string) $b->id !== (string) $myBranchId);
    @endphp

    <div class="container-fluid py-4">
        {{-- Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Laporan Keuangan</h1>
                <p class="text-muted-custom mb-0">
                    <i class="ri-building-line me-1"></i>
                    {{ auth()->user()->roles->first()?->branch->name ?? 'Majelis Pusat' }}
                </p>
            </div>
    <div class="d-flex gap-2 flex-wrap">
        @if ($can('finance.report.create'))
            <button type="button" class="btn btn-primary" data-act="create">
                <i class="ri-add-line me-1"></i> Buat Laporan
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="ri-file-excel-2-line me-1"></i> Import Excel
            </button>
        @endif
        @if ($can('finance.report.edit'))
            <a href="{{ route('admin.finance.reports.category-mapping') }}" class="btn btn-outline-warning">
                <i class="ri-exchange-line me-1"></i> Mapping Kategori
            </a>
        @endif

        @include('admin.finance.partials.export-dropdown')
    </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $activeTab === 'sent' ? 'active' : '' }}" data-bs-toggle="tab"
                    data-bs-target="#paneSent" data-tab="sent">
                    <i class="ri-send-plane-line me-1"></i> Laporan Saya
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $activeTab === 'received' ? 'active' : '' }}" data-bs-toggle="tab"
                    data-bs-target="#paneReceived" data-tab="received">
                    <i class="ri-inbox-line me-1"></i> Laporan Masuk
                    @if (($pendingCount ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill ms-1">{{ $pendingCount }}</span>
                    @endif
                </button>
            </li>
        </ul>

        {{-- Filter (berlaku untuk kedua tab) --}}
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold text-muted-custom">Tipe</label>
                        <select id="filterType" class="form-select form-select-custom">
                            <option value="">Semua Tipe</option>
                            <option value="monthly" {{ request('type') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="annual" {{ request('type') === 'annual' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold text-muted-custom">Status</label>
                        <select id="filterStatus" class="form-select form-select-custom">
                            <option value="">Semua Status</option>
                            @foreach (['draft' => 'Draft', 'submitted' => 'Terkirim', 'reviewed' => 'Direview', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $label)
                                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold text-muted-custom">Tahun</label>
                        <input type="number" id="filterYear" class="form-control form-control-custom" placeholder="Tahun"
                            value="{{ request('year') }}" min="2020" max="2030">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom">Bulan</label>
                        <select id="filterMonth" class="form-select form-select-custom">
                            <option value="">Semua Bulan</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}"
                                    {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }} -
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="button" class="btn btn-outline-custom w-100" id="btnResetFilter">
                            <i class="ri-refresh-line me-1"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content">
            {{-- ================= TAB: LAPORAN SAYA ================= --}}
            <div class="tab-pane fade {{ $activeTab === 'sent' ? 'show active' : '' }}" id="paneSent">
                <div class="card-custom">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Periode /
                                        Cabang</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Item</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                        Pemasukan</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                        Pengeluaran</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">Saldo
                                        Akhir</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groupedData as $key1 => $g1)
                                    <tr class="folder-row folder-l1" data-target="s-{{ $key1 }}">
                                        <td class="py-3 px-4 fw-bold">
                                            <i class="ri-folder-line me-2 text-warning"></i>
                                            {{ $isMP ? $g1['md_name'] : $g1['label'] }}
                                        </td>
                                        <td class="py-3 px-4 text-muted-custom">{{ count($g1['periods']) }} Periode</td>
                                        <td class="py-3 px-4">-</td>
                                        <td class="py-3 px-4 text-end text-success fw-medium">Rp
                                            {{ number_format($g1['total_income'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-end text-danger fw-medium">Rp
                                            {{ number_format($g1['total_expense'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-end text-primary fw-bold">Rp
                                            {{ number_format($g1['total_balance'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4">-</td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($isMP && $can('finance.report.export'))
                                                <a href="{{ route('admin.finance.export.transactions', ['branch_id' => $key1, 'year' => request('year', date('Y'))]) }}"
                                                    class="btn btn-sm btn-outline-success" title="Export">
                                                    <i class="ri-file-excel-2-line"></i>
                                                </a>
                                            @endif
                                            <i class="ri-arrow-down-s-line folder-icon ms-2"></i>
                                        </td>
                                    </tr>

                                    @foreach ($g1['periods'] as $key2 => $period)
                                        <tr class="folder-row folder-l2 d-none" data-parent="s-{{ $key1 }}"
                                            data-target="s-{{ $key1 }}-{{ $key2 }}">
                                            <td class="py-3 px-4 ps-5 fw-semibold">
                                                <i class="ri-folder-2-line me-2 text-info"></i> {{ $period['label'] }}
                                                @if (!$isMP && ($period['missing_count'] ?? 0) > 0)
                                                    <span class="badge bg-danger ms-2">{{ $period['missing_count'] }}
                                                        Belum Setor</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-muted-custom">{{ count($period['reports']) }} Item
                                            </td>
                                            <td class="py-3 px-4">-</td>
                                            <td class="py-3 px-4 text-end text-success fw-medium">Rp
                                                {{ number_format($period['total_income'], 0, ',', '.') }}</td>
                                            <td class="py-3 px-4 text-end text-danger fw-medium">Rp
                                                {{ number_format($period['total_expense'], 0, ',', '.') }}</td>
                                            <td class="py-3 px-4 text-end text-primary fw-bold">Rp
                                                {{ number_format($period['total_balance'], 0, ',', '.') }}</td>
                                            <td class="py-3 px-4">-</td>
                                            <td class="py-3 px-4 text-center">
                                                @if (!$isMP && $can('finance.report.submit'))
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        data-act="submit-folder" data-period="{{ $key2 }}">
                                                        <i class="ri-send-plane-line"></i> Kirim Semua
                                                    </button>
                                                @endif
                                                <i class="ri-arrow-down-s-line folder-icon ms-2"></i>
                                            </td>
                                        </tr>

                                        @foreach ($period['reports'] as $report)
                                            <tr class="d-none" data-parent="s-{{ $key1 }}-{{ $key2 }}">
                                                <td class="py-3 px-4 ps-5 text-muted-custom">{{ $period['label'] }}</td>
                                                <td class="py-3 px-4 fw-semibold text-primary">
                                                    {{ $report->details->first()?->church?->name ?? ($report->branch->name ?? '-') }}
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span
                                                        class="badge bg-secondary">{{ $report->report_type === 'monthly' ? 'Bulanan' : 'Tahunan' }}</span>
                                                </td>
                                                <td class="py-3 px-4 text-end text-success fw-medium">Rp
                                                    {{ number_format((float) $report->total_income, 0, ',', '.') }}</td>
                                                <td class="py-3 px-4 text-end text-danger fw-medium">Rp
                                                    {{ number_format((float) $report->total_expense, 0, ',', '.') }}</td>
                                                <td class="py-3 px-4 text-end text-primary fw-bold">Rp
                                                    {{ number_format((float) $report->closing_balance, 0, ',', '.') }}</td>
                                                <td class="py-3 px-4">
                                                    <span
                                                        class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">{{ ucfirst($report->status) }}</span>
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                            data-act="show" data-id="{{ $report->id }}"
                                                            title="Detail"><i class="ri-eye-line"></i></button>
                                                        @if ($report->status === 'draft' || $isMP)
                                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                                data-act="edit" data-id="{{ $report->id }}"
                                                                title="Edit"><i class="ri-pencil-line"></i></button>
                                                        @endif
                                                        @if ($report->status === 'draft')
                                                            @if ($can('finance.report.submit'))
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-warning"
                                                                    data-act="submit" data-id="{{ $report->id }}"
                                                                    title="Kirim"><i
                                                                        class="ri-send-plane-line"></i></button>
                                                            @endif
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                data-act="delete" data-id="{{ $report->id }}"
                                                                title="Hapus"><i class="ri-delete-bin-line"></i></button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-5 text-center text-muted-custom">
                                            <i class="ri-file-list-3-line display-6 d-block mb-2 opacity-50"></i>
                                            <h6 class="fw-semibold">Tidak ada laporan</h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ================= TAB: LAPORAN MASUK ================= --}}
            <div class="tab-pane fade {{ $activeTab === 'received' ? 'show active' : '' }}" id="paneReceived">
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @if ($can('finance.report.consolidate'))
                        <button type="button" class="btn btn-outline-custom" data-bs-toggle="modal"
                            data-bs-target="#modalConsolidate">
                            <i class="ri-calculator-line me-1"></i> Konsolidasi
                        </button>
                    @endif
                </div>

                <div class="card-custom">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">MD / Periode /
                                        Item</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">PIC</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                        Pemasukan</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                        Pengeluaran</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">Saldo
                                    </th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                                    <th class="py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groupedReceived as $mdId => $mdGroup)
                                    <tr class="folder-row folder-l1" data-target="r-{{ $mdId }}">
                                        <td class="py-3 px-4 fw-bold"><i class="ri-folder-line me-2 text-warning"></i>
                                            {{ $mdGroup['md_name'] }}</td>
                                        <td class="py-3 px-4 text-muted-custom">{{ count($mdGroup['periods']) }} Periode
                                        </td>
                                        <td class="py-3 px-4">-</td>
                                        <td class="py-3 px-4 text-end text-success fw-medium">Rp
                                            {{ number_format($mdGroup['total_income'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-end text-danger fw-medium">Rp
                                            {{ number_format($mdGroup['total_expense'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-end text-primary fw-bold">Rp
                                            {{ number_format($mdGroup['total_balance'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4">-</td>
                                        <td class="py-3 px-4 text-center"><i
                                                class="ri-arrow-down-s-line folder-icon ms-2"></i></td>
                                    </tr>

                                    @foreach ($mdGroup['periods'] as $periodKey => $period)
                                        @php
                                            $pp = explode('-', $periodKey);
                                            $periodYear = $pp[0];
                                            $periodMonth = isset($pp[1]) && $pp[1] !== 'annual' ? $pp[1] : null;
                                        @endphp
                                        <tr class="folder-row folder-l2 d-none" data-parent="r-{{ $mdId }}"
                                            data-target="r-{{ $mdId }}-{{ $periodKey }}">
                                            <td class="py-3 px-4 ps-5 fw-semibold"><i
                                                    class="ri-folder-2-line me-2 text-info"></i> {{ $period['label'] }}
                                            </td>
                                            <td class="py-3 px-4 text-muted-custom">{{ count($period['reports']) }} Gereja
                                            </td>
                                            <td class="py-3 px-4">-</td>
                                            <td class="py-3 px-4 text-end text-success fw-medium">Rp
                                                {{ number_format($period['total_income'], 0, ',', '.') }}</td>
                                            <td class="py-3 px-4 text-end text-danger fw-medium">Rp
                                                {{ number_format($period['total_expense'], 0, ',', '.') }}</td>
                                            <td class="py-3 px-4 text-end text-primary fw-bold">Rp
                                                {{ number_format($period['total_balance'], 0, ',', '.') }}</td>
                                            <td class="py-3 px-4">-</td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                                    @if ($can('finance.report.approve'))
                                                        <button type="button" class="btn-action btn-action-success"
                                                            data-act="approve-folder" data-period="{{ $periodKey }}"
                                                            data-md="{{ $mdId }}"
                                                            title="Setujui Semua di Periode Ini">
                                                            <i class="ri-check-double-line"></i>
                                                        </button>
                                                        <button type="button" class="btn-action btn-action-danger"
                                                            data-act="reject-folder" data-period="{{ $periodKey }}"
                                                            data-md="{{ $mdId }}"
                                                            title="Tolak Semua di Periode Ini">
                                                            <i class="ri-close-line"></i>
                                                        </button>
                                                    @endif
                                                    @if ($can('finance.report.export'))
                                                        <a href="{{ route('admin.finance.export.transactions', ['branch_id' => $mdId, 'year' => $periodYear, 'month' => $periodMonth]) }}"
                                                            class="btn-action btn-action-success"
                                                            title="Export Buku Kas Excel"><i
                                                                class="ri-file-excel-2-line"></i></a>
                                                        <a href="{{ route('admin.finance.export.transactions-pdf', ['branch_id' => $mdId, 'year' => $periodYear, 'month' => $periodMonth]) }}"
                                                            class="btn-action btn-action-danger"
                                                            title="Export Buku Kas PDF"><i
                                                                class="ri-file-pdf-line"></i></a>
                                                        <a href="{{ route('admin.finance.export.monthly-income', ['branch_id' => $mdId, 'year_start' => $periodYear, 'year_end' => $periodYear, 'month' => $periodMonth]) }}"
                                                            class="btn-action btn-action-success"
                                                            title="Export Pemasukan"><i
                                                                class="ri-file-list-3-line"></i></a>
                                                    @endif
                                                    <i class="ri-arrow-down-s-line folder-icon ms-2"></i>
                                                </div>
                                            </td>
                                        </tr>

                                        @foreach ($period['reports'] as $report)
                                            <tr class="d-none {{ $report->missing_pp ? 'bg-danger-subtle-custom' : '' }}"
                                                data-parent="r-{{ $mdId }}-{{ $periodKey }}">
                                                <td class="py-3 px-4 ps-5">
                                                    <div class="fw-semibold text-primary">
                                                        {{ $report->branch->name ?? '-' }}</div>
                                                </td>
                                                <td class="py-3 px-4 text-muted-custom">
                                                    <small><i
                                                            class="ri-user-line me-1"></i>{{ $report->branch->pic_name ?? 'Belum diatur' }}</small>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span
                                                        class="badge bg-secondary">{{ $report->report_type === 'monthly' ? 'Bulanan' : 'Tahunan' }}</span>
                                                </td>
                                                <td class="py-3 px-4 text-end text-success fw-medium">Rp
                                                    {{ number_format((float) $report->total_income, 0, ',', '.') }}</td>
                                                <td class="py-3 px-4 text-end text-danger fw-medium">Rp
                                                    {{ number_format((float) $report->total_expense, 0, ',', '.') }}</td>
                                                <td class="py-3 px-4 text-end text-primary fw-bold">Rp
                                                    {{ number_format((float) $report->closing_balance, 0, ',', '.') }}</td>
                                                <td class="py-3 px-4">
                                                    <span
                                                        class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">{{ ucfirst($report->status) }}</span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button type="button" class="btn-action btn-action-info"
                                                            data-act="show" data-id="{{ $report->id }}"
                                                            title="Detail"><i class="ri-eye-line"></i></button>
                                                        @if ($can('finance.report.export'))
                                                            <a href="{{ route('admin.finance.export.transactions', ['branch_id' => $report->branch_id, 'year' => $report->report_year, 'month' => $report->report_month]) }}"
                                                                class="btn-action btn-action-success"
                                                                title="Export Excel"><i
                                                                    class="ri-file-excel-2-line"></i></a>
                                                            <a href="{{ route('admin.finance.export.transactions-pdf', ['branch_id' => $report->branch_id, 'year' => $report->report_year, 'month' => $report->report_month]) }}"
                                                                class="btn-action btn-action-danger" title="Export PDF"><i
                                                                    class="ri-file-pdf-line"></i></a>
                                                        @endif
                                                        @if ($can('finance.report.approve') && in_array($report->status, ['submitted', 'reviewed']))
                                                            <button type="button" class="btn-action btn-action-success"
                                                                data-act="approve" data-id="{{ $report->id }}"
                                                                title="Setujui"><i class="ri-check-line"></i></button>
                                                            <button type="button" class="btn-action btn-action-danger"
                                                                data-act="reject" data-id="{{ $report->id }}"
                                                                title="Tolak"><i class="ri-close-line"></i></button>
                                                        @endif
                                                        @if ($isMP)
                                                            <button type="button" class="btn-action btn-action-primary"
                                                                data-act="edit" data-id="{{ $report->id }}"
                                                                title="Edit"><i class="ri-pencil-line"></i></button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-5 text-center text-muted-custom">
                                            <i class="ri-inbox-line display-6 d-block mb-2 opacity-50"></i>
                                            <h6 class="fw-semibold mb-1">Belum ada laporan yang diterima</h6>
                                            <p class="small mb-0">Laporan dari cabang akan muncul di sini setelah dikirim
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL XL: FORM BUAT / EDIT ================= --}}
    <div class="modal fade" id="modalReport" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <form id="formReport">
                    <input type="hidden" id="reportId">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalReportTitle">Buat Laporan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                @if ($isMD)
                                    <label class="form-label small fw-semibold text-muted-custom">Pilih Gereja / Pendeta
                                        <span class="text-danger">*</span></label>
                                    <select name="church_id" class="form-select form-select-custom" required>
                                        <option value="">-- Pilih Gereja --</option>
                                        @foreach ($churches as $c)
                                            <option value="{{ $c->id }}">
                                                {{ $c->name }}{{ $c->pic_name ? ' - ' . $c->pic_name : '' }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <label class="form-label small fw-semibold text-muted-custom">Gereja / MD <span
                                            class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-select form-select-custom" required>
                                        <option value="">-- Pilih Gereja / MD --</option>
                                        @foreach ($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted-custom">Tipe Laporan <span
                                        class="text-danger">*</span></label>
                                <select id="reportType" name="report_type" class="form-select form-select-custom"
                                    required>
                                    <option value="monthly">Bulanan</option>
                                    <option value="annual">Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted-custom">Tahun <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="reportYear" name="report_year"
                                    class="form-control form-control-custom" min="2020" max="2030" required>
                            </div>
                            <div class="col-md-6" id="monthGroup">
                                <label class="form-label small fw-semibold text-muted-custom">Bulan <span
                                        class="text-danger">*</span></label>
                                <select id="reportMonth" name="report_month" class="form-select form-select-custom">
                                    <option value="">Pilih Bulan</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">Periode Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="date" id="periodStart" name="period_start"
                                    class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted-custom">Periode Akhir <span
                                        class="text-danger">*</span></label>
                                <input type="date" id="periodEnd" name="period_end"
                                    class="form-control form-control-custom" required>
                            </div>
                        </div>

                        @foreach ($groups as $gKey => $g)
                            <h6 class="fw-semibold mt-4 mb-2">
                                <i class="ri-folder-2-line me-1 text-primary"></i> {{ $g['title'] }}
                            </h6>
                            <div class="table-responsive border rounded border-custom">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th
                                                class="border-0 py-2 px-3 small text-uppercase text-muted-custom fw-semibold">
                                                Kategori</th>
                                            <th class="border-0 py-2 px-3 small text-uppercase text-muted-custom fw-semibold text-end"
                                                style="width:220px">Jumlah</th>
                                            <th
                                                class="border-0 py-2 px-3 small text-uppercase text-muted-custom fw-semibold">
                                                Catatan</th>
                                            <th class="border-0 py-2 px-3" style="width:50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($g['cats'] as $code)
                                            <tr data-cat="{{ $code }}"
                                                data-group="{{ $categories[$code]['group'] }}">
                                                <td class="py-2 px-3 fw-medium">
                                                    {{ $categories[$code]['name'] ?? $code }}
                                                    <input type="hidden"
                                                        name="details[{{ $code }}][category_code]"
                                                        value="{{ $code }}">
                                                </td>
                                                <td class="py-2 px-3">
                                                    <input type="text" inputmode="numeric"
                                                        class="form-control form-control-custom text-end amount-display"
                                                        placeholder="0">
                                                    <input type="hidden" class="raw-amount"
                                                        name="details[{{ $code }}][amount]" value="0">
                                                </td>
                                                <td class="py-2 px-3">
                                                    <input type="text" name="details[{{ $code }}][notes]"
                                                        class="form-control form-control-custom note-input"
                                                        placeholder="Opsional">
                                                </td>
                                                <td class="py-2 px-3 text-center">
                                                    <button type="button" class="btn-action btn-action-danger"
                                                        data-act="clear-row" title="Kosongkan baris"><i
                                                            class="ri-close-line"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach

                        <div class="summary-card border rounded p-4 mt-4">
                            <div class="d-flex justify-content-between py-2 border-bottom border-custom">
                                <span class="text-muted-custom">Total Pemasukan</span>
                                <span id="sumIncome" class="fw-bold text-success">+ Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-custom">
                                <span class="text-muted-custom">Total Pengeluaran</span>
                                <span id="sumExpense" class="fw-bold text-danger">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 mt-1">
                                <span class="fw-bold">Selisih</span>
                                <span id="sumClosing" class="fw-bold text-primary">Rp 0</span>
                            </div>
                            <div class="small text-muted-custom mt-2">Saldo awal dihitung otomatis dari laporan yang sudah
                                disetujui saat laporan disimpan.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSaveReport"><i
                                class="ri-save-line me-1"></i> Simpan Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL XL: DETAIL ================= --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalDetailTitle">Detail Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="modalDetailBody"></div>
                <div class="modal-footer border-top border-custom py-3 px-4 flex-wrap gap-2" id="modalDetailFooter"></div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL XL: KONSOLIDASI ================= --}}
    @if ($can('finance.report.consolidate'))
        <div class="modal fade" id="modalConsolidate" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold"><i
                                class="ri-calculator-line me-2 text-primary"></i>Konsolidasi Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted-custom">Tahun</label>
                                <input type="number" id="consYear" class="form-control form-control-custom"
                                    value="{{ date('Y') }}" min="2020" max="2030">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted-custom">Bulan (opsional)</label>
                                <select id="consMonth" class="form-select form-select-custom">
                                    <option value="">Semua Bulan (Tahunan)</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-custom w-100" id="btnRunConsolidate"><i
                                        class="ri-play-line me-1"></i> Jalankan</button>
                            </div>
                        </div>
                        <div class="row g-3 d-none" id="consolidateResult">
                            <div class="col-md-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-muted-custom small">Total Laporan</div>
                                    <div class="fw-bold fs-4" id="consCount">0</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-success small">Total Pemasukan</div>
                                    <div class="fw-bold fs-5 text-success" id="consIncome">Rp 0</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-danger small">Total Pengeluaran</div>
                                    <div class="fw-bold fs-5 text-danger" id="consExpense">Rp 0</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-primary small">Saldo Bersih</div>
                                    <div class="fw-bold fs-5 text-primary" id="consNet">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= MODAL XL: IMPORT EXCEL (WIZARD) ================= --}}
    <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold"><i class="ri-file-excel-2-line me-2 text-success"></i>Import
                        Laporan dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Langkah 1: unggah --}}
                    <form id="formImportUpload" enctype="multipart/form-data">
                        <div class="alert alert-info small">
                            <i class="ri-information-line me-1"></i>
                            Semua sheet di file akan dianalisis. Di langkah berikutnya Anda memilih kolom Excel mana
                            yang masuk ke kolom database, mengecek tiap sheet, lalu mengimport semuanya sekaligus.
                        </div>
                        @if ($isMP)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">MD / Cabang tujuan <span
                                        class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select form-select-custom" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach ($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="mb-2">
                            <label class="form-label fw-semibold">File Excel <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control form-control-custom"
                                accept=".xlsx,.xls" required>
                        </div>
                    </form>

                    {{-- Langkah 2: wizard --}}
                    <div id="impStepWizard" class="d-none">
                        <details class="mb-3" id="impSettings">
                            <summary class="fw-semibold mb-2" style="cursor:pointer">Pengaturan umum (berlaku untuk semua sheet)</summary>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Lewati baris yang Uraiannya mengandung</label>
                                    <input type="text" id="impKeywords" class="form-control form-control-custom" value="saldo awal, total">
                                    <div class="form-text">Baris "Saldo Awal" dan baris total otomatis tidak ikut diimport.</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="impCarryDate" checked>
                                        <label class="form-check-label" for="impCarryDate">Baris tanpa tanggal memakai tanggal baris di atasnya</label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="impForceYear" checked>
                                        <label class="form-check-label" for="impForceYear">Paksa tahun tanggal mengikuti tahun periode sheet</label>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <label class="small" for="impBlankDay">Jika masih kosong, isi tanggal</label>
                                        <input type="number" id="impBlankDay" min="1" max="31"
                                            class="form-control form-control-custom form-control-sm" style="width:80px" placeholder="kosong">
                                    </div>
                                </div>
                            </div>
                        </details>

                        <div id="impDateNote"></div>
                        <ul class="nav nav-tabs" id="impTabs"></ul>
                        <div id="impPane" class="pt-3"></div>
                    </div>
                </div>

                <div class="modal-footer border-top border-custom py-3 px-4" id="impFooter1">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formImportUpload" class="btn btn-custom" id="btnImpAnalyze"><i
                            class="ri-search-line me-1"></i> Analisis File</button>
                </div>
                <div class="modal-footer border-top border-custom py-3 px-4 d-none" id="impFooter2">
                    <button type="button" class="btn btn-outline-custom me-auto" id="btnImpBack"><i
                            class="ri-arrow-left-line me-1"></i> Ganti File</button>
                    <span class="small text-muted-custom" id="impProgress"></span>
                    <button type="button" class="btn btn-outline-primary" id="btnImpCheck"><i
                            class="ri-play-line me-1"></i> Jalankan Pengecekan</button>
                    <button type="button" class="btn btn-success" id="btnImpCommit" disabled><i
                            class="ri-upload-line me-1"></i> Import Semua</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: KIRIM (satu laporan / satu periode) ================= --}}
    <div class="modal fade" id="modalSubmit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold"><i class="ri-send-plane-line me-2 text-primary"></i>Kirim Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="submitReportId">
                    <input type="hidden" id="submitPeriodKey">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kirim ke Tujuan <span class="text-danger">*</span></label>
                        <select id="submitTo" class="form-select form-select-custom">
                            <option value="">-- Pilih Tujuan --</option>
                            @foreach ($otherBranches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan (Opsional)</label>
                        <textarea id="submitNotes" class="form-control form-control-custom" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-custom">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-custom" id="btnConfirmSubmit"><i
                            class="ri-send-plane-line me-1"></i> Kirim</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: TOLAK (satu laporan / satu periode) ================= --}}
    <div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="formReject">
                    <input type="hidden" id="rejectId">
                    <input type="hidden" id="rejectPeriod">
                    <input type="hidden" id="rejectMd">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold"><i class="ri-close-circle-line me-2 text-danger"></i><span
                                id="rejectTitle">Tolak Laporan</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning small d-none" id="rejectFolderNote">
                            <i class="ri-error-warning-line me-1"></i> Semua laporan berstatus "Terkirim" / "Direview"
                            dalam periode ini akan ditolak.
                        </div>
                        <label class="form-label small fw-semibold text-muted-custom">Alasan Penolakan <span
                                class="text-danger">*</span></label>
                        <textarea id="rejectReason" class="form-control form-control-custom" rows="4" required minlength="10"
                            placeholder="Jelaskan alasan penolakan (minimal 10 karakter)..."></textarea>
                    </div>
                    <div class="modal-footer border-top border-custom">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="btnConfirmReject"><i
                                class="ri-close-line me-1"></i> Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: EXPORT FORM LAPKEU ================= --}}
    <div class="modal fade" id="modalExportLapkeu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold"><i class="ri-file-excel-2-line me-2 text-success"></i>Export Form
                        Lapkeu MD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted-custom">Tahun Awal <span
                                class="text-danger">*</span></label>
                        <input type="number" id="lapkeuStart" class="form-control form-control-custom"
                            value="{{ date('Y') }}" min="2020" max="2030">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted-custom">Tahun Akhir <span
                                class="text-danger">*</span></label>
                        <input type="number" id="lapkeuEnd" class="form-control form-control-custom"
                            value="{{ date('Y') }}" min="2020" max="2030">
                    </div>
                </div>
                <div class="modal-footer border-top border-custom">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" data-act="export-lapkeu"><i
                            class="ri-download-line me-1"></i> Download</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toastBox" class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000;"></div>

    <style>
        .modal-content-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
        }

        .form-control-custom,
        .form-select-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.55rem 0.8rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .summary-card {
            background: var(--bg-body);
            border: 1px solid var(--border-color);
        }

        .bg-danger-subtle-custom {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        .folder-row {
            cursor: pointer;
        }

        .folder-l1>td {
            background: rgba(37, 99, 235, 0.05);
        }

        .folder-l2>td {
            background: rgba(139, 92, 246, 0.05);
        }

        .folder-row:hover>td {
            filter: brightness(0.97);
        }

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
            padding: 0;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-action-info,
        .btn-action-primary {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-info:hover,
        .btn-action-primary:hover {
            background: var(--accent-blue);
            color: #fff;
        }

        .btn-action-success {
            border-color: #22c55e;
            color: #22c55e;
        }

        .btn-action-success:hover {
            background: #22c55e;
            color: #fff;
        }

        .btn-action-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-action-danger:hover {
            background: #ef4444;
            color: #fff;
        }

        .alert-rejected {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #ef4444;
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: var(--radius-sm);
        }

        .toast-custom {
            background: #fff;
            color: #333;
            border-left: 4px solid;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 0.9rem 1rem;
            margin-top: 0.5rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            min-width: 280px;
            transition: opacity 0.3s;
        }

        .toast-success {
            border-left-color: #10b981;
        }

        .toast-success i {
            color: #10b981;
        }

        .toast-error {
            border-left-color: #ef4444;
        }

        .toast-error i {
            color: #ef4444;
        }

        /* Perbaiki scroll modal yang membungkus isinya dengan <form> */
        .modal-dialog-scrollable .modal-content>form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-content>form .modal-body {
            overflow-y: auto;
        }

        #impTabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
        }

        #impTabs .nav-link {
            white-space: nowrap;
        }

        .imp-scroll {
            max-height: 320px;
            overflow: auto;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const BASE = @json(url('admin/finance/reports'));
            const INDEX_URL = @json(route('admin.finance.reports.index'));
            const STORE_URL = @json(route('admin.finance.reports.storeReport'));
            const IMPORT_URL = @json(route('admin.finance.reports.import'));
            const SUBMIT_FOLDER_URL = @json(route('admin.finance.reports.submit-folder'));
            const APPROVE_FOLDER_URL = @json(route('admin.finance.reports.approve-folder'));
            const REJECT_FOLDER_URL = @json(route('admin.finance.reports.reject-folder'));
            const CONSOLIDATE_URL = @json(route('admin.finance.reports.consolidate'));
            const EXPORT = {
                lapkeu: @json(route('admin.finance.export.consolidated')),
                income: @json(route('admin.finance.export.monthly-income')),
                kas: @json(route('admin.finance.export.transactions')),
                kasPdf: @json(route('admin.finance.export.transactions-pdf'))
            };
            const STATUS = {
                draft: ['Draft', 'warning'],
                submitted: ['Terkirim', 'info'],
                reviewed: ['Direview', 'primary'],
                approved: ['Disetujui', 'success'],
                rejected: ['Ditolak', 'danger']
            };

            let currentTab = @json($activeTab);

            // ---------- Helpers ----------
            const $id = id => document.getElementById(id);
            const modal = id => bootstrap.Modal.getOrCreateInstance($id(id));
            const fmt = n => new Intl.NumberFormat('id-ID').format(Number(n) || 0);
            const rp = n => 'Rp ' + fmt(n);
            const digits = v => String(v ?? '').replace(/\D/g, '');
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ||
                @json(csrf_token());

            function toast(type, msg) {
                const t = document.createElement('div');
                t.className = 'toast-custom toast-' + type;
                t.innerHTML = '<i class="ri-' + (type === 'success' ? 'checkbox-circle' : 'error-warning') +
                    '-line me-2 fs-5"></i><div>' + esc(msg).replace(/\n/g, '<br>') + '</div>';
                $id('toastBox').appendChild(t);
                setTimeout(() => {
                    t.style.opacity = '0';
                    setTimeout(() => t.remove(), 300);
                }, 3500);
            }

            async function api(url, method = 'GET', data = null) {
                const headers = {
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                const opt = {
                    method,
                    headers
                };
                if (data instanceof FormData) {
                    opt.body = data;
                } else if (data) {
                    headers['Content-Type'] = 'application/json';
                    opt.body = JSON.stringify(data);
                }
                const res = await fetch(url, opt);
                let json = {};
                try {
                    json = await res.json();
                } catch (e) {}
                if (!res.ok || json.success === false) {
                    let msg = json.message || 'Terjadi kesalahan (status ' + res.status + ')';
                    if (res.status === 422 && json.errors) msg = Object.values(json.errors).flat().join('\n');
                    throw new Error(msg);
                }
                return json;
            }

            async function withBusy(btn, label, fn) {
                const html = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + label;
                try {
                    return await fn();
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = html;
                }
            }

            function reloadPage() {
                const u = new URL(location.href);
                u.searchParams.set('tab', currentTab);
                location.href = u.toString();
            }

            function done(msg) {
                toast('success', msg || 'Berhasil');
                setTimeout(reloadPage, 800);
            }

            function swapModal(fromId, fn) {
                $id(fromId).addEventListener('hidden.bs.modal', fn, {
                    once: true
                });
                modal(fromId).hide();
            }

            // ---------- Tab & filter ----------
            document.querySelectorAll('#mainTabs [data-tab]').forEach(b => {
                b.addEventListener('shown.bs.tab', e => {
                    currentTab = e.target.dataset.tab;
                    const u = new URL(location.href);
                    u.searchParams.set('tab', currentTab);
                    history.replaceState(null, '', u);
                });
            });

            function applyFilters() {
                const p = new URLSearchParams({
                    tab: currentTab
                });
                [
                    ['type', 'filterType'],
                    ['status', 'filterStatus'],
                    ['year', 'filterYear'],
                    ['month', 'filterMonth']
                ].forEach(([k, i]) => {
                    const v = $id(i).value;
                    if (v) p.set(k, v);
                });
                location.href = INDEX_URL + '?' + p.toString();
            }
            ['filterType', 'filterStatus', 'filterYear', 'filterMonth'].forEach(i => $id(i).addEventListener(
                'change', applyFilters));
            $id('btnResetFilter').addEventListener('click', () => location.href = INDEX_URL + '?tab=' + currentTab);

            // ---------- Folder (tree) ----------
            function setIcon(row, open) {
                const i = row.querySelector('.folder-icon');
                if (!i) return;
                i.classList.toggle('ri-arrow-up-s-line', open);
                i.classList.toggle('ri-arrow-down-s-line', !open);
            }

            function collapse(target) {
                document.querySelectorAll(`[data-parent="${target}"]`).forEach(c => {
                    c.classList.add('d-none');
                    if (c.dataset.target) {
                        setIcon(c, false);
                        collapse(c.dataset.target);
                    }
                });
            }

            function toggleFolder(row) {
                const target = row.dataset.target;
                const children = document.querySelectorAll(`[data-parent="${target}"]`);
                const open = children.length > 0 && children[0].classList.contains('d-none');
                if (open) children.forEach(c => c.classList.remove('d-none'));
                else collapse(target);
                setIcon(row, open);
            }

            // ---------- Form buat / edit ----------
            const form = $id('formReport');

            function calcTotal() {
                let inc = 0,
                    exp = 0;
                form.querySelectorAll('tr[data-cat]').forEach(tr => {
                    const v = parseInt(tr.querySelector('.raw-amount').value) || 0;
                    if (tr.dataset.group === 'income') inc += v;
                    else exp += v;
                });
                $id('sumIncome').textContent = '+ ' + rp(inc);
                $id('sumExpense').textContent = '- ' + rp(exp);
                $id('sumClosing').textContent = rp(inc - exp);
            }

            function setRow(tr, amount, notes) {
                const raw = digits(Math.round(Number(amount) || 0));
                tr.querySelector('.raw-amount').value = raw || '0';
                tr.querySelector('.amount-display').value = raw && raw !== '0' ? fmt(raw) : '';
                tr.querySelector('.note-input').value = notes || '';
            }

            function toggleMonth() {
                const annual = $id('reportType').value === 'annual';
                $id('monthGroup').style.display = annual ? 'none' : '';
                $id('reportMonth').required = !annual;
                if (annual) $id('reportMonth').value = '';
            }

            function autoPeriod() {
                toggleMonth();
                const year = $id('reportYear').value;
                const month = $id('reportMonth').value;
                if (!year) return;
                if ($id('reportType').value === 'annual') {
                    $id('periodStart').value = `${year}-01-01`;
                    $id('periodEnd').value = `${year}-12-31`;
                } else if (month) {
                    const last = new Date(year, month, 0).getDate();
                    const m = String(month).padStart(2, '0');
                    $id('periodStart').value = `${year}-${m}-01`;
                    $id('periodEnd').value = `${year}-${m}-${last}`;
                }
            }
            ['reportType', 'reportYear', 'reportMonth'].forEach(i => $id(i).addEventListener('change', autoPeriod));

            form.addEventListener('focusin', e => {
                if (e.target.classList.contains('amount-display')) {
                    const raw = e.target.closest('tr').querySelector('.raw-amount').value;
                    e.target.value = raw === '0' ? '' : raw;
                }
            });
            form.addEventListener('input', e => {
                if (!e.target.classList.contains('amount-display')) return;
                e.target.value = digits(e.target.value);
                e.target.closest('tr').querySelector('.raw-amount').value = e.target.value || '0';
                calcTotal();
            });
            form.addEventListener('focusout', e => {
                if (!e.target.classList.contains('amount-display')) return;
                const raw = digits(e.target.value);
                e.target.value = raw ? fmt(raw) : '';
                e.target.closest('tr').querySelector('.raw-amount').value = raw || '0';
                calcTotal();
            });

            async function openReportForm(id = null) {
                form.reset();
                form.querySelectorAll('tr[data-cat]').forEach(tr => setRow(tr, 0, ''));
                $id('reportId').value = id || '';
                $id('modalReportTitle').textContent = id ? 'Edit Laporan' : 'Buat Laporan Baru';
                $id('reportYear').value = new Date().getFullYear();
                autoPeriod();
                calcTotal();
                modal('modalReport').show();

                if (!id) return;
                try {
                    const d = (await api(`${BASE}/${id}/edit`)).data;
                    if (form.elements['church_id']) form.elements['church_id'].value = d.church_id || '';
                    if (form.elements['branch_id']) form.elements['branch_id'].value = d.branch_id || '';
                    $id('reportType').value = d.report_type;
                    $id('reportYear').value = d.report_year;
                    $id('reportMonth').value = d.report_month || '';
                    toggleMonth();
                    $id('periodStart').value = d.period_start || '';
                    $id('periodEnd').value = d.period_end || '';
                    form.querySelectorAll('tr[data-cat]').forEach(tr => {
                        const x = (d.details || {})[tr.dataset.cat];
                        if (x) setRow(tr, x.amount, x.notes);
                    });
                    calcTotal();
                } catch (err) {
                    modal('modalReport').hide();
                    toast('error', err.message);
                }
            }

            form.addEventListener('submit', async e => {
                e.preventDefault();
                const id = $id('reportId').value;
                const fd = new FormData(form);
                if (id) fd.append('_method', 'PUT');
                try {
                    const res = await withBusy($id('btnSaveReport'), 'Menyimpan...', () =>
                        api(id ? `${BASE}/${id}` : STORE_URL, 'POST', fd));
                    modal('modalReport').hide();
                    done(res.message);
                } catch (err) {
                    toast('error', err.message);
                }
            });

            // ---------- Detail ----------
            const badge = s => {
                const [l, c] = STATUS[s] || [s, 'secondary'];
                return `<span class="badge bg-${c}">${esc(l)}</span>`;
            };

            function periodLabel(r) {
                if (r.report_type === 'annual') return 'Tahunan ' + r.report_year;
                const m = new Date(r.report_year, (r.report_month || 1) - 1, 1)
                    .toLocaleString('id-ID', {
                        month: 'long'
                    });
                return m + ' ' + r.report_year;
            }

            function detailTable(title, icon, color, list, total) {
                const rows = list.length ? list.map(d => `
                    <tr class="border-bottom border-custom">
                        <td class="py-3 px-4 fw-medium">${esc(d.category_name)}</td>
                        <td class="py-3 px-4 text-end text-${color} fw-medium">${rp(d.amount)}</td>
                        <td class="py-3 px-4 text-muted-custom small">${esc(d.notes || '-')}</td>
                    </tr>`).join('') :
                    '<tr><td colspan="3" class="py-3 text-center text-muted-custom small">Tidak ada data</td></tr>';
                return `
                    <div class="card-custom mb-4">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold"><i class="${icon} me-2 text-${color}"></i>${title}</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-body-tertiary"><tr>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Uraian / Kategori</th>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">Jumlah</th>
                                    <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Catatan</th>
                                </tr></thead>
                                <tbody>${rows}
                                    <tr class="bg-body-tertiary"><td class="py-3 px-4 fw-bold">TOTAL</td>
                                    <td class="py-3 px-4 text-end text-${color} fw-bold">${rp(total)}</td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>`;
            }

            function infoItem(icon, label, value) {
                return `<div class="col-md-6"><div class="d-flex gap-3"><i class="${icon} text-primary fs-5"></i>
                    <div><p class="text-muted-custom small mb-1">${label}</p><p class="fw-semibold mb-0">${value}</p></div></div></div>`;
            }

            function renderDetail(r) {
                const inc = r.details.filter(d => d.category_group === 'income' && d.amount > 0);
                const exp = r.details.filter(d => d.category_group !== 'income' && d.amount > 0);
                const church = r.church_name ?
                    `${esc(r.church_name)}${r.church_pic ? ' - ' + esc(r.church_pic) : ''}` : '';

                $id('modalDetailTitle').textContent = (r.report_type === 'monthly' ? 'Laporan Bulanan' :
                    'Laporan Tahunan') + ' - ' + periodLabel(r);

                $id('modalDetailBody').innerHTML = `
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-4">
                        <div>
                            <div class="fw-semibold"><i class="ri-building-line me-1"></i>${esc(r.branch_name || '-')}</div>
                            ${church ? `<div class="text-muted-custom small"><i class="ri-user-line me-1"></i>${church}</div>` : ''}
                        </div>
                        <div class="text-md-end">${badge(r.status)}
                            ${r.submitted_to_name ? `<div class="text-muted-custom small mt-1"><i class="ri-arrow-right-line me-1"></i>Dikirim ke: ${esc(r.submitted_to_name)}</div>` : ''}
                        </div>
                    </div>
                    <div class="row g-3 text-center mb-4">
                        <div class="col-md-3"><div class="summary-card rounded p-3"><div class="small text-muted-custom">Saldo Awal</div><div class="fw-bold fs-5">${rp(r.opening_balance)}</div></div></div>
                        <div class="col-md-3"><div class="summary-card rounded p-3"><div class="small text-success">Total Pemasukan</div><div class="fw-bold fs-5 text-success">+ ${rp(r.total_income)}</div></div></div>
                        <div class="col-md-3"><div class="summary-card rounded p-3"><div class="small text-danger">Total Pengeluaran</div><div class="fw-bold fs-5 text-danger">- ${rp(r.total_expense)}</div></div></div>
                        <div class="col-md-3"><div class="summary-card rounded p-3"><div class="small text-primary">Saldo Akhir</div><div class="fw-bold fs-5 text-primary">${rp(r.closing_balance)}</div></div></div>
                    </div>
                    ${r.status === 'rejected' && r.rejection_reason ? `<div class="alert-rejected mb-4"><i class="ri-error-warning-fill"></i><div><strong>Alasan Penolakan:</strong> ${esc(r.rejection_reason)}</div></div>` : ''}
                    ${detailTable('Penerimaan Dana (Debit)', 'ri-arrow-up-circle-line', 'success', inc, r.total_income)}
                    ${detailTable('Pengeluaran (Kredit)', 'ri-arrow-down-circle-line', 'danger', exp, r.total_expense)}
                    <div class="card-custom"><div class="card-body p-4"><div class="row g-4">
                        ${infoItem('ri-calendar-line', 'Dibuat Pada', esc(r.created_at || '-'))}
                        ${r.submitted_at ? infoItem('ri-send-plane-line', 'Dikirim Pada', esc(r.submitted_at)) : ''}
                        ${r.approved_at ? infoItem('ri-checkbox-circle-line', 'Disetujui Oleh', esc(r.approver_name || '-') + '<br><small class="text-muted-custom fw-normal">' + esc(r.approved_at) + '</small>') : ''}
                    </div>
                    ${r.notes ? `<hr class="border-custom my-4"><p class="text-muted-custom small mb-1">Catatan</p><p class="mb-0">${esc(r.notes)}</p>` : ''}
                    </div></div>`;

                let f =
                    '<button type="button" class="btn btn-outline-custom me-auto" data-bs-dismiss="modal">Tutup</button>';
                if (r.export_url) f +=
                    `<a href="${esc(r.export_url)}" class="btn btn-outline-success"><i class="ri-file-excel-2-line me-1"></i> Export Excel</a>`;
                if (r.can_edit) f +=
                    `<button type="button" class="btn btn-outline-custom" data-act="detail-edit" data-id="${esc(r.id)}"><i class="ri-pencil-line me-1"></i> Edit</button>`;
                if (r.can_submit) f +=
                    `<button type="button" class="btn btn-custom" data-act="detail-submit" data-id="${esc(r.id)}"><i class="ri-send-plane-line me-1"></i> Kirim</button>`;
                if (r.can_approve) {
                    f +=
                        `<button type="button" class="btn btn-success" data-act="detail-approve" data-id="${esc(r.id)}"><i class="ri-check-line me-1"></i> Setujui</button>`;
                    f +=
                        `<button type="button" class="btn btn-warning" data-act="detail-reject" data-id="${esc(r.id)}"><i class="ri-close-line me-1"></i> Tolak</button>`;
                }
                $id('modalDetailFooter').innerHTML = f;
            }

            async function showReport(id) {
                $id('modalDetailTitle').textContent = 'Detail Laporan';
                $id('modalDetailFooter').innerHTML = '';
                $id('modalDetailBody').innerHTML =
                    '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';
                modal('modalDetail').show();
                try {
                    renderDetail((await api(`${BASE}/${id}`)).data);
                } catch (err) {
                    $id('modalDetailBody').innerHTML =
                        `<div class="alert alert-danger mb-0">${esc(err.message)}</div>`;
                }
            }

            // ---------- Kirim / Setujui / Tolak / Hapus ----------
            function openSubmit({
                id = '',
                period = ''
            }) {
                $id('submitReportId').value = id;
                $id('submitPeriodKey').value = period;
                $id('submitTo').value = '';
                $id('submitNotes').value = '';
                modal('modalSubmit').show();
            }

            $id('btnConfirmSubmit').addEventListener('click', async function() {
                const id = $id('submitReportId').value;
                const period = $id('submitPeriodKey').value;
                const to = $id('submitTo').value;
                if (!to) return toast('error', 'Pilih tujuan terlebih dahulu');
                const notes = $id('submitNotes').value;
                try {
                    const res = await withBusy(this, 'Mengirim...', () => period ?
                        api(SUBMIT_FOLDER_URL, 'POST', {
                            period_key: period,
                            submitted_to: to,
                            notes
                        }) :
                        api(`${BASE}/${id}/submit`, 'POST', {
                            submitted_to: to,
                            notes
                        }));
                    modal('modalSubmit').hide();
                    done(res.message);
                } catch (err) {
                    toast('error', err.message);
                }
            });

            function openReject({
                id = '',
                period = '',
                md = ''
            }) {
                $id('rejectId').value = id;
                $id('rejectPeriod').value = period;
                $id('rejectMd').value = md;
                $id('rejectReason').value = '';
                $id('rejectTitle').textContent = period ? 'Tolak Semua Laporan Periode' : 'Tolak Laporan';
                $id('rejectFolderNote').classList.toggle('d-none', !period);
                modal('modalReject').show();
            }

            $id('formReject').addEventListener('submit', async e => {
                e.preventDefault();
                const id = $id('rejectId').value;
                const reason = $id('rejectReason').value.trim();
                try {
                    const res = await withBusy($id('btnConfirmReject'), 'Memproses...', () => id ?
                        api(`${BASE}/${id}/reject`, 'POST', {
                            rejection_reason: reason
                        }) :
                        api(REJECT_FOLDER_URL, 'POST', {
                            period_key: $id('rejectPeriod').value,
                            md_id: $id('rejectMd').value,
                            rejection_reason: reason
                        }));
                    modal('modalReject').hide();
                    done(res.message);
                } catch (err) {
                    toast('error', err.message);
                }
            });

            async function approveReport(id, btn) {
                if (!confirm('Setujui laporan ini?')) return;
                try {
                    const res = await api(`${BASE}/${id}/approve`, 'POST');
                    done(res.message);
                } catch (err) {
                    toast('error', err.message);
                }
            }

            async function approveFolder(period, md) {
                if (!confirm('Setujui SEMUA laporan dalam periode ini? Tindakan ini tidak dapat dibatalkan.'))
                    return;
                try {
                    const res = await api(APPROVE_FOLDER_URL, 'POST', {
                        period_key: period,
                        md_id: md
                    });
                    done(res.message);
                } catch (err) {
                    toast('error', err.message);
                }
            }

            async function deleteReport(id) {
                if (!confirm('Yakin ingin menghapus laporan ini?')) return;
                try {
                    const res = await api(`${BASE}/${id}`, 'DELETE');
                    done(res.message);
                } catch (err) {
                    toast('error', err.message);
                }
            }

            // ---------- Import Excel (wizard) ----------
            const IMP_URL = {
                analyze: @json(route('admin.finance.reports.import.analyze')),
                columns: @json(route('admin.finance.reports.import.columns')),
                preview: @json(route('admin.finance.reports.import.preview')),
                commit: @json(route('admin.finance.reports.import.commit'))
            };
            const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const IMP_BADGE = {
                skip: ['Tidak disertakan', 'secondary'],
                pending: ['Belum dicek', 'light text-dark border'],
                error: ['Error', 'danger'],
                warn: ['Perlu konfirmasi', 'warning text-dark'],
                blocked: ['Akan dilewati', 'danger'],
                ok: ['Siap', 'success']
            };
            let imp = {};
            const impReset = () => {
                imp = { token: null, targets: [], dateColumn: false, sheets: [], names: {}, active: 0, branch: '', running: false };
            };
            impReset();

            const impBranch = () => document.querySelector('#formImportUpload [name=branch_id]')?.value || '';

            function impShowStep(n) {
                $id('formImportUpload').classList.toggle('d-none', n !== 1);
                $id('impStepWizard').classList.toggle('d-none', n !== 2);
                $id('impFooter1').classList.toggle('d-none', n !== 1);
                $id('impFooter2').classList.toggle('d-none', n !== 2);
            }

            function impOpts() {
                return {
                    skip_keywords: $id('impKeywords').value,
                    carry_date: $id('impCarryDate').checked,
                    force_year: $id('impForceYear').checked,
                    blank_day: $id('impBlankDay').value
                };
            }

            const impSheetPayload = s => ({
                name: s.name,
                period: { year: +s.period.year, month: +s.period.month },
                first_row: +s.first_row,
                last_row: +s.last_row,
                mapping: Object.fromEntries(s.columns.map(c => [c.index, c.target])),
                exclude_rows: [...s.exclude]
            });

            function impDecision(d) {
                if (d === 'skip') return { action: 'skip' };
                const [a, ...rest] = d.split(':');
                const v = rest.join(':');
                return a === 'use' ? { action: 'use', church_id: v } : { action: 'new', type: v };
            }

            function impState(s) {
                if (!s.selected) return 'skip';
                if (s.error) return 'error';
                if (!s.result) return 'pending';
                if (s.result.report.will === 'skip') return 'blocked';
                return s.result.names.some(n => !imp.names[n.key].decision) ? 'warn' : 'ok';
            }

            function impUnresolved() {
                const keys = new Set();
                imp.sheets.filter(s => s.selected && s.result).forEach(s => {
                    s.result.names.forEach(n => { if (!imp.names[n.key].decision) keys.add(n.key); });
                });
                return [...keys];
            }

            function impRenderTabs() {
                $id('impTabs').innerHTML = imp.sheets.map((s, i) => {
                    const [label, cls] = IMP_BADGE[impState(s)];
                    return `<li class="nav-item"><button type="button" class="nav-link ${i === imp.active ? 'active' : ''}" data-imp-tab="${i}">${esc(s.name)} <span class="badge bg-${cls} ms-1">${label}</span></button></li>`;
                }).join('');
            }

            function impResultHtml(s) {
                if (s.error) return `<div class="alert alert-danger mb-0">${esc(s.error)}</div>`;
                if (!s.result) return '<div class="text-muted-custom small">Belum diperiksa. Klik "Jalankan Pengecekan" di bawah.</div>';

                const r = s.result;
                const per = MONTHS[(+s.period.month) - 1] + ' ' + s.period.year;
                const will = {
                    create: ['info', `Laporan ${per} belum ada, akan dibuat baru (draft).`],
                    replace: ['warning', `Laporan ${per} sudah ada (draft), isinya akan DIGANTI.`],
                    skip: ['danger', `Laporan ${per} sudah dikirim/disetujui, sheet ini akan dilewati.`]
                }[r.report.will];
                const badgeOf = { exact: ['success', 'Cocok persis'], similar: ['warning text-dark', 'Mirip (beda huruf/spasi)'], new: ['info', 'Baru'] };

                const nameRows = r.names.length ? r.names.map(n0 => {
                    const n = imp.names[n0.key];
                    const [bc, bl] = badgeOf[n0.status];
                    const opt = (v, l) => `<option value="${esc(v)}" ${n.decision === v ? 'selected' : ''}>${l}</option>`;
                    const sel = `<select class="form-select form-select-custom form-select-sm ${n.decision ? '' : 'is-invalid'}" data-name-key="${esc(n.key)}">` +
                        opt('', '— pilih tindakan —') +
                        n0.candidates.map(c => opt('use:' + c.id, 'Pakai yang lama: ' + esc(c.name) + ' (' + (c.type === 'item' ? 'Item' : 'Gereja') + ')')).join('') +
                        opt('new:church', 'Buat baru sebagai Gereja') +
                        opt('new:item', 'Buat baru sebagai Item') +
                        opt('skip', 'Tanpa gereja/item') + '</select>';
                    const variants = n0.variants.length > 1 ? ' · variasi: ' + esc(n0.variants.join(' | ')) : '';
                    return `<tr><td class="px-3 py-2"><div class="fw-medium">${esc(n0.name)}</div><div class="small text-muted-custom">${n0.count}x${variants}</div></td>
                        <td class="px-3"><span class="badge bg-${bc}">${bl}</span></td><td class="px-3" style="min-width:300px">${sel}</td></tr>`;
                }).join('') : '<tr><td colspan="3" class="px-3 py-3 text-muted-custom small">Tidak ada nama gereja/pendeta yang terdeteksi di sheet ini.</td></tr>';

                const i = imp.sheets.indexOf(s);
                const rowRows = r.rows.map(x => `<tr class="${x.skip && !x.manual ? 'text-muted-custom' : ''}">
                    <td class="px-2">${x.row}</td><td class="px-2">${esc(x.date || '-')}</td><td class="px-2">${esc(x.raw)}</td>
                    <td class="px-2 small">${esc(x.who || '-')}</td>
                    <td class="px-2 small">${esc(x.amounts)}</td>
                    <td class="px-2 small">${x.skip ? esc(x.skip) : '<span class="text-success">Diimport</span>'}</td>
                    <td class="px-2 text-center"><input type="checkbox" class="form-check-input" data-i="${i}" data-ex="${x.row}" ${x.manual ? 'checked' : ''} ${x.skip && !x.manual ? 'disabled' : ''}></td></tr>`).join('');
                return `
                    <div class="alert alert-${will[0]} small py-2">${will[1]}</div>
                    <div class="row g-3 mb-3 text-center">
                        <div class="col-6 col-md-3"><div class="summary-card rounded p-2"><div class="small text-muted-custom">Baris diimport</div><div class="fw-bold">${r.summary.used}</div></div></div>
                        <div class="col-6 col-md-3"><div class="summary-card rounded p-2"><div class="small text-muted-custom">Baris dilewati</div><div class="fw-bold">${r.summary.skipped}</div></div></div>
                        <div class="col-6 col-md-3"><div class="summary-card rounded p-2"><div class="small text-success">Total Pemasukan</div><div class="fw-bold text-success">${rp(r.summary.income)}</div></div></div>
                        <div class="col-6 col-md-3"><div class="summary-card rounded p-2"><div class="small text-danger">Total Pengeluaran</div><div class="fw-bold text-danger">${rp(r.summary.expense)}</div></div></div>
                    </div>
                    <div class="small text-muted-custom mb-3">Bandingkan total di atas dengan "Total Debit / Total Kredit" di Excel.</div>
                    <h6 class="fw-semibold">Gereja / Item</h6>
                    <div class="table-responsive border rounded border-custom mb-4"><table class="table table-sm align-middle mb-0"><tbody>${nameRows}</tbody></table></div>
                    <h6 class="fw-semibold">Baris data <span class="small fw-normal text-muted-custom">(centang untuk mengecualikan; klik "Cek ulang" setelah mengubah)</span></h6>
                    <div class="table-responsive imp-scroll border rounded border-custom">
                        <table class="table table-sm align-middle mb-0">
                        <thead class="bg-body-tertiary"><tr><th class="px-2">Baris</th><th class="px-2">Tanggal</th><th class="px-2">Uraian</th><th class="px-2">Gereja / PJ</th><th class="px-2">Nilai</th><th class="px-2">Status</th><th class="px-2 text-center">Kecualikan</th></tr></thead>                            <tbody>${rowRows}</tbody>
                        </table>
                    </div>`;
            }

            function impPaneHtml(s, i) {
                if (!s) return '';
                if (s.empty) return '<div class="alert alert-secondary">Sheet ini kosong dan akan dilewati.</div>';

                const num = (f, v, w) => `<input type="number" class="form-control form-control-custom form-control-sm" style="width:${w}px" data-i="${i}" data-f="${f}" value="${v ?? ''}">`;
                const monthOpts = '<option value="">Bulan...</option>' + MONTHS.map((m, k) => `<option value="${k + 1}" ${+s.period.month === k + 1 ? 'selected' : ''}>${m}</option>`).join('');

                const groups = {};
                imp.targets.forEach(t => (groups[t.group] = groups[t.group] || []).push(t));
                const targetOpts = sel => Object.entries(groups).map(([g, list]) =>
                    `<optgroup label="${esc(g)}">` + list.map(t => `<option value="${esc(t.value)}" ${t.value === sel ? 'selected' : ''}>${esc(t.label)}</option>`).join('') + '</optgroup>').join('');

                const colRows = s.columns.map(c => `<tr>
                    <td class="px-3"><span class="badge bg-secondary">${esc(c.letter)}</span></td>
                    <td class="px-3 small">${c.group ? '<span class="text-muted-custom">' + esc(c.group) + ' /</span> ' : ''}<strong>${esc(c.label)}</strong></td>
                    <td class="px-3 small text-muted-custom">${esc((c.sample || []).join(' | '))}</td>
                    <td class="px-3" style="min-width:240px"><select class="form-select form-select-custom form-select-sm" data-i="${i}" data-col="${c.index}">${targetOpts(c.target)}</select></td></tr>`).join('');

                return `
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="impSel" data-i="${i}" data-f="selected" ${s.selected ? 'checked' : ''}>
                            <label class="form-check-label fw-semibold" for="impSel">Sertakan sheet ini</label></div>
                        ${s.hidden ? '<span class="badge bg-secondary">Sheet tersembunyi</span>' : ''}
                        <div class="d-flex align-items-center gap-2 ms-md-auto"><span class="small fw-semibold">Periode laporan:</span>
                            <select class="form-select form-select-custom form-select-sm" style="width:140px" data-i="${i}" data-f="period.month">${monthOpts}</select>
                            ${num('period.year', s.period.year, 100)}</div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 small">
                        <span class="fw-semibold">Baris header (grup / kolom):</span>${num('group_row', s.group_row, 70)}${num('sub_row', s.sub_row, 70)}
                        <span class="fw-semibold ms-2">Baris data:</span>${num('first_row', s.first_row, 80)}<span>s/d</span>${num('last_row', s.last_row, 80)}
                        <button type="button" class="btn btn-sm btn-outline-custom" data-imp-act="redetect" data-i="${i}"><i class="ri-refresh-line me-1"></i>Deteksi ulang kolom</button>
                    </div>
                    <h6 class="fw-semibold">Pemetaan kolom</h6>
                    <div class="table-responsive imp-scroll border rounded border-custom mb-4">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="bg-body-tertiary"><tr><th class="px-3">Kolom</th><th class="px-3">Judul di Excel</th><th class="px-3">Contoh isi</th><th class="px-3">Masuk ke</th></tr></thead>
                            <tbody>${colRows}</tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="fw-semibold mb-0">Hasil pengecekan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-imp-act="recheck" data-i="${i}"><i class="ri-play-line me-1"></i>Cek ulang sheet ini</button></div>
                    <div id="impResult">${impResultHtml(s)}</div>`;
            }

            function impRefresh() {
                impRenderTabs();
                const sel = imp.sheets.filter(s => s.selected && !s.empty);
                const un = impUnresolved().length;
                $id('btnImpCommit').disabled = !(sel.length > 0 && sel.every(s => s.result && !s.error) && un === 0);
                if (!imp.running) $id('impProgress').textContent = un ? `${un} nama perlu dikonfirmasi` : '';
            }

            function impRender() {
                $id('impDateNote').innerHTML = imp.dateColumn ? '' :
                    '<div class="alert alert-info small"><i class="ri-information-line me-1"></i>Kolom <code>transaction_date</code> belum ada di tabel <code>financial_report_details</code>, jadi tanggal per baris hanya dipakai untuk pengecekan dan belum disimpan.</div>';
                $id('impPane').innerHTML = impPaneHtml(imp.sheets[imp.active], imp.active);
                impRefresh();
            }

            function impInvalidate(s) {
                s.result = null;
                s.error = null;
                const box = $id('impResult');
                if (box && imp.sheets[imp.active] === s) box.innerHTML = impResultHtml(s);
                impRefresh();
            }

            async function impCheckSheet(i) {
                const s = imp.sheets[i];
                s.error = null;
                if (!s.period.year || !s.period.month) throw new Error('Isi bulan dan tahun periode sheet ini');
                const t = s.columns.map(c => c.target);
                if (!t.includes('name')) throw new Error('Pilih satu kolom sebagai "Uraian / Nama Gereja-Item"');
                if (!t.some(x => !['ignore', 'date', 'name', 'notes'].includes(x))) throw new Error('Pilih minimal satu kolom nilai (PP, Iuran, dst.)');
                const res = await api(IMP_URL.preview, 'POST', {
                    token: imp.token, branch_id: imp.branch, opts: impOpts(), sheet: impSheetPayload(s),
                    all_sheets: imp.sheets.filter(x => x.selected && !x.empty).map(impSheetPayload)
                });
                s.result = res.data;
                res.data.names.forEach(n => {
                    if (!imp.names[n.key]) {
                        imp.names[n.key] = { ...n, decision: n.status === 'new' ? 'new:church' : 'use:' + n.candidates[0].id };
                    } else {
                        imp.names[n.key].variants = [...new Set([...imp.names[n.key].variants, ...n.variants])];
                    }
                });
            }

            // Langkah 1: unggah & analisis
            $id('formImportUpload').addEventListener('submit', async e => {
                e.preventDefault();
                try {
                    const res = await withBusy($id('btnImpAnalyze'), 'Menganalisis...', () => api(IMP_URL.analyze, 'POST', new FormData(e.target)));
                    imp.token = res.token;
                    imp.targets = res.targets;
                    imp.dateColumn = res.date_column;
                    imp.branch = impBranch();
                    imp.sheets = res.sheets.map(s => ({
                        ...s,
                        selected: !s.hidden && !s.empty,
                        period: s.period || { year: new Date().getFullYear(), month: '' },
                        exclude: new Set(),
                        result: null,
                        error: null
                    }));
                    imp.active = Math.max(0, imp.sheets.findIndex(s => s.selected));
                    impShowStep(2);
                    impRender();
                } catch (err) {
                    toast('error', err.message);
                }
            });

            $id('btnImpBack').addEventListener('click', () => { impReset(); impShowStep(1); });
            $id('modalImport').addEventListener('hidden.bs.modal', () => { impReset(); $id('formImportUpload').reset(); impShowStep(1); });

            // Pengecekan semua sheet terpilih, berurutan, sekali jalan
            $id('btnImpCheck').addEventListener('click', async function() {
                const idx = imp.sheets.map((s, i) => i).filter(i => imp.sheets[i].selected && !imp.sheets[i].empty);
                if (!idx.length) return toast('error', 'Pilih minimal satu sheet');
                this.disabled = true;
                imp.running = true;
                for (let n = 0; n < idx.length; n++) {
                    const s = imp.sheets[idx[n]];
                    $id('impProgress').textContent = `Memeriksa ${n + 1}/${idx.length}: ${s.name}`;
                    try {
                        await impCheckSheet(idx[n]);
                    } catch (err) {
                        s.error = err.message;
                        s.result = null;
                    }
                    impRenderTabs();
                }
                imp.running = false;
                this.disabled = false;
                impRender();
            });

            // Langkah 3: import semuanya
            $id('btnImpCommit').addEventListener('click', async function() {
                const sheets = imp.sheets.filter(s => s.selected && !s.empty).map(impSheetPayload);
                const decisions = {};
                Object.entries(imp.names).forEach(([k, n]) => { if (n.decision) decisions[k] = impDecision(n.decision); });
                if (!confirm(`Import ${sheets.length} sheet ke database?`)) return;
                try {
                    const res = await withBusy(this, 'Mengimport...', () => api(IMP_URL.commit, 'POST', {
                        token: imp.token, branch_id: imp.branch, opts: impOpts(), sheets, decisions
                    }));
                    modal('modalImport').hide();
                    done(res.message + (res.skipped?.length ? '\n' + res.skipped.join('\n') : ''));
                } catch (err) {
                    toast('error', err.message);
                }
            });

            // Interaksi di dalam wizard
            $id('impStepWizard').addEventListener('click', async e => {
                const tab = e.target.closest('[data-imp-tab]');
                if (tab) {
                    imp.active = +tab.dataset.impTab;
                    return impRender();
                }
                const b = e.target.closest('[data-imp-act]');
                if (!b) return;
                const i = +b.dataset.i;
                const s = imp.sheets[i];
                try {
                    if (b.dataset.impAct === 'redetect') {
                        const res = await withBusy(b, 'Mendeteksi...', () => api(IMP_URL.columns, 'POST', {
                            token: imp.token, branch_id: imp.branch, sheet: s.name, group_row: +s.group_row, sub_row: +s.sub_row
                        }));
                        s.columns = res.columns;
                        s.first_row = +s.sub_row + 1;
                        s.result = null;
                        s.error = null;
                        impRender();
                    } else if (b.dataset.impAct === 'recheck') {
                        await withBusy(b, 'Memeriksa...', () => impCheckSheet(i));
                        impRender();
                    }
                } catch (err) {
                    s.error = err.message;
                    s.result = null;
                    impRender();
                }
            });

            $id('impStepWizard').addEventListener('change', e => {
                const t = e.target;
                if (t.closest('#impSettings')) { // pengaturan umum berubah: semua hasil cek harus diulang
                    imp.sheets.forEach(s => { s.result = null; s.error = null; });
                    return impRender();
                }
                if (t.dataset.nameKey !== undefined) {
                    imp.names[t.dataset.nameKey].decision = t.value;
                    t.classList.toggle('is-invalid', !t.value);
                    return impRefresh();
                }
                if (t.dataset.i === undefined) return;
                const s = imp.sheets[+t.dataset.i];
                if (t.dataset.col !== undefined) {
                    s.columns.find(c => c.index === +t.dataset.col).target = t.value;
                    return impInvalidate(s);
                }
                if (t.dataset.ex !== undefined) {
                    if (t.checked) s.exclude.add(+t.dataset.ex);
                    else s.exclude.delete(+t.dataset.ex);
                    return;
                }
                const f = t.dataset.f;
                if (f === 'selected') {
                    s.selected = t.checked;
                    return impRender();
                }
                if (f === 'period.month') s.period.month = t.value;
                else if (f === 'period.year') s.period.year = t.value;
                else s[f] = t.value;
                impInvalidate(s);
            });

            // ---------- Konsolidasi ----------
            $id('btnRunConsolidate')?.addEventListener('click', async function() {
                try {
                    const res = await withBusy(this, 'Memproses...', () => api(CONSOLIDATE_URL,
                        'POST', {
                            year: $id('consYear').value,
                            month: $id('consMonth').value || null
                        }));
                    $id('consolidateResult').classList.remove('d-none');
                    $id('consCount').textContent = res.data.reports_count;
                    $id('consIncome').textContent = rp(res.data.total_income);
                    $id('consExpense').textContent = rp(res.data.total_expense);
                    $id('consNet').textContent = rp(res.data.net_balance);
                } catch (err) {
                    toast('error', err.message);
                }
            });

            // ---------- Export ----------
            function askYear() {
                return prompt('Masukkan tahun:', new Date().getFullYear());
            }

            // ---------- Delegasi klik ----------
            document.addEventListener('click', e => {
                const b = e.target.closest('[data-act]');
                if (b) {
                    const {
                        act,
                        id,
                        period,
                        md
                    } = b.dataset;
                    switch (act) {
                        case 'create':
                            return openReportForm();
                        case 'edit':
                            return openReportForm(id);
                        case 'show':
                            return showReport(id);
                        case 'delete':
                            return deleteReport(id);
                        case 'submit':
                            return openSubmit({
                                id
                            });
                        case 'submit-folder':
                            return openSubmit({
                                period
                            });
                        case 'approve':
                            return approveReport(id);
                        case 'approve-folder':
                            return approveFolder(period, md);
                        case 'reject':
                            return openReject({
                                id
                            });
                        case 'reject-folder':
                            return openReject({
                                period,
                                md
                            });
                        case 'clear-row':
                            setRow(b.closest('tr'), 0, '');
                            return calcTotal();
                        case 'detail-edit':
                            return swapModal('modalDetail', () => openReportForm(id));
                        case 'detail-submit':
                            return swapModal('modalDetail', () => openSubmit({
                                id
                            }));
                        case 'detail-reject':
                            return swapModal('modalDetail', () => openReject({
                                id
                            }));
                        case 'detail-approve':
                            return approveReport(id);
                        case 'export-lapkeu': {
                            const s = $id('lapkeuStart').value,
                                en = $id('lapkeuEnd').value;
                            if (!s || !en) return alert('Masukkan tahun awal dan akhir.');
                            if (parseInt(en) < parseInt(s)) return alert(
                                'Tahun akhir harus >= tahun awal.');
                            return location.href = `${EXPORT.lapkeu}?year_start=${s}&year_end=${en}`;
                        }
                        case 'export-income': {
                            const y = askYear();
                            return y && (location.href = `${EXPORT.income}?year_start=${y}&year_end=${y}`);
                        }
                        case 'export-kas': {
                            const y = askYear();
                            return y && (location.href = `${EXPORT.kas}?year=${y}`);
                        }
                        case 'export-kas-pdf': {
                            const y = askYear();
                            return y && (location.href = `${EXPORT.kasPdf}?year=${y}`);
                        }
                    }
                    return;
                }
                if (e.target.closest('a, button')) return;
                const row = e.target.closest('.folder-row');
                if (row) toggleFolder(row);
            });
        });
    </script>
@endsection
