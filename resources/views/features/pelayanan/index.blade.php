@extends('layouts.admin')

@section('title', 'Jadwal Pelayanan')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Jadwal Pelayanan</h1>
                <p class="text-muted">Kelola jadwal dan petugas pelayanan gereja</p>
            </div>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-jadwal">
                <i class="ri-calendar-add-line"></i> Buat Jadwal Baru
            </button>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--success); background: #f0fdf4; display: flex; align-items: center; gap: 10px;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Toast Notification -->
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

        <!-- Filter Card -->
        <div class="lux-card mb-4">
            <form id="formFilter"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Dari Tanggal</label>
                    <input type="date" id="tanggal_start" name="tanggal_start" class="lux-input"
                        value="{{ request('tanggal_start') }}">
                </div>
                <div>
                    <label class="lux-label">Sampai Tanggal</label>
                    <input type="date" id="tanggal_end" name="tanggal_end" class="lux-input"
                        value="{{ request('tanggal_end') }}">
                </div>
                <div>
                    <label class="lux-label">Kategori</label>
                    <select id="kategori_id" name="kategori_id" class="lux-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lux-label">Status</label>
                    <select id="status" name="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </div>
                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" id="search" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="Nama pelayanan, lokasi...">
                </div>
                <div style="align-self: end;">
                    <button type="button" id="btnFilter" class="lux-btn lux-btn-primary">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Jadwal List -->
        <div class="lux-grid lux-grid-3" id="jadwalList">
            @include('features.pelayanan._list')
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $pelayanans->links('pagination::simple-bootstrap-4') }}
        </div>

    </div>

    <!-- ================== MODAL FORM ================== -->
    <div class="modal fade" id="modalJadwalForm" tabindex="-1" aria-labelledby="modalJadwalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalJadwalLabel">Buat Jadwal Pelayanan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formJadwal" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="jadwalId" value="">

                    <div class="modal-body lux-container">

                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-grid lux-grid-2">

                            <!-- Kolom Kiri: Informasi Jadwal -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Informasi Jadwal</h3>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Kategori Pelayanan *</label>
                                    <select name="kategori_id" id="kategori_id" class="lux-select" required>
                                        <option value="">Pilih Kategori...</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Nama Pelayanan *</label>
                                    <input type="text" name="nama_pelayanan" id="nama_pelayanan" class="lux-input"
                                        required placeholder="Contoh: Ibadah Minggu Pagi">
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tanggal *</label>
                                        <input type="date" name="tanggal" id="tanggal" class="lux-input" required
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Waktu *</label>
                                        <input type="time" name="waktu" id="waktu" class="lux-input" required
                                            value="08:00">
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Lokasi *</label>
                                    <input type="text" name="lokasi" id="lokasi" class="lux-input" required
                                        placeholder="Gedung Utama / Aula Lantai 2">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Keterangan</label>
                                    <textarea name="keterangan" id="keterangan" class="lux-textarea" rows="3" placeholder="Catatan tambahan..."></textarea>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Status Jadwal *</label>
                                    <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="radio" name="status" value="draft" checked> Draft
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="radio" name="status" value="active"> Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Petugas -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Petugas Pelayanan</h3>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Cari Jemaat</label>
                                    <input type="text" id="searchPetugas" class="lux-input"
                                        placeholder="Ketik nama jemaat...">
                                </div>

                                <div style="max-height: 380px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px;"
                                    id="petugasList">
                                    <div style="padding: 2rem; text-align: center;">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Memuat daftar jemaat...</p>
                                    </div>
                                </div>

                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <button type="button" class="lux-btn lux-btn-outline lux-btn-sm"
                                        id="selectAllPetugas">Pilih Semua</button>
                                    <button type="button" class="lux-btn lux-btn-outline lux-btn-sm"
                                        id="deselectAllPetugas">Batal Semua</button>
                                    <span class="lux-badge" id="selectedPetugasCount">0 Terpilih</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitJadwal">
                            <i class="ri-save-line"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: var(--radius-lg); border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalConfirmDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center lux-container">
                    <i class="ri-delete-bin-6-line"
                        style="font-size: 3.5rem; color: var(--danger); margin-bottom: 1rem;"></i>
                    <p class="fw-bold mb-2" style="font-size: 1.25rem;">Yakin ingin menghapus jadwal ini?</p>
                    <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
                    <p id="deleteNamaJadwal" class="fw-bold text-primary mt-3"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="lux-btn lux-btn-danger" id="btnConfirmDelete">
                        <i class="ri-delete-bin-line"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==================== AJAX HELPER FUNCTIONS ====================
        // Fungsi-fungsi sederhana untuk request AJAX

        /**
         * Fungsi untuk request GET (ambil data JSON)
         */
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
                showError('Gagal mengambil data');
            });
        }

        /**
         * Fungsi untuk request GET HTML (ambil data HTML)
         */
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

        /**
         * Fungsi untuk request POST (kirim data)
         */
        function ajaxPost(url, formData, callback) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // Jika formData adalah FormData object (untuk upload file)
            if (formData instanceof FormData) {
                formData.append('_token', csrfToken);
            } else {
                // Jika object biasa, tambah CSRF token
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

        /**
         * Fungsi untuk request PUT (update data)
         */
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

        /**
         * Fungsi untuk request DELETE (hapus data)
         */
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

        // ==================== NOTIFICATION FUNCTIONS ====================
        // Fungsi-fungsi untuk menampilkan pesan

        /**
         * Tampilkan pesan sukses
         */
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

        /**
         * Tampilkan pesan error
         */
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: message
            });
        }

        /**
         * Tampilkan pesan konfirmasi
         */
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

        /**
         * Tampilkan error validasi dari server
         */
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

            // Scroll ke error
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ==================== FORM FUNCTIONS ====================
        // Fungsi-fungsi untuk mengelola form

        /**
         * Tampilkan loading state pada tombol
         */
        function showLoading(button, loadingText = 'Menyimpan...') {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                ${loadingText}
            `;
        }

        /**
         * Sembunyikan loading state dari tombol
         */
        function hideLoading(button) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || '<i class="ri-save-line"></i> Simpan Jadwal';
        }

        /**
         * Reset form ke kondisi awal
         */
        function resetForm(mode = 'create') {
            const form = document.getElementById('formJadwal');
            const errorBox = document.getElementById('formErrors');

            // Reset form
            form.reset();

            // Reset action URL
            form.action = '{{ route("admin.jadwal.store") }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('jadwalId').value = '';

            // Sembunyikan error
            if (errorBox) errorBox.style.display = 'none';

            // Set default values untuk create
            if (mode === 'create') {
                document.getElementById('modalJadwalLabel').textContent = 'Buat Jadwal Pelayanan Baru';
                document.getElementById('tanggal').value = '{{ date("Y-m-d") }}';
                document.getElementById('waktu').value = '08:00';
                document.querySelector('input[name="status"][value="draft"]').checked = true;
                document.getElementById('petugasList').innerHTML = '<p class="text-muted">Daftar petugas akan dimuat saat modal dibuka...</p>';
            }
        }

        /**
         * Isi form dengan data untuk edit
         */
        function populateForm(data) {
            document.getElementById('kategori_id').value = data.kategori_id || '';
            document.getElementById('nama_pelayanan').value = data.nama_pelayanan || '';
            document.getElementById('tanggal').value = data.tanggal || '{{ date("Y-m-d") }}';
            document.getElementById('waktu').value = data.waktu || '08:00';
            document.getElementById('lokasi').value = data.lokasi || '';
            document.getElementById('keterangan').value = data.keterangan || '';

            // Set status radio button
            const statusRadio = document.querySelector(`input[name="status"][value="${data.status || 'draft'}"]`);
            if (statusRadio) statusRadio.checked = true;
        }

        /**
         * Load daftar petugas
         */
        function loadPetugasList(selectedIds = [], roles = {}) {
            const petugasList = document.getElementById('petugasList');
            petugasList.innerHTML = `
                <div style="padding: 2rem; text-align: center;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Memuat daftar jemaat...</p>
                </div>
            `;

            const params = {
                ajax: 1,
                selected: JSON.stringify(selectedIds)
            };

            const queryString = new URLSearchParams(params).toString();
            const url = '{{ route("admin.jadwal.create") }}?' + queryString;

            ajaxGet(url, function(data) {
                if (data.success) {
                    petugasList.innerHTML = data.petugas_html;

                    // Pre-check checkbox & pre-fill role
                    const checkboxes = petugasList.querySelectorAll('.jemaat-checkbox');
                    checkboxes.forEach(checkbox => {
                        const jemaatId = checkbox.value;
                        if (selectedIds.includes(jemaatId)) {
                            checkbox.checked = true;
                            checkbox.closest('.petugas-item').style.background = '#f0f9ff';
                        }
                    });

                    // Fill role
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

        /**
         * Update jumlah petugas terpilih
         */
        function updateSelectedCount() {
            const count = document.querySelectorAll('#petugasList input[type="checkbox"]:checked').length;
            const countElement = document.getElementById('selectedPetugasCount');
            if (countElement) {
                countElement.textContent = count + ' Terpilih';
            }
        }

        // ==================== MAIN APPLICATION CODE ====================
        // Kode utama aplikasi

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi modal
            const modalForm = new bootstrap.Modal(document.getElementById('modalJadwalForm'));
            const modalDelete = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));

            // ==================== EVENT LISTENERS ====================

            // Tombol Tambah Jadwal
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-tambah-jadwal')) {
                    resetForm('create');
                    loadPetugasList();
                    modalForm.show();
                }
            });

            // Tombol Edit Jadwal
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-edit-jadwal')) {
                    const button = e.target.closest('.btn-edit-jadwal');
                    const id = button.dataset.id;
                    loadDataForEdit(id);
                }
            });

            // Tombol Hapus Jadwal
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

            // Konfirmasi Hapus
            document.getElementById('btnConfirmDelete').addEventListener('click', function() {
                const id = this.dataset.id;
                deleteJadwal(id);
            });

            // Filter Button
            document.getElementById('btnFilter').addEventListener('click', function() {
                reloadList();
            });

            // Petugas: Select all / deselect
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

            // Update count & style checked
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('jemaat-checkbox')) {
                    const item = e.target.closest('.petugas-item');
                    item.style.background = e.target.checked ? '#f0f9ff' : 'white';
                    updateSelectedCount();
                }
            });

            // Search petugas live
            document.getElementById('searchPetugas').addEventListener('input', function(e) {
                const val = e.target.value.toLowerCase();
                const labels = document.querySelectorAll('#petugasList label');
                labels.forEach(label => {
                    const text = label.textContent.toLowerCase();
                    label.style.display = text.includes(val) ? 'block' : 'none';
                });
            });

            // ==================== FORM SUBMIT ====================

            document.getElementById('formJadwal').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const button = document.getElementById('btnSubmitJadwal');

                // Validasi form
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                // Tampilkan loading
                showLoading(button);

                // Buat FormData untuk upload file
                const formData = new FormData(form);

                // Kirim data
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

            // ==================== FUNCTIONS ====================

            /**
             * Load data untuk edit
             */
            function loadDataForEdit(id) {
                const url = '{{ route("admin.jadwal.edit", ":id") }}'.replace(':id', id) + '?ajax=1';

                ajaxGet(url, function(data) {
                    if (data.success) {
                        resetForm('edit');

                        // Set form action untuk update
                        const form = document.getElementById('formJadwal');
                        form.action = '{{ route("admin.jadwal.update", ":id") }}'.replace(':id', id);
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('jadwalId').value = id;

                        // Isi form dengan data
                        populateForm(data);

                        // Load petugas
                        loadPetugasList(data.selected_jemaat_ids || [], data.roles || {});

                        // Update modal title
                        document.getElementById('modalJadwalLabel').textContent = 'Edit Jadwal Pelayanan';

                        modalForm.show();
                    } else {
                        showError(data.message || 'Data tidak ditemukan');
                    }
                });
            }

            /**
             * Hapus jadwal
             */
            function deleteJadwal(id) {
                const url = '{{ route("admin.jadwal.destroy", ":id") }}'.replace(':id', id);

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

            /**
             * Reload daftar jadwal
             */
            function reloadList() {
                const params = {
                    tanggal_start: document.getElementById('tanggal_start').value,
                    tanggal_end: document.getElementById('tanggal_end').value,
                    kategori_id: document.getElementById('kategori_id').value,
                    status: document.getElementById('status').value,
                    search: document.getElementById('search').value.trim(),
                    ajax: 1
                };

                // Buat query string
                const queryString = new URLSearchParams(params).toString();
                const url = '{{ route("admin.jadwal.index") }}?' + queryString;

                ajaxGetHTML(url, function(html) {
                    try {
                        // Parse HTML response
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Update jadwal list
                        const newJadwalList = doc.querySelector('#jadwalList');
                        if (newJadwalList) {
                            document.getElementById('jadwalList').innerHTML = newJadwalList.innerHTML;
                        }

                        // Update pagination
                        const newPagination = doc.querySelector('#paginationContainer');
                        if (newPagination) {
                            document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;
                        }
                    } catch (error) {
                        console.error('Error parsing HTML:', error);
                        showError('Gagal memproses data');
                    }
                });
            }

            // Load data awal
            reloadList();
        });
    </script>
@endpush
