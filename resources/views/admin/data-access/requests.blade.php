@extends('layouts.admin')

@section('title', 'Data Access Requests')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0"><i class="fas fa-shield-alt text-primary me-2"></i>Data Access Requests</h2>
                        <p class="text-muted mb-0">Manage permintaan akses data eksternal</p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h3 class="mb-0">{{ $requests->where('status', 'pending')->count() }}</h3>
                            </div>
                            <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Approved</h6>
                                <h3 class="mb-0">{{ $requests->where('status', 'approved')->count() }}</h3>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Rejected</h6>
                                <h3 class="mb-0">{{ $requests->where('status', 'rejected')->count() }}</h3>
                            </div>
                            <i class="fas fa-times-circle fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total</h6>
                                <h3 class="mb-0">{{ $requests->total() }}</h3>
                            </div>
                            <i class="fas fa-inbox fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Permintaan</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width: auto;" id="filterStatus">
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Requester</th>
                                <th>Project</th>
                                <th>Tables Requested</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr class="{{ $request->status == 'pending' ? 'table-warning' : '' }}">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($request->requester_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $request->requester_name }}</div>
                                                <small class="text-muted">{{ $request->requester_email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $request->project_name ?: 'N/A' }}</div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 250px;"
                                            title="{{ $request->purpose }}">
                                            {{ Str::limit($request->purpose, 50) }}
                                        </small>
                                    </td>
                                    <td>
                                        @foreach ($request->requested_tables as $table)
                                            <span class="badge bg-info text-dark me-1 mb-1">{{ $table }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'pending' => [
                                                    'class' => 'warning',
                                                    'icon' => 'clock',
                                                    'label' => 'Pending',
                                                ],
                                                'verified' => [
                                                    'class' => 'info',
                                                    'icon' => 'check',
                                                    'label' => 'Verified',
                                                ],
                                                'approved' => [
                                                    'class' => 'success',
                                                    'icon' => 'check-circle',
                                                    'label' => 'Approved',
                                                ],
                                                'rejected' => [
                                                    'class' => 'danger',
                                                    'icon' => 'times-circle',
                                                    'label' => 'Rejected',
                                                ],
                                                'revoked' => [
                                                    'class' => 'secondary',
                                                    'icon' => 'ban',
                                                    'label' => 'Revoked',
                                                ],
                                            ];
                                            $config = $statusConfig[$request->status] ?? $statusConfig['pending'];
                                        @endphp
                                        <span class="badge bg-{{ $config['class'] }}">
                                            <i class="fas fa-{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
                                        </span>
                                        @if ($request->isVerified())
                                            <br><small class="text-success"><i class="fas fa-envelope-check"></i> Email
                                                verified</small>
                                        @else
                                            <br><small class="text-warning"><i class="fas fa-envelope"></i> Email not
                                                verified</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $request->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.data-access.requests.show', $request) }}"
                                                class="btn btn-primary" title="Review Request">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                            @if ($request->status == 'pending' || $request->status == 'verified')
                                                <button type="button" class="btn btn-success"
                                                    onclick="quickApprove('{{ $request->id }}')" title="Quick Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p>Tidak ada permintaan akses data</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($requests->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('filterStatus').addEventListener('change', function() {
                const url = new URL(window.location);
                if (this.value) {
                    url.searchParams.set('status', this.value);
                } else {
                    url.searchParams.delete('status');
                }
                window.location = url;
            });

            function quickApprove(requestId) {
                if (confirm('Approve permintaan ini dengan konfigurasi default?')) {
                    window.location.href = `/admin/data-access/requests/${requestId}`;
                }
            }
        </script>
    @endpush
@endsection
