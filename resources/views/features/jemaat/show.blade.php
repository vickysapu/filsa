@extends('layouts.admin')

@section('title', 'Detail Jemaat')

@section('content')
<div class="lux-container">
    
    <!-- Header -->
    <div class="mb-5">
        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
            <a href="{{ route('admin.jemaat.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <div style="display: flex; gap: 0.75rem;">
                @if(auth()->user()->hasPermission('jemaat.edit'))
                <a href="{{ route('admin.jemaat.edit', $jemaat) }}" class="lux-btn lux-btn-primary">
                    <i class="ri-pencil-line"></i> Edit
                </a>
                @endif
                @if(auth()->user()->hasPermission('jemaat.delete'))
                <form action="{{ route('admin.jemaat.destroy', $jemaat) }}" method="POST" 
                      onsubmit="return confirm('Yakin ingin menghapus data jemaat {{ $jemaat->nama_lengkap }}?')" 
                      style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="lux-btn lux-btn-outline" style="border-color: var(--danger); color: var(--danger);">
                        <i class="ri-delete-bin-line"></i> Hapus
                    </button>
                </form>
                @endif
            </div>
        </div>
        
        <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Detail Jemaat</h1>
    </div>

    <div class="lux-grid lux-grid-3" style="gap: 1.5rem;">
        
        <!-- Profile Card -->
        <div class="lux-card" style="grid-column: span 1; text-align: center;">
            <div style="position: relative; display: inline-block; margin-bottom: 1.5rem;">
                @if($jemaat->foto)
                <img src="{{ asset('storage/' . $jemaat->foto) }}" alt="{{ $jemaat->nama_lengkap }}" 
                     style="width: 180px; height: 180px; object-fit: cover; border-radius: 50%; 
                            border: 5px solid white; box-shadow: var(--shadow-lg);">
                @else
                <div style="width: 180px; height: 180px; border-radius: 50%; 
                            background: linear-gradient(135deg, var(--primary), var(--accent)); 
                            display: flex; align-items: center; justify-content: center; 
                            color: white; font-size: 4rem; font-weight: 700; 
                            border: 5px solid white; box-shadow: var(--shadow-lg);">
                    {{ substr($jemaat->nama_lengkap, 0, 1) }}
                </div>
                @endif
                
                <!-- Status Badge -->
                <span class="lux-badge {{ $jemaat->status_jemaat ? 'lux-badge-success' : 'lux-badge-danger' }}" 
                      style="position: absolute; bottom: 10px; right: 10px; 
                             border: 3px solid white; font-size: 0.75rem; padding: 5px 12px;">
                    {{ $jemaat->status_jemaat ? 'Aktif' : 'Non-Aktif' }}
                </span>
            </div>
            
            <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">
                {{ $jemaat->nama_lengkap }}
            </h2>
            
            <p class="text-muted" style="margin-bottom: 1.5rem;">
                NIK: {{ number_format($jemaat->NIK, 0, ',', '.') }}
            </p>
            
            <!-- Quick Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: left; 
                        padding: 1rem; background: #f8fafc; border-radius: var(--radius); margin-bottom: 1.5rem;">
                <div>
                    <p class="lux-label" style="margin-bottom: 0.25rem;">Jenis Kelamin</p>
                    <p style="font-weight: 600;">{{ $jemaat->jenis_kelamin ? 'Laki-laki' : 'Perempuan' }}</p>
                </div>
                <div>
                    <p class="lux-label" style="margin-bottom: 0.25rem;">Usia</p>
                    <p style="font-weight: 600;">{{ $jemaat->tanggal_lahir ? $jemaat->tanggal_lahir->age : '-' }} tahun</p>
                </div>
                <div>
                    <p class="lux-label" style="margin-bottom: 0.25rem">Status</p>
                    <p style="font-weight: 600; text-transform: capitalize;">{{ ucfirst($jemaat->status_pernikahan) }}</p>
                </div>
                <div>
                    <p class="lux-label" style="margin-bottom: 0.25rem">Bergabung</p>
                    <p style="font-weight: 600;">{{ $jemaat->tanggal_bergabung->format('M Y') }}</p>
                </div>
            </div>

            <!-- Contact Quick Access -->
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @if($jemaat->no_hp)
                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $jemaat->no_hp) }}" 
                   target="_blank" class="lux-btn lux-btn-outline" style="border-color: #25D366; color: #25D366;">
                    <i class="ri-whatsapp-line"></i> Chat WhatsApp
                </a>
                @endif
                @if($jemaat->email)
                <a href="mailto:{{ $jemaat->email }}" class="lux-btn lux-btn-outline">
                    <i class="ri-mail-line"></i> Kirim Email
                </a>
                @endif
            </div>
        </div>

        <!-- Detail Information -->
        <div class="lux-card" style="grid-column: span 2;">
            
            <!-- Personal Information -->
            <div class="lux-card-header" style="margin-bottom: 1.5rem;">
                <h3 class="lux-card-title">Informasi Pribadi</h3>
                <i class="ri-user-line" style="color: var(--primary);"></i>
            </div>
            
            <div class="lux-grid lux-grid-2" style="gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <p class="lux-label">Tempat, Tanggal Lahir</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">
                        {{ $jemaat->tempat_lahir }}, {{ $jemaat->tanggal_lahir->format('d F Y') }}
                    </p>
                </div>
                <div>
                    <p class="lux-label">Usia</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">{{ $jemaat->tanggal_lahir->age }} tahun</p>
                </div>
                <div>
                    <p class="lux-label">Jenis Kelamin</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">{{ $jemaat->jenis_kelamin ? 'Laki-laki' : 'Perempuan' }}</p>
                </div>
                <div>
                    <p class="lux-label">Status Pernikahan</p>
                    <span class="lux-badge" style="background: #f1f5f9; color: var(--text-main);">
                        {{ ucfirst($jemaat->status_pernikahan) }}
                    </span>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;">

            <!-- Contact Information -->
            <div class="lux-card-header" style="margin-bottom: 1.5rem;">
                <h3 class="lux-card-title">Informasi Kontak</h3>
                <i class="ri-phone-line" style="color: var(--accent);"></i>
            </div>
            
            <div class="lux-grid lux-grid-2" style="gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <p class="lux-label">Nomor Telepon</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">
                        <a href="tel:{{ $jemaat->no_hp }}" style="color: var(--primary); text-decoration: none;">
                            {{ $jemaat->no_hp }}
                        </a>
                    </p>
                </div>
                <div>
                    <p class="lux-label">Email</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">
                        @if($jemaat->email)
                            <a href="mailto:{{ $jemaat->email }}" style="color: var(--primary); text-decoration: none;">
                                {{ $jemaat->email }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div style="grid-column: span 2;">
                    <p class="lux-label">Alamat Lengkap</p>
                    <p style="font-weight: 600; font-size: 0.95rem; line-height: 1.6;">
                        {{ $jemaat->alamat }}
                    </p>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;">

            <!-- Education & Work -->
            <div class="lux-card-header" style="margin-bottom: 1.5rem;">
                <h3 class="lux-card-title">Pendidikan & Pekerjaan</h3>
                <i class="ri-briefcase-line" style="color: var(--primary);"></i>
            </div>
            
            <div class="lux-grid lux-grid-2" style="gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <p class="lux-label">Pendidikan Terakhir</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">{{ $jemaat->pendidikan ?? '-' }}</p>
                </div>
                <div>
                    <p class="lux-label">Pekerjaan</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">{{ $jemaat->pekerjaan ?? '-' }}</p>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;">

            <!-- Church Information -->
            <div class="lux-card-header" style="margin-bottom: 1.5rem;">
                <h3 class="lux-card-title">Data Gerejawi</h3>
                <i class="ri-church-line" style="color: var(--accent);"></i>
            </div>
            
            <div class="lux-grid lux-grid-2" style="gap: 1.5rem;">
                <div>
                    <p class="lux-label">Tanggal Bergabung</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">
                        {{ $jemaat->tanggal_bergabung->format('d F Y') }}
                    </p>
                    <p class="text-muted" style="font-size: 0.85rem;">
                        {{ $jemaat->tanggal_bergabung->diffForHumans() }}
                    </p>
                </div>
                <div>
                    <p class="lux-label">Status Jemaat</p>
                    <span class="lux-badge {{ $jemaat->status_jemaat ? 'lux-badge-success' : 'lux-badge-danger' }}">
                        {{ $jemaat->status_jemaat ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;">

            <!-- System Information -->
            <div class="lux-card-header" style="margin-bottom: 1.5rem;">
                <h3 class="lux-card-title">Informasi Sistem</h3>
                <i class="ri-information-line" style="color: var(--text-muted);"></i>
            </div>
            
            <div class="lux-grid lux-grid-2" style="gap: 1.5rem;">
                <div>
                    <p class="lux-label">Dibuat Pada</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">
                        {{ $jemaat->created_at->format('d F Y, H:i') }}
                    </p>
                </div>
                <div>
                    <p class="lux-label">Terakhir Diupdate</p>
                    <p style="font-weight: 600; font-size: 0.95rem;">
                        {{ $jemaat->updated_at->format('d F Y, H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection