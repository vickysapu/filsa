@extends('layouts.admin')

@section('title', 'Kategori Event')

@section('content')
    <div class="lux-container">

        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Kategori Event</h1>
                <p class="text-muted">Kelola kategori event untuk website</p>
            </div>
            <button class="lux-btn lux-btn-primary" onclick="openModal()">
                <i class="ri-add-line"></i> Tambah Kategori
            </button>
        </div>

        @if (session('success'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--success); background: #f0fdf4;">
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <span style="color: #991b1b; font-weight: 600;">{{ session('error') }}</span>
            </div>
        @endif

        @if ($kategoris->count() > 0)
            <div class="lux-grid lux-grid-3">
                @foreach ($kategoris as $kategori)
                    <div class="lux-card" style="border-top: 4px solid {{ $kategori->warna }};">
                        <div class="flex-between mb-3">
                            <h3 class="lux-card-title" style="margin: 0;">{{ $kategori->nama_kategori }}</h3>
                            <span class="lux-badge {{ $kategori->is_active ? 'lux-badge-success' : 'lux-badge-danger' }}">
                                {{ $kategori->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>

                        @if ($kategori->deskripsi)
                            <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;">{{ $kategori->deskripsi }}
                            </p>
                        @endif

                        <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                            <i class="ri-calendar-line"></i> {{ $kategori->events->count() }} Events
                        </div>

                        <div style="display: flex; gap: 0.5rem;">
                            <button class="lux-btn lux-btn-outline lux-btn-sm"
                                onclick="openEditModal({{ json_encode($kategori) }})">
                                <i class="ri-pencil-line"></i> Edit
                            </button>
                            @if ($kategori->events->count() == 0)
                                <form action="{{ route('admin.event-kategori.destroy', $kategori) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lux-btn lux-btn-outline lux-btn-sm"
                                        style="border-color: var(--danger); color: var(--danger);">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 4rem; background: #f8fafc; border-radius: var(--radius);">
                <i class="ri-folder-off-line"
                    style="font-size: 4rem; color: var(--text-muted); opacity: 0.3; margin-bottom: 1rem;"></i>
                <h3>Belum Ada Kategori</h3>
                <p class="text-muted">Mulai dengan menambahkan kategori event pertama</p>
                <button class="lux-btn lux-btn-primary" onclick="openModal()" style="margin-top: 1rem;">
                    <i class="ri-add-line"></i> Tambah Kategori
                </button>
            </div>
        @endif
    </div>

    <!-- Modal -->
    <div id="modalKategori"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);"
            onclick="closeModal()"></div>
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                background: white; border-radius: var(--radius); width: 90%; max-width: 500px;">
            <form id="formKategori" method="POST">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="">

                <div
                    style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 id="modalTitle" style="font-weight: 700; margin: 0;">Tambah Kategori</h3>
                    <button type="button" onclick="closeModal()"
                        style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
                </div>

                <div style="padding: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label class="lux-label">Nama Kategori *</label>
                        <input type="text" name="nama_kategori" id="namaKategori" class="lux-input" required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="lux-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsiKategori" class="lux-textarea" rows="2"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="lux-label">Warna</label>
                        <input type="color" name="warna" id="warnaKategori" class="lux-input" value="#4f46e5"
                            style="height: 50px;">
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="is_active" id="isActiveKategori" value="1" checked>
                            <span>Aktif</span>
                        </label>
                    </div>
                </div>

                <div
                    style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()" class="lux-btn lux-btn-outline">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {

            // Fungsi buka modal untuk Tambah Kategori
            function openModal() {
                resetForm();
                $('#modalTitle').text('Tambah Kategori');
                $('#formKategori').attr('action', "{{ route('admin.event-kategori.store') }}");
                $('#methodField').val(''); // kosongkan _method saat create
                $('#modalKategori').fadeIn(200);
                $('body').css('overflow', 'hidden');
            }

            // Fungsi buka modal untuk Edit Kategori
            function openEditModal(kategori) {
                // Jika data dikirim sebagai string JSON (misal dari onclick atau data-attribute)
                if (typeof kategori === 'string') {
                    kategori = JSON.parse(kategori);
                }

                $('#modalTitle').text('Edit Kategori');
                $('#formKategori').attr('action', '/admin/event-kategori/' + kategori.id);
                $('#methodField').val('PUT');

                // Isi field-form
                $('#namaKategori').val(kategori.nama_kategori || '');
                $('#deskripsiKategori').val(kategori.deskripsi || '');
                $('#warnaKategori').val(kategori.warna || '#4f46e5');
                $('#isActiveKategori').prop('checked', kategori.is_active == 1);

                $('#modalKategori').fadeIn(200);
                $('body').css('overflow', 'hidden');
            }

            // Fungsi tutup modal
            function closeModal() {
                $('#modalKategori').fadeOut(200, function() {
                    // Optional: reset form setelah modal benar-benar tertutup
                    resetForm();
                });
                $('body').css('overflow', '');
            }

            // Fungsi reset form
            function resetForm() {
                $('#formKategori')[0].reset();
                $('#methodField').val('');
                $('#formKategori').attr('action', "{{ route('admin.event-kategori.store') }}");
            }

            // ================== EVENT LISTENER ==================

            // Contoh pemanggilan (sesuaikan dengan HTML kamu)
            // Tombol Tambah biasanya:
            // <button type="button" onclick="openModal()">Tambah Kategori</button>

            // Tombol Edit (rekomendasi pakai class + data attribute)
            // Contoh HTML:
            // <button class="btn-edit-kategori" data-kategori='{"id":5,"nama_kategori":"Seminar","deskripsi":"...","warna":"#ff0000","is_active":1}'>Edit</button>

            $(document).on('click', '.btn-edit-kategori', function() {
                const kategori = $(this).data('kategori');
                openEditModal(kategori);
            });

            // Tombol close / batal (sesuaikan ID atau class tombol close kamu)
            $('#btnCloseModal, #btnBatal, .close-modal-btn').on('click', closeModal);

            // Tutup modal dengan tombol Escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // Optional: tutup modal jika klik di luar area modal-content
            $('#modalKategori').on('click', function(e) {
                if (e.target === this) { // hanya jika klik di background (bukan di dalam modal)
                    closeModal();
                }
            });

            // Agar fungsi tetap bisa dipanggil dari inline onclick (jika masih dipakai)
            window.openModal = openModal;
            window.openEditModal = openEditModal;
            window.closeModal = closeModal;

        });
    </script>
@endpush
