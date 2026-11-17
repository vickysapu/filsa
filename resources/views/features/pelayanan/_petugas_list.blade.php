@if ($jemaats->isEmpty())
    <div class="text-center py-4" style="background: var(--bg-body); border-radius: var(--radius);">
        <i class="ri-team-line display-6 text-muted-custom opacity-25"></i>
        <p class="mt-3 mb-1 fw-medium">Belum ada jemaat aktif</p>
        <small class="text-muted-custom">Tambahkan data jemaat terlebih dahulu</small>
    </div>
@else
    @foreach ($jemaats as $jemaat)
        @php
            $isChecked = in_array($jemaat->id, (array) ($selected ?? [])) ? 'checked' : '';
            $selectedKategori = $kategori_ids[$jemaat->id] ?? '';
        @endphp
        <div class="petugas-item d-flex align-items-center gap-3 p-3 mb-2 rounded border card-custom"
            style="background: var(--bg-surface);">
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                <input type="checkbox" id="jemaat_{{ $jemaat->id }}" name="jemaat_ids[]" value="{{ $jemaat->id }}"
                    class="jemaat-checkbox form-check-input mt-0" {{ $isChecked }}
                    onchange="toggleKategoriField(this)">
                <label for="jemaat_{{ $jemaat->id }}" class="d-flex align-items-center gap-3 mb-0"
                    style="cursor:pointer;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                        style="width: 42px; height: 42px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-blue-hover));">
                        {{ substr($jemaat->nama_lengkap ?? 'J', 0, 1) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $jemaat->nama_lengkap }}</div>
                        @if ($jemaat->no_hp)
                            <small class="text-muted-custom">{{ $jemaat->no_hp }}</small>
                        @endif
                    </div>
                </label>
            </div>
            <div class="kategori_list" style="display: {{ $isChecked ? 'block' : 'none' }}; min-width: 260px;">
                <label class="form-label small text-muted-custom">Kategori Pelayanan <span
                        class="text-danger">*</span></label>
                <select name="kategori_id[{{ $jemaat->id }}]" class="form-select kategori-select">
                    <option value="">Pilih Kategori...</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" {{ $kategori->id == $selectedKategori ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endforeach
@endif

<style>
    .petugas-item {
        transition: all 0.2s ease;
    }

    .kategori_list {
        transition: all 0.3s ease;
    }
</style>
