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

                    <div class="d-flex justify-content-center gap-2 mb-3">
                        @foreach ($churchProfile->social_links as $platform => $url)
                            <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-light">
                                <i class="ri-{{ $platform }}-line"></i>
                            </a>
                        @endforeach
                    </div>

                    <div class="text-start small">
                        @if ($churchProfile->address || $churchProfile->city)
                            <p class="mb-1"><i class="ri-map-pin-line"></i>
                                {{ collect([$churchProfile->address, $churchProfile->city])->filter()->implode(', ') }}
                            </p>
                        @endif
                        @if ($churchProfile->phone)
                            <p class="mb-1"><i class="ri-phone-line"></i> {{ $churchProfile->phone }}</p>
                        @endif
                        @if ($churchProfile->email)
                            <p class="mb-0"><i class="ri-mail-line"></i> {{ $churchProfile->email }}</p>
                        @endif
                    </div>
                </div>
            </div>
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
        </div>
    </div>
@endsection