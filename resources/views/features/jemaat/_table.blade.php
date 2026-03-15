<!-- resources/views/admin/jemaat/_table.blade.php -->

@forelse($jemaats as $jemaat)
    <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;"
        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
        <td style="padding: 15px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                @if ($jemaat->foto)
                    <img src="{{ asset('storage/' . $jemaat->foto) }}"
                         alt="{{ $jemaat->nama_lengkap }}"
                         style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: var(--shadow-sm);">
                @else
                    <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                         display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem;">
                        {{ substr($jemaat->nama_lengkap, 0, 1) }}
                    </div>
                @endif
                <div>
                    <div style="font-weight: 600; color: var(--text-main);">{{ $jemaat->nama_lengkap }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        NIK: {{ number_format($jemaat->NIK, 0, ',', '.') }} • {{ $jemaat->jenis_kelamin == 1 ? 'L' : 'P' }}
                    </div>
                </div>
            </div>
        </td>
        <td style="padding: 15px;">
            <div style="font-size: 0.9rem; font-weight: 500;">{{ $jemaat->no_hp }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $jemaat->email ?? '-' }}</div>
        </td>
        <td style="padding: 15px;">
            <div style="margin-bottom: 5px;">
                @if ($jemaat->status_jemaat)
                    <span class="lux-badge lux-badge-success">Aktif</span>
                @else
                    <span class="lux-badge lux-badge-danger">Non-Aktif</span>
                @endif
            </div>
            <div>
                <span class="lux-badge" style="background: #f1f5f9; color: var(--text-muted); font-size: 0.7rem;">
                    {{ ucfirst($jemaat->status_pernikahan) }}
                </span>
            </div>
        </td>
        <td style="padding: 15px;">
            <div style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($jemaat->tanggal_bergabung)->format('d M Y') }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">
                {{ \Carbon\Carbon::parse($jemaat->tanggal_bergabung)->diffForHumans() }}
            </div>
        </td>
        <td style="padding: 15px; text-align: center;">
            <div style="display: flex; gap: 8px; justify-content: center;">
                <a href="{{ route('admin.jemaat.show', $jemaat) }}" class="lux-btn lux-btn-outline lux-btn-sm" title="Detail">
                    <i class="ri-eye-line"></i>
                </a>
                <button type="button" class="lux-btn lux-btn-primary lux-btn-sm btn-edit-jemaat" data-id="{{ $jemaat->id }}" title="Edit">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="lux-btn lux-btn-outline lux-btn-sm btn-delete-jemaat"
                        data-id="{{ $jemaat->id }}"
                        data-nama="{{ $jemaat->nama_lengkap }}"
                        style="border-color: var(--danger); color: var(--danger);" title="Hapus">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
            <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 1rem;">Belum ada data jemaat</p>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-jemaat" style="margin-top: 1rem;">
                <i class="ri-user-add-line"></i> Tambah Jemaat Pertama
            </button>
        </td>
    </tr>
@endforelse