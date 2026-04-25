@foreach($latihans as $latihan)
    <div class="lux-card">
        <div class="lux-card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1">{{ $latihan->nama_latihan }}</h5>
                <small class="text-muted">{{ $latihan->pelayanan?->nama_pelayanan ?? '-' }}</small>
            </div>
            <span class="badge bg-{{ $latihan->status === 'active' ? 'success' : 'danger' }}">
                {{ ucfirst($latihan->status) }}
            </span>
        </div>
        
        <div class="lux-card-body">
            <p>
                <i class="ri-calendar-line"></i> 
                {{ \Carbon\Carbon::parse($latihan->tanggal)->format('d M Y') }}
            </p>
            <p>
                <i class="ri-time-line"></i> 
                {{ \Carbon\Carbon::parse($latihan->waktu)->format('H:i') }}
            </p>
            <p>
                <i class="ri-map-pin-line"></i> 
                {{ $latihan->lokasi ?? 'Gedung Utama' }}
            </p>
        </div>

        <div class="lux-card-footer d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary flex-fill btn-edit-latihan" 
                    data-id="{{ $latihan->id }}">Edit</button>
            <button class="btn btn-sm btn-outline-danger flex-fill btn-delete-latihan" 
                    data-id="{{ $latihan->id }}" 
                    data-nama="{{ addslashes($latihan->nama_latihan) }}">
                Hapus
            </button>
        </div>
    </div>
@endforeach

@if($latihans->isEmpty())
    <div class="col-12 text-center py-5 text-muted">
        Belum ada jadwal latihan pelayanan.
    </div>
@endif