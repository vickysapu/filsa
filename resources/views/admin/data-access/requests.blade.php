@extends('layouts.admin')

@section('title', 'Data Access Requests')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">

                        </li>
                        <li class="breadcrumb-item active">Data Access Requests</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold mb-1">
                    <i class="ri-shield-line me-2 text-primary"></i>Data Access Requests
                </h1>
                <p class="text-muted-custom mb-0">Manage permintaan akses data eksternal</p>
            </div>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert-custom success mb-4" data-auto-close>
                <i class="ri-checkbox-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <!-- Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted-custom small text-uppercase fw-semibold mb-1">Pending</p>
                            <p class="h3 mb-0 text-warning">{{ $requests->where('status', 'pending')->count() }}</p>
                        </div>
                        <i class="ri-time-line fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted-custom small text-uppercase fw-semibold mb-1">Approved</p>
                            <p class="h3 mb-0 text-success">{{ $requests->where('status', 'approved')->count() }}</p>
                        </div>
                        <i class="ri-checkbox-circle-line fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card border-start border-4 border-danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted-custom small text-uppercase fw-semibold mb-1">Rejected</p>
                            <p class="h3 mb-0 text-danger">{{ $requests->where('status', 'rejected')->count() }}</p>
                        </div>
                        <i class="ri-close-circle-line fs-1 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted-custom small text-uppercase fw-semibold mb-1">Total</p>
                            <p class="h3 mb-0 text-primary">{{ $requests->total() }}</p>
                        </div>
                        <i class="ri-inbox-line fs-1 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="card-custom">
            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-list-check me-2 text-primary"></i>Daftar Permintaan
                    </h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-custom" id="filterStatus"
                            style="width: auto; min-width: 150px;">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified
                            </option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Requester</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Project</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tables
                                Requested</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Submitted</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr class="{{ $request->status == 'pending' ? 'table-warning-custom' : '' }}">
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="requester-avatar">
                                            {{ strtoupper(substr($request->requester_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $request->requester_name }}</div>
                                            <small class="text-muted-custom">{{ $request->requester_email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-medium">{{ $request->project_name ?: 'N/A' }}</div>
                                    <small class="text-muted-custom text-truncate d-block" style="max-width: 200px;"
                                        title="{{ $request->purpose }}">
                                        {{ Str::limit($request->purpose, 50) }}
                                    </small>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($request->requested_tables as $table)
                                            <span class="badge badge-table-request">{{ $table }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $statusConfig = [
                                            'pending' => [
                                                'class' => 'badge-status-pending',
                                                'icon' => 'time-line',
                                                'label' => 'Pending',
                                            ],
                                            'verified' => [
                                                'class' => 'badge-status-verified',
                                                'icon' => 'checkbox-line',
                                                'label' => 'Verified',
                                            ],
                                            'approved' => [
                                                'class' => 'badge-status-approved',
                                                'icon' => 'checkbox-circle-line',
                                                'label' => 'Approved',
                                            ],
                                            'rejected' => [
                                                'class' => 'badge-status-rejected',
                                                'icon' => 'close-circle-line',
                                                'label' => 'Rejected',
                                            ],
                                            'revoked' => [
                                                'class' => 'badge-status-revoked',
                                                'icon' => 'forbid-line',
                                                'label' => 'Revoked',
                                            ],
                                        ];
                                        $config = $statusConfig[$request->status] ?? $statusConfig['pending'];
                                    @endphp
                                    <span class="badge {{ $config['class'] }} mb-1">
                                        <i class="ri-{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
                                    </span>
                                    @if ($request->isVerified())
                                        <br><small class="text-success"><i class="ri-mail-check-line me-1"></i>Email
                                            verified</small>
                                    @else
                                        <br><small class="text-muted-custom"><i class="ri-mail-line me-1"></i>Email not
                                            verified</small>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-medium">{{ $request->created_at->format('d M Y') }}</div>
                                    <small class="text-muted-custom">{{ $request->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.data-access.requests.show', $request) }}"
                                            class="btn-action btn-action-primary" title="Review Request">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if ($request->status == 'pending' || $request->status == 'verified')
                                            <button type="button" class="btn-action btn-action-success"
                                                onclick="quickApprove('{{ $request->id }}')" title="Quick Approve">
                                                <i class="ri-check-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-inbox-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Tidak ada permintaan akses data</h6>
                                        <p class="text-muted-custom small mb-0">Permintaan akses akan muncul disini setelah
                                            diajukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($requests->hasPages())
                <div class="d-flex justify-content-center p-4 border-top border-custom">
                    {{ $requests->links('pagination::simple-bootstrap-4') }}
                </div>
            @endif
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

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.25rem;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .h3 {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
        }

        /* ===== REQUESTER AVATAR ===== */
        .requester-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* ===== TABLE REQUEST BADGE ===== */
        .badge-table-request {
            background: var(--bg-body);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 0.25em 0.5em;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-pending {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-verified {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
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

        .badge-status-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-revoked {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
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

        /* ===== FORM SELECT ===== */
        .form-select-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 2rem 0.5rem 0.875rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .form-select-custom:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        /* ===== ALERT CUSTOM ===== */
        .alert-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            animation: slideIn 0.3s ease;
        }

        .alert-custom.success {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .alert-custom i {
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
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

        /* ===== TABLE WARNING ROW ===== */
        .table-warning-custom {
            background: rgba(245, 158, 11, 0.05);
        }

        .table-warning-custom:hover {
            background: rgba(245, 158, 11, 0.1);
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

        [data-theme="dark"] .stat-card {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .badge-table-request {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .table-warning-custom {
            background: rgba(245, 158, 11, 0.08);
        }

        [data-theme="dark"] .table-warning-custom:hover {
            background: rgba(245, 158, 11, 0.12);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .stat-card .h3 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 900px;
            }

            .d-flex.flex-wrap.gap-1 {
                flex-direction: column;
            }

            .d-flex.flex-wrap.gap-1 .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== FILTER STATUS CHANGE =====
            document.getElementById('filterStatus')?.addEventListener('change', function() {
                const url = new URL(window.location);
                if (this.value) {
                    url.searchParams.set('status', this.value);
                } else {
                    url.searchParams.delete('status');
                }
                window.location = url;
            });

            // ===== QUICK APPROVE =====
            window.quickApprove = function(requestId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'question',
                        title: 'Quick Approve?',
                        text: 'Approve permintaan ini dengan konfigurasi default?',
                        showCancelButton: true,
                        confirmButtonColor: '#22c55e',
                        confirmButtonText: 'Ya, Approve',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = `/admin/data-access/requests/${requestId}`;
                        }
                    });
                } else {
                    if (confirm('Approve permintaan ini dengan konfigurasi default?')) {
                        window.location.href = `/admin/data-access/requests/${requestId}`;
                    }
                }
            }

            // ===== AUTO-CLOSE ALERTS =====
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
@endsection
