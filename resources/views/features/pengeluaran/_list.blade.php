@if ($pengeluarans->isEmpty())
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="ri-inbox-line" style="font-size: 4rem; opacity: 0.3;"></i>
            <p class="mt-3">Belum ada data pengeluaran</p>
            <button type="button" class="lux-btn lux-btn-danger mt-3 btn-tambah-pengeluaran">
                <i class="ri-add-line"></i> Tambah Pengeluaran Pertama
            </button>
        </td>
    </tr>
@else
    @foreach ($pengeluarans as $pengeluaran)
        <tr style="border-bottom: 1px solid #f1f5f9;">
            <td class="py-4">
                <div class="fw-semibold">{{ $pengeluaran->tanggal->format('d M Y') }}</div>
                <small class="text-muted">{{ $pengeluaran->tanggal->diffForHumans() }}</small>
            </td>
            <td class="py-4">
                <code class="fw-bold text-danger">{{ $pengeluaran->nomor_transaksi }}</code>
            </td>
            <td class="py-4">
                <span class="lux-badge" style="background: #fce7f3; color: #9d174d;">
                    {{ ucwords(str_replace('_', ' ', $pengeluaran->kategori)) }}
                </span>
            </td>
            <td class="py-4">
                <div class="fw-medium">{{ $pengeluaran->keperluan }}</div>
                <small class="text-muted">{{ $pengeluaran->penerima }}</small>
            </td>
            <td class="py-4 text-end">
                <div class="fw-bold text-danger">
                    Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}
                </div>
            </td>
            <td class="py-4 text-center">
                @if ($pengeluaran->status === 'verified')
                    <span class="lux-badge bg-success text-white">Verified</span>
                @elseif ($pengeluaran->status === 'pending')
                    <span class="lux-badge bg-warning text-dark">Pending</span>
                @else
                    <span class="lux-badge bg-danger text-white">Ditolak</span>
                @endif
            </td>
            <td class="py-4 text-center">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('admin.pengeluaran.show', $pengeluaran) }}"
                        class="lux-btn lux-btn-outline lux-btn-sm">
                        <i class="ri-eye-line"></i>
                    </a>

                    @if ($pengeluaran->status === 'pending' && auth()->user()->can('pengeluaran.edit'))
                        <button class="lux-btn lux-btn-danger lux-btn-sm btn-edit-pengeluaran"
                            data-id="{{ $pengeluaran->id }}" data-keterangan="{{ $pengeluaran->keperluan }}">
                            <i class="ri-pencil-line"></i>
                        </button>
                    @endif

                    @if ($pengeluaran->status === 'pending' && auth()->user()->can('pengeluaran.verify'))
                        <form action="{{ route('admin.pengeluaran.verify', $pengeluaran) }}" method="POST"
                            class="d-inline">
                            @csrf
                            <button type="submit" class="lux-btn lux-btn-outline lux-btn-sm"
                                style="border-color: var(--success); color: var(--success);">
                                <i class="ri-checkbox-circle-line"></i>
                            </button>
                        </form>
                    @endif

                    @if ($pengeluaran->status === 'pending' && auth()->user()->can('pengeluaran.delete'))
                        <button class="lux-btn lux-btn-outline lux-btn-sm btn-delete-pengeluaran"
                            style="border-color: var(--danger); color: var(--danger);" data-id="{{ $pengeluaran->id }}"
                            data-keterangan="{{ $pengeluaran->keperluan }}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@endif
