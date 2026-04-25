@forelse($inventaris as $item)
    <tr style="border-bottom: 1px solid #f1f5f9;" 
        onmouseover="this.style.background='#f8fafc'" 
        onmouseout="this.style.background='white'">
        
        <td style="padding: 15px;">{{ $item->branch->name ?? '—' }}</td>
        <td style="padding: 15px; font-weight: 500;">{{ $item->nama_barang }}</td>
        <td style="padding: 15px;">{{ $item->kategori }}</td>
        <td style="padding: 15px;">
            <span class="lux-badge 
                {{ $item->kondisi === 'Baik' ? 'lux-badge-success' : 
                   ($item->kondisi === 'Rusak Ringan' ? 'lux-badge-warning' : 'lux-badge-danger') }}">
                {{ $item->kondisi }}
            </span>
        </td>
        <td style="padding: 15px; text-align: center;">
            <div style="display: flex; gap: 8px; justify-content: center;">
                <button type="button" class="lux-btn lux-btn-primary lux-btn-sm btn-edit-inventaris" 
                        data-id="{{ $item->id }}">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="lux-btn lux-btn-danger lux-btn-sm btn-delete-inventaris" 
                        data-id="{{ $item->id }}">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" style="padding: 4rem; text-align: center; color: var(--text-muted);">
            <i class="ri-inbox-line" style="font-size: 3.5rem; opacity: 0.3;"></i>
            <p>Belum ada data inventaris</p>
        </td>
    </tr>
@endforelse