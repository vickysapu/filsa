@extends('layouts.admin')

@section('title', 'Jadwal Latihan Pelayanan')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Jadwal Latihan Pelayanan</h1>
                <p class="text-muted">Kelola jadwal latihan untuk pelayanan yang sudah ada</p>
            </div>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-latihan">
                <i class="ri-calendar-add-line"></i> Buat Jadwal Latihan Baru
            </button>
        </div>

        @if (session('success'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--success); background: #f0fdf4;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter -->
        <div class="lux-card mb-4">
            <form id="formFilter" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Pelayanan</label>
                    <select id="pelayanan_id" class="lux-select">
                        <option value="">Semua Pelayanan</option>
                        @foreach($pelayanans as $p)
                            <option value="{{ $p->id }}" {{ request('pelayanan_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_pelayanan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lux-label">Dari Tanggal</label>
                    <input type="date" id="tanggal_start" class="lux-input" value="{{ request('tanggal_start') }}">
                </div>
                <div>
                    <label class="lux-label">Sampai Tanggal</label>
                    <input type="date" id="tanggal_end" class="lux-input" value="{{ request('tanggal_end') }}">
                </div>
                <div>
                    <label class="lux-label">Status</label>
                    <select id="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" id="search" class="lux-input" value="{{ request('search') }}" placeholder="Nama latihan...">
                </div>
                <div style="align-self: end;">
                    <button type="button" id="btnFilter" class="lux-btn lux-btn-primary">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="lux-grid lux-grid-3" id="latihanList">
            @include('features.latihan-pelayanan._list')
        </div>

        <div id="paginationContainer" class="mt-4 d-flex justify-content-center">
            {{ $latihans->links() }}
        </div>

    </div>

    @include('features.latihan-pelayanan._modal_form', ['pelayanans' => $pelayanans ?? collect()])

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const modal = new bootstrap.Modal(document.getElementById('modalLatihanForm'));

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('.btn-tambah-latihan').addEventListener('click', () => {
                resetForm();
                modal.show();
            });

            document.getElementById('btnFilter').addEventListener('click', reloadList);
            reloadList();
        });

        function reloadList() {
            const params = new URLSearchParams({
                pelayanan_id: document.getElementById('pelayanan_id').value,
                tanggal_start: document.getElementById('tanggal_start').value,
                tanggal_end: document.getElementById('tanggal_end').value,
                status: document.getElementById('status').value,
                search: document.getElementById('search').value.trim(),
                ajax: 1
            });

            const url = '{{ route("admin.latihan-pelayanan.index") }}?' + params.toString();

            document.getElementById('latihanList').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Memuat jadwal latihan...</p>
                </div>`;

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('latihanList').innerHTML = html;
                    attachListeners();
                })
                .catch(() => Swal.fire('Error', 'Gagal memuat data', 'error'));
        }

        function attachListeners() {
            document.querySelectorAll('.btn-edit-latihan').forEach(btn => {
                btn.addEventListener('click', () => editLatihan(btn.dataset.id));
            });

            document.querySelectorAll('.btn-delete-latihan').forEach(btn => {
                btn.addEventListener('click', function() {
                    confirmDelete(this.dataset.id, this.dataset.nama);
                });
            });
        }

        function resetForm() {
            const form = document.getElementById('formLatihan');
            form.reset();
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('latihanId').value = '';
            document.getElementById('modalTitle').textContent = 'Buat Jadwal Latihan Pelayanan Baru';
        }

        function editLatihan(id) {
            fetch(`{{ route('admin.latihan-pelayanan.index') }}/${id}/edit?ajax=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const l = data.latihan;

                    document.getElementById('modalTitle').textContent = 'Edit Jadwal Latihan';
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('latihanId').value = l.id;

                    // Isi form
                    document.getElementById('pelayanan_id').value = l.pelayanan_id || '';
                    document.getElementById('nama_latihan').value = l.nama_latihan || '';

                    // === PERBAIKAN TANGGAL (PALING KRITIS) ===
                    if (l.tanggal) {
                        // Pastikan format Y-m-d
                        let tanggalFormatted = l.tanggal;
                        if (tanggalFormatted.includes(' ')) {
                            tanggalFormatted = tanggalFormatted.split(' ')[0]; // ambil hanya tanggal
                        }
                        document.getElementById('tanggal').value = tanggalFormatted;
                    } else {
                        document.getElementById('tanggal').value = '';
                    }

                    // Waktu
                    if (l.waktu) {
                        let waktuFormatted = l.waktu.toString();
                        if (waktuFormatted.length > 5) {
                            waktuFormatted = waktuFormatted.substring(0, 5);
                        }
                        document.getElementById('waktu').value = waktuFormatted;
                    } else {
                        document.getElementById('waktu').value = '';
                    }

                    document.getElementById('lokasi').value = l.lokasi || 'Gedung Utama';
                    document.getElementById('keterangan').value = l.keterangan || '';

                    // Set status radio
                    const statusRadio = document.querySelector(`input[name="status"][value="${l.status}"]`);
                    if (statusRadio) {
                        statusRadio.checked = true;
                    }

                    modal.show();
                }
            })
            .catch(err => {
                console.error('Edit error:', err);
                Swal.fire('Error', 'Gagal memuat data untuk edit', 'error');
            });
        }

        function confirmDelete(id, nama) {
            Swal.fire({
                title: 'Yakin hapus?',
                text: `"${nama}" akan dihapus permanen`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) deleteLatihan(id);
            });
        }

        function deleteLatihan(id) {
            fetch(`{{ route('admin.latihan-pelayanan.destroy', ':id') }}`.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Terhapus!', data.message, 'success');
                    reloadList();
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Gagal menghapus data', 'error'));
        }

        // Form Submit (Create & Update)
        document.getElementById('formLatihan').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route("admin.latihan-pelayanan.index") }}';
            const method = document.getElementById('formMethod').value;

            if (method === 'PUT') {
                const id = document.getElementById('latihanId').value;
                url = `{{ route('admin.latihan-pelayanan.index') }}/${id}`;
            }

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', data.message, 'success');
                    modal.hide();
                    reloadList();
                } else {
                    Swal.fire('Gagal', data.message || 'Validasi gagal', 'error');
                }
            });
        });
    </script>
@endpush