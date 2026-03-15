@if ($pemasukans->isEmpty())
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="ri-inbox-line" style="font-size: 4rem; opacity: 0.3;"></i>
            <p class="mt-3">Belum ada data pemasukan</p>
            <button type="button" class="lux-btn lux-btn-primary mt-3 btn-tambah-pemasukan">
                <i class="ri-add-line"></i> Tambah Pemasukan Pertama
            </button>
        </td>
    </tr>
@else
    @foreach ($pemasukans as $pemasukan)
        <tr style="border-bottom: 1px solid #f1f5f9;">
            <td class="py-4">
                <div class="fw-semibold">{{ $pemasukan->tanggal->format('d M Y') }}</div>
                <small class="text-muted">{{ $pemasukan->tanggal->diffForHumans() }}</small>
            </td>
            <td class="py-4">
                <code class="fw-bold text-primary">{{ $pemasukan->nomor_transaksi }}</code>
            </td>
            <td class="py-4">
                <span class="lux-badge" style="background: #e0e7ff; color: #3730a3;">
                    {{ ucwords(str_replace('_', ' ', $pemasukan->kategori)) }}
                </span>
            </td>
            <td class="py-4">
                <div class="fw-medium">{{ $pemasukan->sumber_dana }}</div>
                <small class="text-muted">{{ $pemasukan->creator->name ?? '-' }}</small>
            </td>
            <td class="py-4 text-end">
                <div class="fw-bold text-success">
                    Rp {{ number_format($pemasukan->jumlah, 0, ',', '.') }}
                </div>
            </td>
            <td class="py-4 text-center">
                @if ($pemasukan->status === 'verified')
                    <span class="lux-badge bg-success text-white">Verified</span>
                @elseif ($pemasukan->status === 'pending')
                    <span class="lux-badge bg-warning text-dark">Pending</span>
                @else
                    <span class="lux-badge bg-danger text-white">Ditolak</span>
                @endif
            </td>
            <td class="py-4 text-center">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('admin.pemasukan.show', $pemasukan) }}" class="lux-btn lux-btn-outline lux-btn-sm">
                        <i class="ri-eye-line"></i>
                    </a>

                    @if ($pemasukan->status === 'pending' && auth()->user()->can('pemasukan.edit'))
                        <button class="lux-btn lux-btn-primary lux-btn-sm btn-edit-pemasukan"
                                data-id="{{ $pemasukan->id }}"
                                data-keterangan="{{ $pemasukan->sumber_dana }}">
                            <i class="ri-pencil-line"></i>
                        </button>
                    @endif

                    @if ($pemasukan->status === 'pending' && auth()->user()->can('pemasukan.verify'))
                        <form action="{{ route('admin.pemasukan.verify', $pemasukan) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="lux-btn lux-btn-outline lux-btn-sm" style="border-color: var(--success); color: var(--success);">
                                <i class="ri-checkbox-circle-line"></i>
                            </button>
                        </form>
                    @endif

                    @if ($pemasukan->status === 'pending' && auth()->user()->can('pemasukan.delete'))
                        <button class="lux-btn lux-btn-outline lux-btn-sm btn-delete-pemasukan"
                                style="border-color: var(--danger); color: var(--danger);"
                                data-id="{{ $pemasukan->id }}"
                                data-keterangan="{{ $pemasukan->sumber_dana }}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@endif