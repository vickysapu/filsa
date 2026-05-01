@extends('layouts.admin')

@section('title', 'Detail Pelayanan - ' . $pelayanan->nama_pelayanan)

@section('content')
    @php
        $statusMap = [
            'draft' => ['label' => 'Draft', 'bs' => 'warning'],
            'active' => ['label' => 'Aktif', 'bs' => 'success'],
            'completed' => ['label' => 'Selesai', 'bs' => 'primary'],
            'cancelled' => ['label' => 'Dibatalkan', 'bs' => 'danger'],
        ];
        $st = $statusMap[$pelayanan->status] ?? ['label' => ucfirst($pelayanan->status), 'bs' => 'secondary'];
        $aktif = $pelayanan->details->where('status', 'active');
        $off = $pelayanan->details->where('status', 'off');
    @endphp

    <div class="container py-4" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">{{ $pelayanan->nama_pelayanan }}</h4>
                <div class="text-muted-custom small">{{ $pelayanan->nomor_jadwal }}</div>
            </div>
            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ri-arrow-left-line"></i>
            </a>
        </div>
        <div class="mb-3">
            <span class="badge bg-{{ $st['bs'] }}">{{ $st['label'] }}</span>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-muted-custom small">Tanggal</div>
                        <div class="fw-semibold">{{ $pelayanan->tanggal->format('d M Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted-custom small">Waktu</div>
                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted-custom small">Lokasi</div>
                        <div class="fw-semibold">{{ $pelayanan->lokasi }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Petugas Aktif</h6>
                <span class="badge bg-success">{{ $aktif->count() }}</span>
            </div>
            <div class="list-group">
                @foreach ($aktif as $d)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $d->jemaat->nama_lengkap }}</div>
                            <div class="small text-muted-custom">
                                {{ $d->kategori->nama_kategori ?? '-' }}
                            </div>
                            @if ($d->role)
                                <span class="badge bg-primary mt-1">{{ $d->role }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($off->count())
            <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Petugas OFF</h6>
                    <span class="badge bg-danger">{{ $off->count() }}</span>
                </div>
                <div class="list-group">
                    @foreach ($off as $d)
                        <div class="list-group-item">
                            <div class="fw-semibold text-decoration-line-through text-muted-custom">
                                {{ $d->jemaat->nama_lengkap }}
                            </div>
                            @if ($d->alasan_off)
                                <div class="small text-danger">
                                    {{ $d->alasan_off }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
