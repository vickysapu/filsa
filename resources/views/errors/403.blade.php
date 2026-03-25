@extends('layouts.public')
@section('title', 'Akses Ditolak')

@section('content')
<div class="lux-container" style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div style="max-width: 500px; width: 100%; text-align: center;">
        
        <div class="lux-card" style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            
            <div style="background: linear-gradient(135deg, #ef4444, #f97316); padding: 2rem; color: white;">
                <i class="ri-shield-cross-line" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">🔐 Akses Ditolak</h2>
            </div>
            
            <div style="padding: 2rem;">
                <p style="margin: 0 0 1.5rem; font-size: 1.05rem; line-height: 1.7; color: var(--brand-dark, #0f172a); opacity: 0.9;">
                    {{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
                </p>
                
                <div style="background: var(--brand-light, #f8fafc); padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.9rem; opacity: 0.8;">
                    <i class="ri-information-line"></i> 
                    Halaman ini hanya dapat diakses melalui link khusus dengan token verifikasi yang valid.
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/" class="lux-btn lux-btn-primary" 
                       style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; text-decoration: none; background: linear-gradient(135deg, var(--brand-primary, #4f46e5), var(--brand-secondary, #06b6d4)); color: white; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-home-line"></i> Kembali ke Beranda
                    </a>
                    <a href="javascript:history.back()" class="lux-btn lux-btn-outline"
                       style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-arrow-left-line"></i> Kembali
                    </a>
                </div>
            </div>
            
        </div>
        
        <p style="margin-top: 2rem; font-size: 0.9rem; opacity: 0.6;">
            Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator.
        </p>
        
    </div>
</div>
@endsection