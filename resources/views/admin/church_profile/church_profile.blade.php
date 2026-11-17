@extends('layouts.public')

@section('title', $profile->meta_title ?? $profile->church_name)

@section('meta')
    <meta name="description" content="{{ $profile->meta_description }}">
    @if ($profile->meta_keywords)
        <meta name="keywords" content="{{ implode(', ', $profile->meta_keywords) }}">
    @endif
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-center text-lg-start">
                    <img src="{{ $profile->logo_url }}" class="img-fluid rounded mb-3 mb-lg-0" style="max-width:200px">
                </div>
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold">{{ $profile->church_name }}</h1>
                    @if ($profile->tagline)
                        <p class="lead text-muted">{{ $profile->tagline }}</p>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @if ($profile->phone)
                            <a href="tel:{{ $profile->phone }}" class="btn btn-outline-primary btn-sm"><i
                                    class="ri-phone-line"></i> {{ $profile->phone }}</a>
                        @endif
                        @if ($profile->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->whatsapp) }}"
                                class="btn btn-success btn-sm"><i class="ri-whatsapp-line"></i> WhatsApp</a>
                        @endif
                        @foreach ($profile->social_links as $platform => $url)
                            <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i
                                    class="ri-{{ $platform }}-line"></i></a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="ri-map-pin-line me-2"></i>Alamat</h5>
                            <p class="card-text">{{ $profile->full_address }}</p>
                            @if ($profile->map_embed_code)
                                <div class="ratio ratio-16x9 mt-3">{!! $profile->map_embed_code !!}</div>
                            @endif
                        </div>
                    </div>

                    @if ($profile->service_time_sunday || $profile->service_time_wednesday || $profile->service_time_friday)
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h5 class="card-title"><i class="ri-calendar-line me-2"></i>Jadwal Ibadah</h5>
                                <ul class="list-unstyled mb-0">
                                    @if ($profile->service_time_sunday)
                                        <li><strong>Minggu:</strong>
                                            {{ date('H:i', strtotime($profile->service_time_sunday)) }} WIB</li>
                                    @endif
                                    @if ($profile->service_time_wednesday)
                                        <li><strong>Rabu:</strong>
                                            {{ date('H:i', strtotime($profile->service_time_wednesday)) }} WIB</li>
                                    @endif
                                    @if ($profile->service_time_friday)
                                        <li><strong>Jumat:</strong>
                                            {{ date('H:i', strtotime($profile->service_time_friday)) }} WIB</li>
                                    @endif
                                </ul>
                                @if ($profile->service_note)
                                    <small class="text-muted d-block mt-2">{{ $profile->service_note }}</small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-8">
                    @if ($profile->history)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Sejarah Gereja</h5>
                                <div>{!! nl2br(e($profile->history)) !!}</div>
                            </div>
                        </div>
                    @endif

                    <div class="row g-4">
                        @if ($profile->vision)
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Visi</h5>
                                        <p>{!! nl2br(e($profile->vision)) !!}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($profile->mission)
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Misi</h5>
                                        <p>{!! nl2br(e($profile->mission)) !!}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($profile->values)
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Nilai-Nilai Kami</h5>
                                <div class="row g-3">
                                    @foreach ($profile->values as $value)
                                        <div class="col-md-6">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h6 class="fw-bold mb-1">{{ $value['label'] }}</h6>
                                                <small class="text-muted">{{ $value['description'] }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($profile->gallery_images)
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Galeri</h5>
                                <div class="row g-2">
                                    @foreach ($profile->gallery_urls as $url)
                                        <div class="col-4 col-md-3">
                                            <a href="{{ $url }}" data-bs-toggle="modal"
                                                data-bs-target="#galleryModal">
                                                <img src="{{ $url }}" class="img-fluid rounded"
                                                    style="aspect-ratio:1;object-fit:cover">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($profile->gallery_images)
        <div class="modal fade" id="galleryModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <img src="" id="galleryImage" class="img-fluid w-100">
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.querySelectorAll('[data-bs-target="#galleryModal"]').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.getElementById('galleryImage').src = link.href;
                });
            });
        </script>
    @endif
@endsection
