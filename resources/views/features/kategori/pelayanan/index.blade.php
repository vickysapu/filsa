@extends('layouts.admin')

@section('title', 'Kategori Pelayanan')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Kategori Pelayanan</h1>
                <p class="text-muted">Kelola jenis/kategori pelayanan gereja</p>
            </div>
            <button class="lux-btn lux-btn-primary" onclick="openModal()">
                <i class="ri-add-line"></i> Tambah Kategori
            </button>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--success); background: #f0fdf4;">
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Alert Error -->
        @if (session('error'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <span style="color: #991b1b; font-weight: 600;">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Kategori Cards -->
        @if ($kategoris->count() > 0)
            <div class="lux-grid lux-grid-3">
                @foreach ($kategoris as $kategori)
                    <div class="lux-card" style="border-top: 4px solid {{ $kategori->warna }}; position: relative;">

                        <!-- Status Badge -->
                        <div style="position: absolute; top: 1rem; right: 1rem;">
                            <span class="lux-badge {{ $kategori->is_active ? 'lux-badge-success' : 'lux-badge-danger' }}">
                                {{ $kategori->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>

                        <!-- Nama Kategori -->
                        <h3 class="lux-card-title" style="margin: 0 0 0.75rem 0; font-size: 1.1rem;">
                            {{ $kategori->nama_kategori }}
                        </h3>

                        <!-- Deskripsi -->
                        @if ($kategori->deskripsi)
                            <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem; line-height: 1.5;">
                                {{ $kategori->deskripsi }}
                            </p>
                        @endif

                        <!-- Stats -->
                        <div class="text-muted"
                            style="font-size: 0.85rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-calendar-line"></i>
                            <span>{{ $kategori->pelayanans->count() }} Jadwal Pelayanan</span>
                        </div>

                        <!-- Actions -->
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="lux-btn lux-btn-outline lux-btn-sm"
                                onclick="openEditModal({{ json_encode($kategori) }})">
                                <i class="ri-pencil-line"></i> Edit
                            </button>
                            @if ($kategori->pelayanans->count() == 0)
                                <form action="{{ route('admin.kategori.pelayanan.destroy', $kategori) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
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
            <!-- Empty State -->
            <div
                style="text-align: center; padding: 4rem; background: #f8fafc; border-radius: var(--radius); margin-top: 2rem;">
                <i class="ri-folder-off-line"
                    style="font-size: 4rem; color: var(--text-muted); opacity: 0.3; margin-bottom: 1rem;"></i>
                <h3 style="color: var(--text-main); margin-bottom: 0.5rem;">Belum Ada Kategori</h3>
                <p class="text-muted" style="margin-bottom: 1.5rem;">Mulai dengan menambahkan kategori pelayanan pertama
                    Anda</p>
                <button class="lux-btn lux-btn-primary" onclick="openModal()">
                    <i class="ri-add-line"></i> Tambah Kategori Pertama
                </button>
            </div>
        @endif
    </div>

    <!-- Modal Add/Edit Kategori -->
    <div id="modalKategori"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999;">
        <!-- Overlay -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);"
            onclick="closeModal()"></div>

        <!-- Modal Content -->
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                background: white; border-radius: var(--radius, 12px); 
                width: 90%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                z-index: 10000;">

            <form id="formKategori" method="POST">
                @csrf
                <input type="hidden" id="kategoriId" name="_method" value="">

                <!-- Header -->
                <div
                    style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 id="modalTitle" style="font-weight: 700; margin: 0; font-size: 1.25rem;">Tambah Kategori</h3>
                    <button type="button" onclick="closeModal()"
                        style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">
                        &times;
                    </button>
                </div>

                <!-- Body -->
                <div style="padding: 1.5rem;">
                    <!-- Nama Kategori -->
                    <div style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--text-muted);">
                            Nama Kategori *
                        </label>
                        <input type="text" name="nama_kategori" id="namaKategori"
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: var(--radius, 8px); font-size: 0.95rem;"
                            required placeholder="Contoh: Praise & Worship, Usher, Multimedia">
                    </div>

                    <!-- Deskripsi -->
                    <div style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--text-muted);">
                            Deskripsi
                        </label>
                        <textarea name="deskripsi" id="deskripsiKategori" rows="2"
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: var(--radius, 8px); font-size: 0.95rem; resize: vertical;"
                            placeholder="Deskripsi singkat tentang kategori ini..."></textarea>
                    </div>

                    <!-- Warna Badge (Auto Random) -->
                    <div style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--text-muted);">
                            Warna Badge
                        </label>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <input type="color" name="warna" id="warnaKategori" value="#4f46e5"
                                style="width: 50px; height: 40px; padding: 0; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                            <span class="text-muted" style="font-size: 0.85rem;">
                                <i class="ri-magic-line"></i> Warna akan diacak otomatis jika tidak dipilih
                            </span>
                            <button type="button" class="lux-btn lux-btn-outline lux-btn-sm" onclick="randomizeColor()">
                                <i class="ri-refresh-line"></i> Acak Warna
                            </button>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="isActiveKategori" value="1" checked
                                style="width: 18px; height: 18px; accent-color: var(--primary, #4f46e5);">
                            <span style="font-weight: 500;">Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()"
                        style="padding: 0.625rem 1.25rem; border: 1px solid #e2e8f0; border-radius: var(--radius, 8px); background: white; cursor: pointer; font-weight: 500;">
                        Batal
                    </button>
                    <button type="submit"
                        style="padding: 0.625rem 1.25rem; border: none; border-radius: var(--radius, 8px); background: var(--primary, #4f46e5); color: white; cursor: pointer; font-weight: 600;">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {

            // Random color palette
            const colorPalette = [
                '#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#06b6d4',
                '#8b5cf6', '#ef4444', '#14b8a6', '#f97316', '#6366f1'
            ];

            // Fungsi buka modal Tambah
            function openModal() {
                resetForm();
                randomizeColor(); // auto random warna saat tambah baru

                $('#modalTitle').text('Tambah Kategori');
                $('#formKategori')
                    .attr('action', "{{ route('admin.kategori.pelayanan.store') }}")
                    .find('input[name="_method"]').val('');

                $('#kategoriId').val('');
                $('#modalKategori').fadeIn(200);
                $('body').css('overflow', 'hidden');
            }

            // Fungsi buka modal Edit
            function openEditModal(kategori) {
                // Parse jika dikirim sebagai string JSON
                if (typeof kategori === 'string') {
                    kategori = JSON.parse(kategori);
                }

                $('#modalTitle').text('Edit Kategori');

                $('#formKategori')
                    .attr('action', '/admin/pelayanan/kategori/' + kategori.id)
                    .find('input[name="_method"]').val('PUT');

                $('#kategoriId').val(kategori.id);

                // Isi field
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
                    // Optional: reset form setelah tertutup
                    resetForm();
                });
                $('body').css('overflow', '');
            }

            // Fungsi reset form
            function resetForm() {
                $('#formKategori')[0].reset();
                $('#namaKategori').val('');
                $('#deskripsiKategori').val('');
                $('#isActiveKategori').prop('checked', true);
                // warna tidak di-reset di sini karena randomizeColor dipanggil saat tambah
            }

            // Fungsi random warna
            function randomizeColor() {
                const randomColor = colorPalette[Math.floor(Math.random() * colorPalette.length)];
                $('#warnaKategori').val(randomColor);
            }

            // ================== EVENT LISTENER ==================

            // Tutup dengan tombol Escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // Validasi form sebelum submit (client-side sederhana)
            $('#formKategori').on('submit', function(e) {
                const nama = $('#namaKategori').val().trim();
                if (!nama) {
                    e.preventDefault();
                    alert('Nama kategori harus diisi!');
                    $('#namaKategori').focus();
                    return false;
                }
            });

            // Optional: tutup modal jika klik di luar modal-content
            $('#modalKategori').on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Expose fungsi ke global scope (agar bisa dipanggil dari onclick inline)
            window.openModal = openModal;
            window.openEditModal = openEditModal;
            window.closeModal = closeModal;
            window.randomizeColor = randomizeColor;

        });
    </script>
@endpush
