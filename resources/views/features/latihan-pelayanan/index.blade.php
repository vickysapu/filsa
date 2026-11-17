@extends('layouts.admin')

@section('title', 'Jadwal Latihan Pelayanan')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">Jadwal Latihan Pelayanan</h1>
                <p class="text-muted-custom mb-0">Kelola jadwal latihan untuk pelayanan yang sudah ada</p>
            </div>
            <button type="button" class="btn btn-custom btn-tambah-latihan">
                <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Latihan Baru
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show card-custom mb-4" role="alert">
                <i class="ri-checkbox-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card card-custom mb-4">
            <div class="card-body">
                <form id="formFilter" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted-custom">Pelayanan</label>
                        <select id="pelayanan_id" class="form-select">
                            <option value="">Semua Pelayanan</option>
                            @foreach ($pelayanans as $p)
                                <option value="{{ $p->id }}"
                                    {{ request('pelayanan_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_pelayanan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted-custom">Dari Tanggal</label>
                        <input type="date" id="tanggal_start" class="form-control"
                            value="{{ request('tanggal_start') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted-custom">Sampai Tanggal</label>
                        <input type="date" id="tanggal_end" class="form-control" value="{{ request('tanggal_end') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted-custom">Status</label>
                        <select id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted-custom">Cari</label>
                        <input type="text" id="search" class="form-control" value="{{ request('search') }}"
                            placeholder="Nama latihan...">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" id="btnFilter" class="btn btn-custom w-100">
                            <i class="ri-filter-line"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4" id="latihanList">
            @include('features.latihan-pelayanan._list')
        </div>

        <div id="paginationContainer" class="mt-4 d-flex justify-content-center">
            {{ $latihans->links() }}
        </div>
    </div>

    @include('features.latihan-pelayanan._modal_form', ['pelayanans' => $pelayanans ?? collect()])
@endsection

@push('scripts')
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
            const url = '{{ route('admin.latihan-pelayanan.index') }}?' + params.toString();
            document.getElementById('latihanList').innerHTML =
                `<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted-custom">Memuat jadwal latihan...</p></div>`;
            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
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
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const l = data.latihan;
                        document.getElementById('modalTitle').textContent = 'Edit Jadwal Latihan';
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('latihanId').value = l.id;
                        document.getElementById('pelayanan_id').value = l.pelayanan_id || '';
                        document.getElementById('nama_latihan').value = l.nama_latihan || '';
                        if (l.tanggal) {
                            let tanggalFormatted = l.tanggal;
                            if (tanggalFormatted.includes(' ')) {
                                tanggalFormatted = tanggalFormatted.split(' ')[0];
                            }
                            document.getElementById('tanggal').value = tanggalFormatted;
                        } else {
                            document.getElementById('tanggal').value = '';
                        }
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
        document.getElementById('formLatihan').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            let url = '{{ route('admin.latihan-pelayanan.index') }}';
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
