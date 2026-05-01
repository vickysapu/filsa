@extends('layouts.admin')

@section('title', 'Buat Jadwal Pelayanan')

@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
            <h1 class="fw-bold mt-3 mb-1" style="font-size: 1.75rem;">Buat Jadwal Pelayanan Baru</h1>
            <p class="text-muted-custom mb-0">Tambah jadwal pelayanan dengan pemilihan petugas secara massal</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger card-custom mb-4">
                <strong><i class="ri-error-warning-line me-2"></i> Terjadi kesalahan validasi</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.jadwal.store') }}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-transparent border-bottom border-custom">
                            <h6 class="card-title mb-0 fw-semibold"><i
                                    class="ri-calendar-line me-2 text-primary"></i>Informasi Jadwal</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Kategori Pelayanan <span
                                        class="text-danger">*</span></label>
                                <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                                    <option value="">Pilih Kategori...</option>
                                    @foreach ($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}"
                                            {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Nama Pelayanan <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_pelayanan"
                                    class="form-control @error('nama_pelayanan') is-invalid @enderror"
                                    value="{{ old('nama_pelayanan') }}"
                                    placeholder="Contoh: Ibadah Minggu Pagi, Kebaktian Pemuda, dll">
                                @error('nama_pelayanan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small text-muted-custom">Tanggal <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal"
                                        class="form-control @error('tanggal') is-invalid @enderror"
                                        value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted-custom">Waktu <span
                                            class="text-danger">*</span></label>
                                    <input type="time" name="waktu"
                                        class="form-control @error('waktu') is-invalid @enderror"
                                        value="{{ old('waktu', '08:00') }}">
                                    @error('waktu')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Lokasi <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="lokasi"
                                    class="form-control @error('lokasi') is-invalid @enderror"
                                    value="{{ old('lokasi', 'Gedung Utama') }}"
                                    placeholder="Contoh: Gedung Utama, Aula Lantai 2">
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Keterangan</label>
                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3"
                                    placeholder="Catatan tambahan untuk jadwal ini...">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label small text-muted-custom">Status Jadwal <span
                                        class="text-danger">*</span></label>
                                <div class="d-flex gap-3 flex-wrap">
                                    <label class="form-check">
                                        <input type="radio" name="status" value="draft" class="form-check-input"
                                            {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}
                                            style="accent-color: var(--warning);">
                                        <span class="form-check-label">Draft (Belum Publik)</span>
                                    </label>
                                    <label class="form-check">
                                        <input type="radio" name="status" value="active" class="form-check-input"
                                            {{ old('status') == 'active' ? 'checked' : '' }}
                                            style="accent-color: var(--success);">
                                        <span class="form-check-label">Aktif (Sudah Publik)</span>
                                    </label>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-transparent border-bottom border-custom">
                            <h6 class="card-title mb-0 fw-semibold"><i class="ri-team-line me-2 text-primary"></i>Petugas
                                Pelayanan</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted-custom small mb-3">
                                <i class="ri-information-line me-1"></i> Pilih beberapa jemaat sekaligus untuk dijadikan
                                petugas pelayanan
                            </p>
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Cari Jemaat</label>
                                <div class="position-relative">
                                    <i
                                        class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                    <input type="text" id="searchJemaat" class="form-control ps-5"
                                        placeholder="Ketik nama jemaat...">
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAll()">
                                    <i class="ri-checkbox-circle-line me-1"></i> Pilih Semua
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">
                                    <i class="ri-close-circle-line me-1"></i> Batal Semua
                                </button>
                                <span class="badge bg-primary ms-auto" id="selectedCount">0 Terpilih</span>
                            </div>
                            <div style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 0.5rem;"
                                id="jemaatList">
                                @foreach ($jemaats as $jemaat)
                                    <label
                                        class="d-flex align-items-center justify-content-between gap-3 p-3 mb-2 rounded border"
                                        style="cursor: pointer; transition: var(--transition); background: var(--bg-surface);"
                                        onmouseover="this.style.background='var(--bg-sidebar-hover)'"
                                        onmouseout="if(!this.querySelector('input').checked) this.style.background='var(--bg-surface)'">
                                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                                            <input type="checkbox" name="jemaat_ids[]" value="{{ $jemaat->id }}"
                                                class="jemaat-checkbox form-check-input"
                                                {{ in_array($jemaat->id, old('jemaat_ids', [])) ? 'checked' : '' }}
                                                style="width: 18px; height: 18px; accent-color: var(--accent-blue);">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                style="width: 35px; height: 35px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-blue-hover)); font-size: 0.9rem;">
                                                {{ substr($jemaat->nama_lengkap, 0, 1) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-semibold">{{ $jemaat->nama_lengkap }}</span>
                                                @if ($jemaat->no_hp)
                                                    <br>
                                                    <span class="text-muted-custom small">{{ $jemaat->no_hp }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="text" name="roles[]" class="form-control form-control-sm"
                                            placeholder="Role (opsional)" value="{{ old('roles.' . $loop->index, '') }}"
                                            style="width: 150px; font-size: 0.85rem;">
                                    </label>
                                @endforeach
                            </div>
                            @error('jemaat_ids')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                            <div class="mt-4 p-3"
                                style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: var(--radius);">
                                <div class="d-flex gap-3">
                                    <i class="ri-information-line text-primary" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <p class="fw-semibold text-primary mb-2 small">Tips Bulk Selection</p>
                                        <ul class="small text-muted-custom mb-0 ps-3">
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
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end mt-4">
                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-custom">
                    <i class="ri-save-line me-1"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function updateSelectedCount() {
            const count = document.querySelectorAll('.jemaat-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = count + ' Terpilih';
        }

        function selectAll() {
            document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
                cb.checked = true;
                cb.closest('label').style.background = 'var(--bg-sidebar-hover)';
            });
            updateSelectedCount();
        }

        function deselectAll() {
            document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('label').style.background = 'var(--bg-surface)';
            });
            updateSelectedCount();
        }
        document.getElementById('searchJemaat').addEventListener('input', function(e) {
            const keyword = e.target.value.toLowerCase();
            document.querySelectorAll('#jemaatList label').forEach(label => {
                const text = label.textContent.toLowerCase();
                label.style.display = text.includes(keyword) ? 'flex' : 'none';
            });
        });
        document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('label').style.background = this.checked ? 'var(--bg-sidebar-hover)' :
                    'var(--bg-surface)';
                updateSelectedCount();
            });
            if (cb.checked) cb.closest('label').style.background = 'var(--bg-sidebar-hover)';
        });
        updateSelectedCount();
    </script>
@endpush
