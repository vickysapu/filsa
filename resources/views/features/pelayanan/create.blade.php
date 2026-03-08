@extends('layouts.admin')

@section('title', 'Buat Jadwal Pelayanan')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('admin.jadwal.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Buat Jadwal Pelayanan
                Baru</h1>
            <p class="text-muted">Tambah jadwal pelayanan dengan pemilihan petugas secara massal</p>
        </div>

        <!-- Alert Error -->
        @if ($errors->any())
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <div style="color: #991b1b;">
                    <strong><i class="ri-error-warning-line"></i> Terjadi kesalahan validasi</strong>
                    <ul style="margin: 5px 0 0 20px; font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.jadwal.store') }}" method="POST">
            @csrf

            <div class="lux-grid lux-grid-2">

                <!-- Kolom Kiri: Informasi Jadwal -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Jadwal</h3>
                        <i class="ri-calendar-line" style="color: var(--primary);"></i>
                    </div>

                    <!-- Kategori -->
                    <div class="lux-form-group">
                        <label class="lux-label">Kategori Pelayanan *</label>
                        <select name="kategori_id" class="lux-select @error('kategori_id') lux-input-error @enderror">
                            <option value="">Pilih Kategori...</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Pelayanan -->
                    <div class="lux-form-group">
                        <label class="lux-label">Nama Pelayanan *</label>
                        <input type="text" name="nama_pelayanan"
                            class="lux-input @error('nama_pelayanan') lux-input-error @enderror"
                            value="{{ old('nama_pelayanan') }}"
                            placeholder="Contoh: Ibadah Minggu Pagi, Kebaktian Pemuda, dll">
                        @error('nama_pelayanan')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal & Waktu -->
                    <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                        <div class="lux-form-group">
                            <label class="lux-label">Tanggal *</label>
                            <input type="date" name="tanggal"
                                class="lux-input @error('tanggal') lux-input-error @enderror"
                                value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                            @error('tanggal')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="lux-form-group">
                            <label class="lux-label">Waktu *</label>
                            <input type="time" name="waktu" class="lux-input @error('waktu') lux-input-error @enderror"
                                value="{{ old('waktu', '08:00') }}">
                            @error('waktu')
                                <p class="lux-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div class="lux-form-group">
                        <label class="lux-label">Lokasi *</label>
                        <input type="text" name="lokasi" class="lux-input @error('lokasi') lux-input-error @enderror"
                            value="{{ old('lokasi', 'Gedung Utama') }}" placeholder="Contoh: Gedung Utama, Aula Lantai 2">
                        @error('lokasi')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="lux-form-group">
                        <label class="lux-label">Keterangan</label>
                        <textarea name="keterangan" class="lux-textarea @error('keterangan') lux-input-error @enderror" rows="3"
                            placeholder="Catatan tambahan untuk jadwal ini...">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="lux-form-group">
                        <label class="lux-label">Status Jadwal *</label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                <input type="radio" name="status" value="draft"
                                    {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}
                                    style="accent-color: var(--warning); width: 16px; height: 16px;">
                                <span>Draft (Belum Publik)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                <input type="radio" name="status" value="active"
                                    {{ old('status') == 'active' ? 'checked' : '' }}
                                    style="accent-color: var(--success); width: 16px; height: 16px;">
                                <span>Aktif (Sudah Publik)</span>
                            </label>
                        </div>
                        @error('status')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Kolom Kanan: Pemilihan Petugas (Bulk Selection) -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Petugas Pelayanan</h3>
                        <i class="ri-team-line" style="color: var(--accent);"></i>
                    </div>

                    <p class="text-muted" style="margin-bottom: 1rem; font-size: 0.9rem;">
                        <i class="ri-information-line"></i> Pilih beberapa jemaat sekaligus untuk dijadikan petugas
                        pelayanan
                    </p>

                    <!-- Search Box -->
                    <div class="lux-form-group">
                        <label class="lux-label">Cari Jemaat</label>
                        <div style="position: relative;">
                            <i class="ri-search-line"
                                style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="text" id="searchJemaat" class="lux-input" placeholder="Ketik nama jemaat..."
                                style="padding-left: 36px;">
                        </div>
                    </div>

                    <!-- Quick Select Buttons -->
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
                        <button type="button" class="lux-btn lux-btn-outline lux-btn-sm" onclick="selectAll()">
                            <i class="ri-checkbox-circle-line"></i> Pilih Semua
                        </button>
                        <button type="button" class="lux-btn lux-btn-outline lux-btn-sm" onclick="deselectAll()">
                            <i class="ri-close-circle-line"></i> Batal Semua
                        </button>
                        <span class="lux-badge lux-badge-info" id="selectedCount" style="margin-left: auto;">
                            0 Terpilih
                        </span>
                    </div>

                    <!-- Jemaat List with Checkboxes -->
                    <div style="max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: var(--radius); padding: 0.5rem;"
                        id="jemaatList">
                        @foreach ($jemaats as $jemaat)
                            <label class="lux-permission-item"
                                style="
                        display: flex; 
                        align-items: center; 
                        justify-content: space-between;
                        gap: 10px; 
                        padding: 10px 12px; 
                        margin-bottom: 4px; 
                        border-radius: 8px;
                        cursor: pointer;
                        transition: var(--transition);
                        background: white;
                    "
                                onmouseover="this.style.background='#f8fafc'"
                                onmouseout="if(!this.querySelector('input').checked) this.style.background='white'">

                                <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                                    <input type="checkbox" name="jemaat_ids[]" value="{{ $jemaat->id }}"
                                        class="jemaat-checkbox"
                                        {{ in_array($jemaat->id, old('jemaat_ids', [])) ? 'checked' : '' }}
                                        style="width: 18px; height: 18px; accent-color: var(--primary);">

                                    <div
                                        style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                                        display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">
                                        {{ substr($jemaat->nama_lengkap, 0, 1) }}
                                    </div>

                                    <div style="flex: 1;">
                                        <span
                                            style="font-weight: 600; color: var(--text-main);">{{ $jemaat->nama_lengkap }}</span>
                                        @if ($jemaat->no_hp)
                                            <br>
                                            <span class="text-muted"
                                                style="font-size: 0.8rem;">{{ $jemaat->no_hp }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Role Input -->
                                <input type="text" name="roles[]" class="lux-input" placeholder="Role (opsional)"
                                    value="{{ old('roles.' . $loop->index, '') }}"
                                    style="width: 150px; font-size: 0.85rem; padding: 6px 10px;">
                            </label>
                        @endforeach
                    </div>

                    @error('jemaat_ids')
                        <p class="lux-error-text" style="margin-top: 0.5rem;">{{ $message }}</p>
                    @enderror

                    <!-- Info Box -->
                    <div
                        style="margin-top: 1.5rem; padding: 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: var(--radius);">
                        <div style="display: flex; align-items: start; gap: 10px;">
                            <i class="ri-information-line"
                                style="color: var(--primary); font-size: 1.2rem; margin-top: 2px;"></i>
                            <div>
                                <p style="font-weight: 600; color: var(--primary); margin-bottom: 0.5rem;">Tips Bulk
                                    Selection</p>
                                <ul style="font-size: 0.85rem; color: var(--text-muted); margin: 0; padding-left: 1.2rem;">
                                    <li>Gunakan "Pilih Semua" untuk memilih semua jemaat sekaligus</li>
                                    <li>Isi kolom "Role" untuk setiap jemaat (contoh: Leader, Vocal 1, Usher)</li>
                                    <li>Role bersifat opsional, bisa dikosongkan</li>
                                    <li>Gunakan search untuk menemukan jemaat dengan cepat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.jadwal.index') }}" class="lux-btn lux-btn-outline">Batal</a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Update selected count
        function updateSelectedCount() {
            const count = document.querySelectorAll('.jemaat-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = count + ' Terpilih';
        }

        // Select all
        function selectAll() {
            document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
                cb.checked = true;
                cb.closest('label').style.background = '#f0f9ff';
            });
            updateSelectedCount();
        }

        // Deselect all
        function deselectAll() {
            document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('label').style.background = 'white';
            });
            updateSelectedCount();
        }

        // Search functionality
        document.getElementById('searchJemaat').addEventListener('input', function(e) {
            const keyword = e.target.value.toLowerCase();
            document.querySelectorAll('#jemaatList label').forEach(label => {
                const text = label.textContent.toLowerCase();
                label.style.display = text.includes(keyword) ? 'flex' : 'none';
            });
        });

        // Checkbox change handler
        document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('label').style.background = this.checked ? '#f0f9ff' : 'white';
                updateSelectedCount();
            });
            // Init style
            if (cb.checked) cb.closest('label').style.background = '#f0f9ff';
        });

        // Init count
        updateSelectedCount();
    </script>
@endpush
