@extends('layouts.public')
@section('title', 'Ajukan Akses Data')

@section('content')
    <div class="lux-container"
        style="padding: 4rem 2rem; min-height: calc(100vh - 200px); display: flex; align-items: center;">
        <div style="max-width: 700px; margin: 0 auto; width: 100%;">

            <div class="lux-card" style="border-left: 4px solid var(--brand-accent, #f59e0b); margin-bottom: 2rem;">
                <div style="padding: 1rem 1.5rem; display: flex; gap: 0.75rem; align-items: flex-start;">
                    <i class="ri-shield-warning-line" style="font-size: 1.5rem; color: var(--brand-accent, #f59e0b);"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 0.25rem;">Data Sensitif</strong>
                        <span style="opacity: 0.8; font-size: 0.95rem;">Permintaan ini akan ditinjau oleh tim keamanan.
                            Pastikan tujuan penggunaan data jelas dan sesuai kebijakan.</span>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="lux-card" style="border-left: 4px solid #22c55e; margin-bottom: 2rem; background: #f0fdf4;">
                    <div style="padding: 1rem 1.5rem; display: flex; gap: 0.75rem; align-items: center; color: #166534;">
                        <i class="ri-checkbox-circle-line" style="font-size: 1.25rem;"></i>
                        <span style="font-weight: 600;">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="lux-card" style="border-left: 4px solid #ef4444; margin-bottom: 2rem; background: #fef2f2;">
                    <div style="padding: 1rem 1.5rem; display: flex; gap: 0.75rem; align-items: center; color: #991b1b;">
                        <i class="ri-error-warning-line" style="font-size: 1.25rem;"></i>
                        <span style="font-weight: 600;">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="lux-card" style="border-radius: 24px; overflow: hidden;">
                <div
                    style="background: linear-gradient(135deg, var(--brand-primary, #4f46e5), var(--brand-secondary, #06b6d4)); padding: 1.5rem 2rem; color: white;">
                    <h3
                        style="margin: 0; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-file-list-3-line"></i>
                        Form Permintaan Akses Data
                    </h3>
                </div>

                <div style="padding: 2rem;">
                    <form action="{{ route('public.data-access.store', ['token' => request('token')]) }}" method="POST"
                        id="requestForm">
                        @csrf
                        <input type="hidden" name="_token_access" value="{{ request('token') }}">

                        {{-- 👤 Requester Name --}}
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">
                                Nama Lengkap <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="requester_name" class="lux-input"
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem;"
                                value="{{ old('requester_name') }}" placeholder="Contoh: John Doe" required>
                            @error('requester_name')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">
                                Email <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="email" name="requester_email" class="lux-input"
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem;"
                                value="{{ old('requester_email') }}" placeholder="contoh@email.com" required>
                            @error('requester_email')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- 🔗 Project URL --}}
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">
                                URL Proyek / Aplikasi <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="url" name="project_url" class="lux-input" required
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem;"
                                value="{{ old('project_url') }}" placeholder="https://contoh.com/proyek">
                            <div style="font-size: 0.85rem; opacity: 0.7; margin-top: 0.5rem;">
                                <i class="ri-information-line"></i> Masukkan URL web anda (bisa localhost jika belum
                                memiliki domain)
                                domain.
                            </div>
                            @error('project_url')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">
                                Nama Proyek / Aplikasi
                            </label>
                            <input type="text" name="project_name" class="lux-input"
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem;"
                                value="{{ old('project_name') }}" placeholder="Contoh: Dashboard Sales Q1 2026">
                            @error('project_name')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">
                                Tabel yang Dibutuhkan <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="lux-card"
                                style="padding: 1rem; background: var(--brand-light, #f8fafc); border-radius: 12px; max-height: 200px; overflow-y: auto;">
                                @foreach ($availableTables as $id => $name)
                                    <label
                                        style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; cursor: pointer;">
                                        <input type="checkbox" name="requested_tables[]" value="{{ $id }}"
                                            class="lux-checkbox"
                                            {{ in_array($id, old('requested_tables', [])) ? 'checked' : '' }}
                                            style="width: 18px; height: 18px; accent-color: var(--brand-primary, #4f46e5);">
                                        <span style="font-size: 0.95rem;">{{ $name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div style="font-size: 0.85rem; opacity: 0.7; margin-top: 0.5rem;">
                                <i class="ri-information-line"></i>
                                Pilih minimal 1, maksimal 5 tabel. Hanya tabel aktif yang tersedia.
                            </div>
                            @error('requested_tables')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 🔒 Field: Branch/Cabang (Single Select) --}}
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">
                                Cabang / Lokasi Web <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="branch_id" class="lux-select"
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem;"
                                required>
                                <option value="" disabled {{ !old('branch_id') ? 'selected' : '' }}>Pilih cabang
                                    lokasi...</option>
                                @foreach ($branches as $id => $name)
                                    <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                            <div style="font-size: 0.85rem; opacity: 0.7; margin-top: 0.5rem;">
                                <i class="ri-map-pin-line"></i>
                                Pilih cabang tempat web/aplikasi Anda akan di-deploy.
                            </div>
                            @error('branch_id')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Purpose --}}
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">
                                Tujuan Penggunaan Data <span style="color: #ef4444;">*</span>
                            </label>
                            <textarea name="purpose" class="lux-input" rows="4"
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem; resize: vertical; font-family: inherit;"
                                placeholder="Jelaskan secara detail: untuk apa data digunakan, siapa yang mengakses, dan bagaimana hasilnya..."
                                required>{{ old('purpose') }}</textarea>
                            <div style="font-size: 0.85rem; opacity: 0.7; margin-top: 0.5rem;">Minimal 50 karakter.</div>
                            <small id="charCount"
                                style="display: block; font-size: 0.8rem; opacity: 0.6; margin-top: 0.25rem;">0/1000
                                karakter</small>
                            @error('purpose')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div
                            style="margin-bottom: 2rem; padding: 1.25rem; background: var(--brand-light, #f8fafc); border-radius: 16px; border: 1px solid #e2e8f0;">
                            <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                                <input type="checkbox" name="agreed_terms" class="lux-checkbox" required
                                    style="width: 18px; height: 18px; accent-color: var(--brand-primary, #4f46e5); margin-top: 2px;">
                                <span style="font-size: 0.9rem; line-height: 1.6;">
                                    <strong>Saya menyetujui bahwa:</strong>
                                    <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0; opacity: 0.8;">
                                        <li>Data hanya digunakan sesuai tujuan yang dinyatakan</li>
                                        <li>Tidak akan membagikan data ke pihak ketiga tanpa izin</li>
                                        <li>Akses akan dicatat dan diaudit secara berkala</li>
                                    </ul>
                                </span>
                            </label>
                            @error('agreed_terms')
                                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Buttons --}}
                        <div style="display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
                            <a href="/" class="lux-btn lux-btn-outline"
                                style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-arrow-left-line"></i> Batal
                            </a>
                            <button type="submit" class="lux-btn lux-btn-primary" id="submitBtn"
                                style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; background: linear-gradient(135deg, var(--brand-primary, #4f46e5), var(--brand-secondary, #06b6d4)); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                                <i class="ri-send-plane-line"></i> Ajukan Permintaan
                            </button>
                            <a href="{{ route('data-portal.verify') }}" class="lux-btn lux-btn-primary" id="submitBtn"
                                style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; background: linear-gradient(135deg, var(--brand-primary, #4f46e5), var(--brand-secondary, #06b6d4)); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                                <i class="ri-send-plane-line"></i> Telah Memiliki Token?
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('requestForm');
            const submitBtn = document.getElementById('submitBtn');
            const purpose = document.querySelector('textarea[name="purpose"]');
            const charCount = document.getElementById('charCount');
            const checkboxes = document.querySelectorAll('input[name="requested_tables[]"]');

            // ✅ Character counter untuk purpose
            if (purpose && charCount) {
                const updateCount = () => {
                    const len = purpose.value.length;
                    charCount.textContent = `${len}/1000 karakter ${len < 50 ? '(minimal 50)' : '✓'}`;
                    charCount.style.color = len < 50 ? '#f59e0b' : '#22c55e';
                };
                purpose.addEventListener('input', updateCount);
                updateCount();
            }

            // ✅ Limit checkbox: maksimal 5 pilihan
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const checked = document.querySelectorAll(
                        'input[name="requested_tables[]"]:checked');
                    if (checked.length > 5) {
                        this.checked = false;
                        showAlert('warning', 'Maksimal 5 tabel dapat dipilih per permintaan.');
                    }
                });
            });

            // ✅ Prevent double submit
            form?.addEventListener('submit', function() {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span style="width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Mengirim...';
                }
            });

            // ✅ Simple alert helper (tanpa Bootstrap/Swal)
            function showAlert(type, message) {
                const alert = document.createElement('div');
                alert.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            padding: 1rem 1.5rem; border-radius: 12px; 
            background: ${type === 'success' ? '#f0fdf4' : type === 'warning' ? '#fffbeb' : '#fef2f2'};
            border-left: 4px solid ${type === 'success' ? '#22c55e' : type === 'warning' ? '#f59e0b' : '#ef4444'};
            color: ${type === 'success' ? '#166534' : type === 'warning' ? '#92400e' : '#991b1b'};
            font-weight: 600; box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
                alert.innerHTML =
                    `<i class="ri-${type === 'success' ? 'checkbox-circle' : type === 'warning' ? 'alert' : 'error-warning'}-line" style="margin-right: 0.5rem;"></i>${message}`;
                document.body.appendChild(alert);
                setTimeout(() => {
                    alert.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => alert.remove(), 300);
                }, 4000);
            }

            // Add keyframes if not exist
            if (!document.getElementById('lux-animations')) {
                const style = document.createElement('style');
                style.id = 'lux-animations';
                style.textContent = `
            @keyframes spin { to { transform: rotate(360deg); } }
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        `;
                document.head.appendChild(style);
            }
        });
    </script>
@endpush
