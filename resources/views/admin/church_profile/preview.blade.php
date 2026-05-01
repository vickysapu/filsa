@extends('layouts.admin')

@section('title', 'Preview Profile')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="ri-eye-line me-2"></i>Preview Profile</h4>
            <small class="text-muted-custom">Tampilan seperti yang akan dilihat publik</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.church_profile.edit', $churchProfile) }}" class="btn btn-custom">
                <i class="ri-pencil-line me-1"></i>Edit
            </a>
            <a href="{{ route('admin.church_profile.index') }}" class="btn btn-light">Kembali</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-custom text-center">
                <div class="card-body">
                    <img src="{{ $churchProfile->logo_url }}" class="rounded mb-3" style="max-width:150px">
                    <h5 class="fw-bold">{{ $churchProfile->church_name }}</h5>
                    <p class="text-muted">{{ $churchProfile->tagline }}</p>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        @foreach ($churchProfile->social_links as $platform => $url)
                            <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-light">
                                <i class="ri-{{ $platform }}-line"></i>
                            </a>
                        @endforeach
                    </div>
                    <div class="text-start small">
                        <p class="mb-1"><i class="ri-map-pin-line"></i> {{ $churchProfile->full_address }}</p>
                        @if ($churchProfile->phone)
                            <p class="mb-1"><i class="ri-phone-line"></i> {{ $churchProfile->phone }}</p>
                        @endif
                        @if ($churchProfile->whatsapp)
                            <p class="mb-1"><i class="ri-whatsapp-line"></i> {{ $churchProfile->whatsapp }}</p>
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

            @if ($churchProfile->gallery_images)
                <div class="card card-custom mt-4">
                    <div class="card-header">Galeri</div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach ($churchProfile->gallery_urls as $url)
                                <div class="col-4">
                                    <img src="{{ $url }}" class="img-fluid rounded"
                                        style="aspect-ratio:1;object-fit:cover">
                                </div>
                            @endforeach
                        </div>
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

            @if ($churchProfile->values)
                <div class="card card-custom mt-4">
                    <div class="card-header"><i class="ri-heart-line me-1"></i>Nilai-Nilai</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($churchProfile->values as $value)
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <h6 class="fw-bold mb-1">{{ $value['label'] }}</h6>
                                        <small class="text-muted">{{ $value['description'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if (
                $churchProfile->service_time_sunday ||
                    $churchProfile->service_time_wednesday ||
                    $churchProfile->service_time_friday)
                <div class="card card-custom mt-4">
                    <div class="card-header"><i class="ri-calendar-check-line me-1"></i>Jadwal Ibadah</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if ($churchProfile->service_time_sunday)
                                <div class="col-md-4"><strong>Minggu:</strong>
                                    {{ date('H:i', strtotime($churchProfile->service_time_sunday)) }} WIB</div>
                            @endif
                            @if ($churchProfile->service_time_wednesday)
                                <div class="col-md-4"><strong>Rabu:</strong>
                                    {{ date('H:i', strtotime($churchProfile->service_time_wednesday)) }} WIB</div>
                            @endif
                            @if ($churchProfile->service_time_friday)
                                <div class="col-md-4"><strong>Jumat:</strong>
                                    {{ date('H:i', strtotime($churchProfile->service_time_friday)) }} WIB</div>
                            @endif
                        </div>
                        @if ($churchProfile->service_note)
                            <p class="mt-3 mb-0 small text-muted">{{ $churchProfile->service_note }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($churchProfile->map_embed_code)
                <div class="card card-custom mt-4">
                    <div class="card-header"><i class="ri-map-pin-line me-1"></i>Lokasi</div>
                    <div class="card-body">
                        {!! $churchProfile->map_embed_code !!}
                    </div>
                </div>
            @endif

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
@endsection
