@if ($jemaats->isEmpty())
    <div class="text-center py-4 text-muted">
        <i class="ri-team-line" style="font-size: 3rem; opacity: 0.4;"></i>
        <p class="mt-2">Tidak ada data jemaat aktif</p>
    </div>
@else
    @foreach ($jemaats as $jemaat)
        @php
            $checked = in_array($jemaat->id, (array)($selected ?? [])) ? 'checked' : '';
            $role    = $roles[$jemaat->id] ?? '';
        @endphp

        <label class="petugas-item d-flex align-items-center gap-3 p-3 mb-2 rounded border"
               style="cursor: pointer; background: {{ $checked ? '#f0f9ff' : 'white' }};">

            <!-- Avatar -->
            <div class="avatar-circle" style="width: 42px; height: 42px; background: #e0f2fe; color: #0369a1; 
                 font-weight: 700; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                {{ strtoupper(substr($jemaat->nama_lengkap, 0, 1)) }}
            </div>

            <!-- Info -->
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ $jemaat->nama_lengkap }}</div>
                @if ($jemaat->no_hp)
                    <small class="text-muted">{{ $jemaat->no_hp }}</small>
                @endif
            </div>

            <!-- Checkbox -->
            <input type="checkbox" 
                   name="jemaat_ids[]" 
                   value="{{ $jemaat->id }}" 
                   class="jemaat-checkbox form-check-input mt-0"
                   {{ $checked }}>

            <!-- Role Input -->
            <input type="text" 
                   name="roles[{{ $jemaat->id }}]" 
                   class="lux-input form-control form-control-sm"
                   style="width: 130px;"
                   placeholder="Role (opsional)"
                   value="{{ $role }}">
        </label>
    @endforeach
@endif