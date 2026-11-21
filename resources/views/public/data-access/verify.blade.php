@extends('layouts.public')
@section('title', 'Verifikasi Token Akses Data')

@section('content')
    <header class="page-hero public-page-hero public-page-hero--gallery">
        <div class="page-hero-container fade-in">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <span>Verifikasi Token</span>
            </nav>
            <span class="page-kicker">Portal Akses Data</span>
            <h1 class="page-hero-title">Verifikasi Token Akses</h1>
            <p class="page-hero-desc">Masukkan token yang dikirim melalui email untuk melanjutkan ke portal data.</p>
        </div>
        <span class="page-image-note">Foto ilustrasi · bukan dokumentasi Gereja Filadelfia</span>
    </header>
    <div class="lux-container"
        style="display: flex; align-items: center; justify-content: center; padding: 3rem 2rem;">
        <div style="max-width: 500px; width: 100%;">

            <div class="lux-card" style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.1);">
                <div style="padding: 2rem;">
                    @if (session('error'))
                        <div
                            style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: #991b1b;">
                            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('data-portal.verify.post') }}">
                        @csrf
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Token Akses</label>
                            <textarea name="token" rows="3" class="lux-input" required placeholder="Paste token Anda di sini..."
                                style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 2px solid #e2e8f0; font-family: monospace; font-size: 0.9rem;">{{ old('token') }}</textarea>
                            <small style="display: block; margin-top: 0.5rem; opacity: 0.7;">
                                <i class="ri-information-line"></i> Token dimulai dengan <code>dap_</code> dan panjangnya
                                ~100 karakter
                            </small>
                        </div>

                        <button type="submit" class="lux-btn lux-btn-primary"
                            style="width: 100%; padding: 1rem; border-radius: 50px; font-weight: 600; background: var(--zaitun-deep, #34432C); color: white; border: none; cursor: pointer; transition: all 0.3s ease;">
                            <i class="ri-lock-unlock-line me-2"></i>Verifikasi & Masuk
                        </button>
                    </form>

                    <div
                        style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
                        <small style="opacity: 0.7;">
                            Tidak punya token? <a href="{{ route('public.data-access.request') }}"
                                style="color: var(--brand-primary); text-decoration: none;">Ajukan akses data</a>
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
