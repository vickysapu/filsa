<!-- features/pelayanan/_petugas_list.blade.php -->

@if ($jemaats->isEmpty())
    <div style="padding: 3rem 1rem; text-align: center; color: var(--text-muted); background: #f8fafc; border-radius: 12px;">
        <i class="ri-team-line" style="font-size: 3.5rem; opacity: 0.4;"></i>
        <p class="mt-3 mb-1 fw-medium">Belum ada jemaat aktif</p>
        <small>Tambahkan data jemaat terlebih dahulu</small>
    </div>
@else
    @foreach ($jemaats as $jemaat)
        @php
            $isChecked = in_array($jemaat->id, (array)($selected ?? [])) ? 'checked' : '';
            $roleValue = $roles[$jemaat->id] ?? '';
        @endphp

        <label class="petugas-item d-flex align-items-center justify-content-between p-3 mb-2 rounded-3 border"
               style="border-color: #e2e8f0; background: white; cursor: pointer; transition: all 0.2s;"
               onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'"
               onmouseout="if(!this.querySelector('input').checked) {this.style.background='white'; this.style.borderColor='#e2e8f0';}">

            <div class="d-flex align-items-center gap-3 flex-grow-1">
                <input type="checkbox" name="jemaat_ids[]" value="{{ $jemaat->id }}"
                       class="jemaat-checkbox form-check-input mt-0" {{ $isChecked }}
                       style="width: 1.25rem; height: 1.25rem; accent-color: var(--primary);">

                <div class="rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width: 42px; height: 42px; font-size: 1.1rem; background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    {{ substr($jemaat->nama_lengkap ?? 'J', 0, 1) }}
                </div>

                <div>
                    <div class="fw-semibold">{{ $jemaat->nama_lengkap }}</div>
                    @if ($jemaat->no_hp)
                        <small class="text-muted">{{ $jemaat->no_hp }}</small>
                    @endif
                </div>
            </div>

        </label>
    @endforeach
@endif