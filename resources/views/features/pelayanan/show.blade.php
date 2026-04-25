@extends('layouts.admin')

@section('title', 'Detail Pelayanan - ' . $pelayanan->nama_pelayanan)

@section('content')

@php
    $statusMap = [
        'draft'     => ['label' => 'Draft', 'bs' => 'warning'],
        'active'    => ['label' => 'Aktif', 'bs' => 'success'],
        'completed' => ['label' => 'Selesai', 'bs' => 'primary'],
        'cancelled' => ['label' => 'Dibatalkan', 'bs' => 'danger'],
    ];

    $st = $statusMap[$pelayanan->status] ?? ['label' => ucfirst($pelayanan->status), 'bs' => 'secondary'];

    $aktif = $pelayanan->details->where('status','active');
    $off   = $pelayanan->details->where('status','off');
@endphp

<div class="container py-4" style="max-width: 900px;">

    {{-- HEADER --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">{{ $pelayanan->nama_pelayanan }}</h4>
                <div class="text-muted small">{{ $pelayanan->nomor_jadwal }}</div>
            </div>

            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ri-arrow-left-line"></i>
            </a>
        </div>

        <div class="mt-2">
            <span class="badge bg-{{ $st['bs'] }}">
                {{ $st['label'] }}
            </span>
        </div>
    </div>

    {{-- INFO --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap gap-4 small">
            <div>
                <div class="text-muted">Tanggal</div>
                <div class="fw-semibold">{{ $pelayanan->tanggal->format('d M Y') }}</div>
            </div>

            <div>
                <div class="text-muted">Waktu</div>
                <div class="fw-semibold">
                    {{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB
                </div>
            </div>

            <div>
                <div class="text-muted">Lokasi</div>
                <div class="fw-semibold">{{ $pelayanan->lokasi }}</div>
            </div>
        </div>
    </div>

    {{-- PETUGAS AKTIF --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Petugas Aktif</h6>
            <span class="badge bg-success">{{ $aktif->count() }}</span>
        </div>

        <div class="list-group">
            @foreach($aktif as $d)
            <div class="list-group-item d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">{{ $d->jemaat->nama_lengkap }}</div>
                    <div class="small text-muted">
                        {{ $d->kategori->nama_kategori ?? '-' }}
                    </div>

                    @if($d->role)
                        <span class="badge bg-primary mt-1">{{ $d->role }}</span>
                    @endif
                </div>

            </div>
            @endforeach
        </div>
    </div>

    {{-- PETUGAS OFF --}}
    @if($off->count())
    <div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Petugas OFF</h6>
            <span class="badge bg-danger">{{ $off->count() }}</span>
        </div>

        <div class="list-group">
            @foreach($off as $d)
            <div class="list-group-item">

                <div class="fw-semibold text-decoration-line-through text-muted">
                    {{ $d->jemaat->nama_lengkap }}
                </div>

                @if($d->alasan_off)
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