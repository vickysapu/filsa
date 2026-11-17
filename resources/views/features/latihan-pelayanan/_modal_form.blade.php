<div class="modal fade" id="modalLatihanForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content card-custom">
            <div class="modal-header border-bottom border-custom">
                <h5 class="modal-title fw-semibold" id="modalTitle">Buat Jadwal Latihan Pelayanan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formLatihan" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="latihanId" value="">
                <div class="modal-body">
                    <div class="card card-custom">
                        <div class="card-header bg-transparent border-bottom border-custom">
                            <h6 class="card-title mb-0 fw-semibold">Informasi Jadwal Latihan</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Pelayanan <span
                                        class="text-danger">*</span></label>
                                <select name="pelayanan_id" id="pelayanan_id" class="form-select" required>
                                    <option value="">Pilih Pelayanan</option>
                                    @foreach ($pelayanans ?? [] as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_pelayanan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Nama Latihan <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_latihan" id="nama_latihan" class="form-control"
                                    required placeholder="Contoh: Latihan Pelayanan Ibadah Minggu">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small text-muted-custom">Tanggal <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" id="tanggal" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted-custom">Waktu <span
                                            class="text-danger">*</span></label>
                                    <input type="time" name="waktu" id="waktu" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Lokasi</label>
                                <input type="text" name="lokasi" id="lokasi" class="form-control"
                                    value="Gedung Utama">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted-custom">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan..."></textarea>
                            </div>
                            <div>
                                <label class="form-label small text-muted-custom">Status</label>
                                <div class="d-flex gap-4">
                                    <label class="form-check">
                                        <input type="radio" name="status" value="active" class="form-check-input"
                                            checked>
                                        <span class="form-check-label">Aktif</span>
                                    </label>
                                    <label class="form-check">
                                        <input type="radio" name="status" value="cancelled" class="form-check-input">
                                        <span class="form-check-label">Dibatalkan</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-custom">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-custom" id="btnSubmit">
                        <i class="ri-save-line me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
