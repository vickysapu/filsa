@extends('layouts.admin')

@section('title', 'Jadwal Pelayanan')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="font-size: 1.75rem; font-family: 'Playfair Display', Georgia, serif; letter-spacing: -0.01em;">Jadwal Pelayanan</h1>
                <p class="text-muted-custom mb-0">Kelola jadwal dan petugas pelayanan gereja</p>
            </div>
            <button type="button" class="btn btn-custom btn-tambah-jadwal">
                <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Baru
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show card-custom mb-4" role="alert">
                <i class="ri-checkbox-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000">
            <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold" id="toastMessage">Operasi berhasil!</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-body">
                <form id="formFilter" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small text-muted-custom">Dari Tanggal</label>
                        <input type="date" id="tanggal_start" name="tanggal_start" class="form-control form-control-custom"
                            value="{{ request('tanggal_start') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted-custom form-control-custom">Sampai Tanggal</label>
                        <input type="date" id="tanggal_end" name="tanggal_end" class="form-control"
                            value="{{ request('tanggal_end') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted-custom">Status</label>
                        <select id="status" name="status" class="form-select  form-select-custom-dark">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted-custom">Cari</label>
                        <input type="text" id="search" name="search" class="form-control form-control-custom"
                            value="{{ request('search') }}" placeholder="Nama pelayanan, lokasi...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="btnFilter" class="btn btn-custom w-100">
                            <i class="ri-filter-line me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4" id="jadwalList">
            @include('features.pelayanan._list')
        </div>

        <div id="paginationContainer" class="mt-4 d-flex justify-content-center">
            {{ $pelayanans->links('pagination::simple-bootstrap-4') }}
        </div>
    </div>

    <div class="modal fade" id="modalJadwalForm" tabindex="-1" aria-labelledby="modalJadwalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content card-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalJadwalLabel">Buat Jadwal Pelayanan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formJadwal" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="jadwalId" value="">
                    <div class="modal-body">
                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="card card-custom h-100">
                                    <div class="card-header bg-transparent border-bottom border-custom">
                                        <h6 class="card-title mb-0 fw-semibold">Informasi Jadwal</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label small text-muted-custom">Nama Pelayanan <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="nama_pelayanan" id="nama_pelayanan"
                                                class="form-control" required placeholder="Contoh: Ibadah Minggu Pagi">
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small text-muted-custom">Tanggal <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="tanggal" id="tanggal" class="form-control"
                                                    required value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted-custom">Waktu <span
                                                        class="text-danger">*</span></label>
                                                <input type="time" name="waktu" id="waktu" class="form-control"
                                                    required value="08:00">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted-custom">Lokasi <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="lokasi" id="lokasi" class="form-control"
                                                required placeholder="Gedung Utama / Aula Lantai 2">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted-custom">Keterangan</label>
                                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan..."></textarea>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label small text-muted-custom">Status Jadwal <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex gap-3 flex-wrap">
                                                <label class="form-check">
                                                    <input type="radio" name="status" value="draft"
                                                        class="form-check-input" checked> Draft
                                                </label>
                                                <label class="form-check">
                                                    <input type="radio" name="status" value="active"
                                                        class="form-check-input"> Aktif
                                                </label>
                                                <label class="form-check">
                                                    <input type="radio" name="status" value="completed"
                                                        class="form-check-input"> Selesai
                                                </label>
                                                <label class="form-check">
                                                    <input type="radio" name="status" value="cancelled"
                                                        class="form-check-input"> Dibatalkan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card card-custom h-100">
                                    <div class="card-header bg-transparent border-bottom border-custom">
                                        <h6 class="card-title mb-0 fw-semibold">Petugas Pelayanan</h6>
                                    </div>
                                    <div class="card-body">
                                        <div style="max-height: 380px; overflow-y: auto;" id="petugasList">
                                            <div class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status"></div>
                                                <p class="mt-2 text-muted-custom small">Memuat daftar jemaat...</p>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap mt-3">
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                id="selectAllPetugas">Pilih Semua</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                id="deselectAllPetugas">Batal Semua</button>
                                            <span class="badge bg-primary" id="selectedPetugasCount">0 Terpilih</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitJadwal">
                            <i class="ri-save-line me-1"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalConfirmDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="ri-delete-bin-6-line display-4 text-danger mb-3"></i>
                    <p class="fw-semibold mb-2">Yakin ingin menghapus jadwal ini?</p>
                    <p class="text-muted-custom small">Aksi ini tidak dapat dibatalkan.</p>
                    <p id="deleteNamaJadwal" class="fw-semibold text-primary mt-3"></p>
                </div>
                <div class="modal-footer border-top border-custom justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                        <i class="ri-delete-bin-line me-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function ajaxGet(url, callback) {
            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (callback) callback(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Gagal mengambil data 1');
                });
        }

        function ajaxGetHTML(url, callback) {
            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.text();
                })
                .then(html => {
                    if (callback) callback(html);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Gagal mengambil data');
                });
        }

        function ajaxPost(url, formData, callback) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            if (formData instanceof FormData) {
                formData.append('_token', csrfToken);
            } else {
                formData._token = csrfToken;
                formData = new URLSearchParams(formData);
            }
            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (callback) callback(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Gagal mengirim data');
                });
        }

        function ajaxPut(url, data, callback) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            data._token = csrfToken;
            data._method = 'PUT';
            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (callback) callback(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Gagal mengubah data');
                });
        }

        function ajaxDelete(url, callback) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (callback) callback(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Gagal menghapus data');
                });
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: message
            });
        }

        function showConfirm(message, onConfirm) {
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi',
                text: message,
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed && onConfirm) {
                    onConfirm();
                }
            });
        }

        function showValidationErrors(errors) {
            const errorBox = document.getElementById('formErrors');
            const errorList = document.getElementById('errorList');
            if (!errorBox || !errorList) return;
            let html = '';
            for (let field in errors) {
                const messages = errors[field];
                if (Array.isArray(messages)) {
                    html += `<li><strong>${field}:</strong> ${messages.join(', ')}</li>`;
                } else {
                    html += `<li><strong>${field}:</strong> ${messages}</li>`;
                }
            }
            errorList.innerHTML = html;
            errorBox.style.display = 'block';
            errorBox.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function showLoading(button, loadingText = 'Menyimpan...') {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
        }

        function hideLoading(button) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || '<i class="ri-save-line"></i> Simpan Jadwal';
        }

        function resetForm(mode = 'create') {
            const form = document.getElementById('formJadwal');
            const errorBox = document.getElementById('formErrors');
            form.reset();
            form.action = '{{ route('admin.jadwal.store') }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('jadwalId').value = '';
            if (errorBox) errorBox.style.display = 'none';
            if (mode === 'create') {
                document.getElementById('modalJadwalLabel').textContent = 'Buat Jadwal Pelayanan Baru';
                document.getElementById('tanggal').value = '{{ date('Y-m-d') }}';
                document.getElementById('waktu').value = '08:00';
                document.querySelector('input[name="status"][value="draft"]').checked = true;
                document.getElementById('petugasList').innerHTML =
                    '<p class="text-muted-custom small">Daftar petugas akan dimuat saat modal dibuka...</p>';
            }
        }

        function populateForm(data) {
            document.getElementById('nama_pelayanan').value = data.nama_pelayanan || '';
            document.getElementById('tanggal').value = data.tanggal || '';
            document.getElementById('waktu').value = data.waktu || '';
            document.getElementById('lokasi').value = data.lokasi || '';
            document.getElementById('keterangan').value = data.keterangan || '';
            const statusRadio = document.querySelector(`input[name="status"][value="${data.status}"]`);
            if (statusRadio) statusRadio.checked = true;
            window.kategoriPerJemaat = data.kategori_ids || {};
        }

        function loadPetugasList(selectedIds = [], roles = {}) {
            const petugasList = document.getElementById('petugasList');
            petugasList.innerHTML =
                `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted-custom small">Memuat daftar jemaat...</p></div>`;
            const params = {
                ajax: 1,
                selected: JSON.stringify(selectedIds)
            };
            const queryString = new URLSearchParams(params).toString();
            const url = '{{ route('admin.jadwal.create') }}?' + queryString;
            ajaxGet(url, function(data) {
                if (data.success) {
                    petugasList.innerHTML = data.petugas_html;
                    setTimeout(() => {
                        const checkboxes = petugasList.querySelectorAll('.jemaat-checkbox');
                        checkboxes.forEach(cb => {
                            toggleKategori(cb);
                        });
                    }, 100);
                    const checkboxes = petugasList.querySelectorAll('.jemaat-checkbox');
                    checkboxes.forEach(checkbox => {
                        const jemaatId = checkbox.value;
                        if (selectedIds.includes(jemaatId)) {
                            checkbox.checked = true;
                            const item = checkbox.closest('.petugas-item');
                            item.style.background = '#f0f9ff';
                            const select = item.querySelector('.kategori_list select');
                            if (select && window.kategoriPerJemaat[jemaatId]) {
                                select.value = window.kategoriPerJemaat[jemaatId];
                            }
                            toggleKategori(checkbox);
                        }
                    });
                    const roleInputs = petugasList.querySelectorAll('input[name^="roles["]');
                    roleInputs.forEach(input => {
                        const name = input.name;
                        const idMatch = name.match(/roles\[(\d+)\]/);
                        if (idMatch && roles[idMatch[1]]) {
                            input.value = roles[idMatch[1]];
                        }
                    });
                    updateSelectedCount();
                } else {
                    petugasList.innerHTML = '<p class="text-danger">Gagal memuat daftar jemaat</p>';
                }
            });
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('#petugasList input[type="checkbox"]:checked').length;
            const countElement = document.getElementById('selectedPetugasCount');
            if (countElement) {
                countElement.textContent = count + ' Terpilih';
            }
        }

        function toggleKategori(checkbox) {
            const item = checkbox.closest('.petugas-item');
            const kategoriDiv = item.querySelector('.kategori_list');
            const select = item.querySelector('.kategori-select');
            if (!kategoriDiv) return;
            if (checkbox.checked) {
                kategoriDiv.style.display = 'block';
                if (select) select.name = `kategori_id[${checkbox.value}]`;
            } else {
                kategoriDiv.style.display = 'none';
                if (select) select.name = '';
            }
        }
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('jemaat-checkbox')) {
                toggleKategori(e.target);
                updateSelectedCount();
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const modalForm = new bootstrap.Modal(document.getElementById('modalJadwalForm'));
            const modalDelete = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-tambah-jadwal')) {
                    resetForm('create');
                    loadPetugasList();
                    modalForm.show();
                }
            });
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-edit-jadwal')) {
                    const button = e.target.closest('.btn-edit-jadwal');
                    const id = button.dataset.id;
                    loadDataForEdit(id);
                }
            });
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete-jadwal')) {
                    e.preventDefault();
                    const button = e.target.closest('.btn-delete-jadwal');
                    const id = button.dataset.id;
                    const nama = button.dataset.nama || 'Jadwal ini';
                    document.getElementById('deleteNamaJadwal').textContent = nama;
                    document.getElementById('btnConfirmDelete').dataset.id = id;
                    modalDelete.show();
                }
            });
            document.getElementById('btnConfirmDelete').addEventListener('click', function() {
                const id = this.dataset.id;
                deleteJadwal(id);
            });
            document.getElementById('btnFilter').addEventListener('click', function() {
                reloadList();
            });
            document.getElementById('selectAllPetugas').addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('#petugasList input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });
            document.getElementById('deselectAllPetugas').addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('#petugasList input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('jemaat-checkbox')) {
                    const item = e.target.closest('.petugas-item');
                    item.style.background = e.target.checked ? '#f0f9ff' : 'white';
                    updateSelectedCount();
                }
            });
            let searchTimeout;

            function setupPetugasSearch() {
                const searchInput = document.getElementById('searchPetugas');
                if (!searchInput) return;
                searchInput.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const keyword = e.target.value.toLowerCase().trim();
                        const items = document.querySelectorAll('#petugasList .petugas-item');
                        if (items.length === 0) return;
                        let visibleCount = 0;
                        items.forEach(item => {
                            const text = item.textContent.toLowerCase();
                            const shouldShow = text.includes(keyword);
                            item.style.display = shouldShow ? 'flex' : 'none';
                            if (shouldShow) visibleCount++;
                        });
                        if (keyword !== '' && visibleCount === 0) {
                            const noResult = document.createElement('div');
                            noResult.id = 'noResultSearch';
                            noResult.style.cssText =
                                'padding: 2rem; text-align: center; color: #6b7280;';
                            noResult.innerHTML =
                                `<p>Tidak ditemukan jemaat dengan kata "<strong>${keyword}</strong>"</p>`;
                            document.getElementById('noResultSearch')?.remove();
                            document.getElementById('petugasList').appendChild(noResult);
                        } else {
                            document.getElementById('noResultSearch')?.remove();
                        }
                    }, 200);
                });
            }
            document.getElementById('formJadwal').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const button = document.getElementById('btnSubmitJadwal');
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }
                let isValid = true;
                const checkedCheckboxes = document.querySelectorAll(
                '#petugasList .jemaat-checkbox:checked');
                checkedCheckboxes.forEach(checkbox => {
                    const item = checkbox.closest('.petugas-item');
                    const select = item.querySelector('.kategori-select');
                    if (select && !select.value) {
                        select.style.borderColor = '#dc3545';
                        isValid = false;
                        if (isValid === false) {
                            item.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    } else if (select) {
                        select.style.borderColor = '';
                    }
                });
                if (!isValid) {
                    showError('Kategori pelayanan wajib dipilih untuk setiap petugas yang dicentang!');
                    return;
                }
                showLoading(button);
                const checkedBoxes = document.querySelectorAll('#petugasList .jemaat-checkbox:checked');
                let kategoriValid = true;
                checkedBoxes.forEach(checkbox => {
                    const item = checkbox.closest('.petugas-item');
                    const select = item.querySelector('.kategori-select');
                    if (select && !select.value) {
                        select.style.border = '2px solid #dc3545';
                        kategoriValid = false;
                    } else if (select) {
                        select.style.border = '';
                    }
                });
                if (!kategoriValid) {
                    showError('Kategori pelayanan harus dipilih untuk setiap petugas yang dicentang!');
                    return;
                }
                const formData = new FormData(form);
                ajaxPost(form.action, formData, function(response) {
                    hideLoading(button);
                    if (response.success) {
                        showSuccess(response.message || 'Jadwal berhasil disimpan');
                        modalForm.hide();
                        reloadList();
                    } else {
                        if (response.errors) {
                            showValidationErrors(response.errors);
                        } else {
                            showError(response.message || 'Gagal menyimpan jadwal');
                        }
                    }
                });
            });

            function loadDataForEdit(id) {
                const url = '{{ route('admin.jadwal.edit', ':id') }}'.replace(':id', id) + '?ajax=1';
                ajaxGet(url, function(data) {
                    if (data.success) {
                        resetForm('edit');
                        const form = document.getElementById('formJadwal');
                        form.action = '{{ route('admin.jadwal.update', ':id') }}'.replace(':id', id);
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('jadwalId').value = id;
                        populateForm(data);
                        window.kategoriPerJemaat = data.kategori_ids || {};
                        loadPetugasList(data.selected_jemaat_ids || [], data.roles || {});
                        document.getElementById('modalJadwalLabel').textContent = 'Edit Jadwal Pelayanan';
                        modalForm.show();
                    } else {
                        showError(data.message || 'Data tidak ditemukan');
                    }
                });
            }

            function deleteJadwal(id) {
                const url = '{{ route('admin.jadwal.destroy', ':id') }}'.replace(':id', id);
                ajaxDelete(url, function(response) {
                    if (response.success) {
                        showSuccess(response.message || 'Jadwal berhasil dihapus');
                        modalDelete.hide();
                        reloadList();
                    } else {
                        showError(response.message || 'Gagal menghapus jadwal');
                    }
                });
            }

            function reloadList() {
                const params = {
                    tanggal_start: document.getElementById('tanggal_start').value,
                    tanggal_end: document.getElementById('tanggal_end').value,
                    status: document.getElementById('status').value,
                    search: document.getElementById('search').value.trim(),
                    ajax: 1
                };
                const queryString = new URLSearchParams(params).toString();
                const url = '{{ route('admin.jadwal.index') }}?' + queryString;
                const jadwalList = document.getElementById('jadwalList');
                jadwalList.innerHTML =
                    `<div class="text-center py-5"><div class="spinner-border text-primary mb-3" role="status"></div><p class="text-muted-custom">Memuat jadwal pelayanan...</p></div>`;
                ajaxGetHTML(url, function(html) {
                    try {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newList = doc.querySelector('#jadwalList');
                        const newPagination = doc.querySelector('#paginationContainer');
                        if (newList) {
                            document.getElementById('jadwalList').innerHTML = newList.innerHTML;
                        } else {
                            document.getElementById('jadwalList').innerHTML = html;
                        }
                        if (newPagination) {
                            document.getElementById('paginationContainer').innerHTML = newPagination
                                .innerHTML;
                        }
                        attachDynamicEventListeners();
                    } catch (error) {
                        console.error('Error parsing HTML:', error);
                        showError('Gagal memproses data jadwal');
                        window.location.reload();
                    }
                });
            }

            function attachDynamicEventListeners() {
                document.querySelectorAll('.btn-edit-jadwal').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        loadDataForEdit(id);
                    });
                });
                document.querySelectorAll('.btn-delete-jadwal').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        const nama = this.dataset.nama || 'Jadwal ini';
                        document.getElementById('deleteNamaJadwal').textContent = nama;
                        document.getElementById('btnConfirmDelete').dataset.id = id;
                        modalDelete.show();
                    });
                });
            }
            reloadList();
        });
    </script>
@endpush
