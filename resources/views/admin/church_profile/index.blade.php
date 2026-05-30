@extends('layouts.admin')

@section('title', 'Profile Gereja')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="ri-building-line me-2"></i>Profile Gereja</h4>
            <small class="text-muted-custom">Informasi identitas gereja Anda</small>
        </div>
        <div class="d-flex gap-2">
            @if ($churchProfile)
                <a href="{{ route('admin.church_profile.preview', $churchProfile) }}" class="btn btn-light">
                    <i class="ri-eye-line me-1"></i>Preview Publik
                </a>
                <a href="{{ route('admin.church_profile.edit', $churchProfile) }}" class="btn btn-custom">
                    <i class="ri-pencil-line me-1"></i>Edit Profile
                </a>
                @can('church_profile.delete')
                    <button class="btn btn-outline-danger"
                        onclick="confirmDelete('{{ $churchProfile->id }}', '{{ addslashes($churchProfile->church_name) }}')">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                @endcan
            @else
                <a href="{{ route('admin.church_profile.create') }}" class="btn btn-custom">
                    <i class="ri-add-line me-1"></i>Buat Profile Pertama
                </a>
            @endif
        </div>
    </div>

    @if (!$churchProfile)
        <div class="card card-custom text-center py-5">
            <div class="card-body">
                <i class="ri-building-line fs-1 text-muted mb-3"></i>
                <h5 class="mb-3">Belum ada profile gereja</h5>
                <p class="text-muted mb-4">Silakan buat profile gereja untuk mulai mengelola informasi publik.</p>
                <a href="{{ route('admin.church_profile.create') }}" class="btn btn-custom">
                    <i class="ri-add-line me-1"></i>Buat Profile Sekarang
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-custom text-center">
                    <div class="card-body">
                        <img src="{{ $churchProfile->logo_url }}" class="rounded mb-3" style="max-width:150px">
                        <h5 class="fw-bold">{{ $churchProfile->church_name }}</h5>
                        <p class="text-muted">{{ $churchProfile->tagline ?: '-' }}</p>

                        <div class="d-flex justify-content-center gap-2 mb-3">
                            @foreach ($churchProfile->social_links as $platform => $url)
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-light">
                                    <i class="ri-{{ $platform }}-line"></i>
                                </a>
                            @endforeach
                        </div>

                        <div class="text-start small">
                            <p class="mb-1"><i class="ri-map-pin-line"></i> {{ $churchProfile->address ?: '-' }}</p>
                            @if ($churchProfile->phone)
                                <p class="mb-1"><i class="ri-phone-line"></i> {{ $churchProfile->phone }}</p>
                            @endif
                            @if ($churchProfile->email)
                                <p class="mb-1"><i class="ri-mail-line"></i> {{ $churchProfile->email }}</p>
                            @endif
                            @if ($churchProfile->website)
                                <p class="mb-0"><i class="ri-global-line"></i> <a href="{{ $churchProfile->website }}"
                                        target="_blank">{{ $churchProfile->website }}</a></p>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($churchProfile->map_embed_code)
                    <div class="card card-custom mt-4">
                        <div class="card-header"><i class="ri-map-pin-line me-1"></i>Lokasi</div>
                        <div class="card-body">
                            {!! $churchProfile->map_embed_code !!}
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-8">
                @if ($churchProfile->history)
                    <div class="card card-custom mb-4">
                        <div class="card-header"><i class="ri-time-line me-1"></i>Sejarah</div>
                        <div class="card-body">{!! nl2br(e($churchProfile->history)) !!}</div>
                    </div>
                @endif

                <div class="row g-4">
                    @if ($churchProfile->vision)
                        <div class="col-md-6">
                            <div class="card card-custom h-100">
                                <div class="card-header"><i class="ri-eye-line me-1"></i>Visi</div>
                                <div class="card-body">{!! nl2br(e($churchProfile->vision)) !!}</div>
                            </div>
                        </div>
                    @endif
                    @if ($churchProfile->mission)
                        <div class="col-md-6">
                            <div class="card card-custom h-100">
                                <div class="card-header"><i class="ri-target-line me-1"></i>Misi</div>
                                <div class="card-body">{!! nl2br(e($churchProfile->mission)) !!}</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($churchProfile->documents)
                    <div class="card card-custom mt-4">
                        <div class="card-header"><i class="ri-file-list-line me-1"></i>Dokumen</div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach ($churchProfile->documents as $doc)
                                    <a href="{{ Storage::url($doc) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                        target="_blank">
                                        <span><i class="ri-file-line me-2"></i>{{ basename($doc) }}</span>
                                        <i class="ri-download-line"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            const confirmDelete = (id, name) => {
                if (!confirm(`Hapus profile "${name}"? Aksi ini tidak dapat dibatalkan.`)) return;
                fetch(`/admin/church-profile/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.message || 'Gagal menghapus');
                    })
                    .catch(() => alert('Kesalahan koneksi'));
            };
        </script>
    @endpush
@endsection
