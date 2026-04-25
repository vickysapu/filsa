<div class="modal fade" id="modalLatihanForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-lg); box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            
            <div class="modal-header lux-card-header">
                <h5 class="modal-title" id="modalTitle">Buat Jadwal Latihan Pelayanan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formLatihan" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="latihanId" value="">

                <div class="modal-body lux-container">
                    <div class="lux-card">
                        <div class="lux-card-header"><h3>Informasi Jadwal Latihan</h3></div>

                        <div class="lux-form-group">
                            <label class="lux-label">Pelayanan <span class="text-danger">*</span></label>
                            <select name="pelayanan_id" id="pelayanan_id" class="lux-select" required>
                                <option value="">Pilih Pelayanan</option>
                                {{-- Gunakan variable global dari controller --}}
                                @foreach($pelayanans ?? [] as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_pelayanan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lux-form-group">
                            <label class="lux-label">Nama Latihan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_latihan" id="nama_latihan" class="lux-input" required placeholder="Contoh: Latihan Pelayanan Ibadah Minggu">
                        </div>

                        <div class="lux-grid lux-grid-2">
                            <div class="lux-form-group">
                                <label class="lux-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" class="lux-input" required>
                            </div>
                            <div class="lux-form-group">
                                <label class="lux-label">Waktu <span class="text-danger">*</span></label>
                                <input type="time" name="waktu" id="waktu" class="lux-input" required>
                            </div>
                        </div>

                        <div class="lux-form-group">
                            <label class="lux-label">Lokasi</label>
                            <input type="text" name="lokasi" id="lokasi" class="lux-input" value="Gedung Utama">
                        </div>

                        <div class="lux-form-group">
                            <label class="lux-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="lux-textarea" rows="3" placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <div class="lux-form-group">
                            <label class="lux-label">Status</label>
                            <div style="display:flex; gap:20px; margin-top:8px;">
                                <label style="cursor:pointer;">
                                    <input type="radio" name="status" value="active" checked> Aktif
                                </label>
                                <label style="cursor:pointer;">
                                    <input type="radio" name="status" value="cancelled"> Dibatalkan
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmit">
                        <i class="ri-save-line"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>