@extends('layouts.admin')

@section('title', 'Kategori Dokumentasi')

@section('content')
    <div class="lux-container">

        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Kategori Dokumentasi</h1>
                <p class="text-muted">Kelola kategori album dokumentasi</p>
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
                            <i class="ri-image-line"></i> {{ $kategori->albums->count() }} Albums
                        </div>

                        <div style="display: flex; gap: 0.5rem;">
                            <button class="lux-btn lux-btn-outline lux-btn-sm"
                                onclick="openEditModal({{ json_encode($kategori) }})">
                                <i class="ri-pencil-line"></i> Edit
                            </button>
                            @if ($kategori->albums->count() == 0)
                                <form action="{{ route('admin.dokumentasi-kategori.destroy', $kategori) }}" method="POST"
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
                <button class="lux-btn lux-btn-primary" onclick="openModal()" style="margin-top: 1rem;">
                    <i class="ri-add-line"></i> Tambah Kategori
                </button>
            </div>
        @endif
    </div>

    <!-- Modal (sama seperti Event Kategori, ganti route saja) -->
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

            // Fungsi buka modal tambah
            function openModal() {
                resetForm();
                $('#modalTitle').text('Tambah Kategori');
                $('#formKategori').attr('action', "{{ route('admin.dokumentasi-kategori.store') }}");
                $('#methodField').val(''); // atau hapus jika tidak diperlukan saat create
                $('#modalKategori').fadeIn(200);
                $('body').css('overflow', 'hidden');
            }

            // Fungsi buka modal edit
            function openEditModal(kategori) {
                // Jika dikirim sebagai string JSON dari blade (misal via onclick)
                if (typeof kategori === 'string') {
                    kategori = JSON.parse(kategori);
                }

                $('#modalTitle').text('Edit Kategori');
                $('#formKategori').attr('action', '/admin/dokumentasi-kategori/' + kategori.id);
                $('#methodField').val('PUT');

                $('#namaKategori').val(kategori.nama_kategori);
                $('#deskripsiKategori').val(kategori.deskripsi || '');
                $('#warnaKategori').val(kategori.warna || '#4f46e5');
                $('#isActiveKategori').prop('checked', kategori.is_active == 1);

                $('#modalKategori').fadeIn(200);
                $('body').css('overflow', 'hidden');
            }

            // Fungsi tutup modal
            function closeModal() {
                $('#modalKategori').fadeOut(200, function() {
                    // Optional: reset form setelah fade out selesai
                    resetForm();
                });
                $('body').css('overflow', '');
            }

            // Fungsi reset form
            function resetForm() {
                $('#formKategori')[0].reset();
                $('#methodField').val('');
                $('#formKategori').attr('action', "{{ route('admin.dokumentasi-kategori.store') }}");
            }

            // Event listener tombol (contoh pemanggilan - sesuaikan dengan HTML kamu)
            // Misal: tombol tambah
            // <button onclick="openModal()">Tambah Kategori</button>

            // Tombol edit (contoh dengan data attribute)
            // <button class="btn-edit" data-kategori='{"id":1,"nama_kategori":"Khotbah","is_active":1,...}'>Edit</button>

            $(document).on('click', '.btn-edit', function() {
                const kategori = $(this).data('kategori');
                openEditModal(kategori);
            });

            // Tombol close / batal
            $('#btnClose, #btnBatal').on('click', closeModal); // sesuaikan ID tombol close kamu

            // Tutup dengan tombol Escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // Optional: tutup modal jika klik di luar modal-content
            $('#modalKategori').on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Expose fungsi ke global scope jika masih dipanggil dari inline onclick
            window.openModal = openModal;
            window.openEditModal = openEditModal;
            window.closeModal = closeModal;

        });
    </script>
@endpush
