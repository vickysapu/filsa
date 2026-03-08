@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="lux-container">

        <div class="mb-5">
            <a href="{{ route('admin.users.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Edit User</h1>
            <p class="text-muted">Memperbarui data akun: <strong>{{ $user->name }}</strong></p>
        </div>

        @if ($errors->any())
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <div style="display: flex; align-items: center; gap: 10px; color: #991b1b;">
                    <i class="ri-error-warning-line" style="font-size: 1.2rem;"></i>
                    <div>
                        <strong>Terjadi kesalahan validasi</strong>
                        <ul style="margin: 5px 0 0 20px; font-size: 0.9rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="lux-grid lux-grid-2">

                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Akun</h3>
                        <i class="ri-user-settings-line" style="color: var(--primary);"></i>
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Nama Lengkap *</label>
                        <input type="text" name="name" class="lux-input @error('name') lux-input-error @enderror"
                            value="{{ old('name', $user->name) }}" placeholder="Contoh: Budi Santoso">
                        @error('name')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Email *</label>
                        <input type="email" name="email" class="lux-input @error('email') lux-input-error @enderror"
                            value="{{ old('email', $user->email) }}" placeholder="email@perusahaan.com">
                        @error('email')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 1.5rem 0;">

                    <div class="lux-form-group">
                        <label class="lux-label">Password Baru
                            <span class="text-muted" style="font-weight: 400; font-size: 0.8rem;">(kosongkan jika tidak
                                ingin mengubah)</span>
                        </label>
                        <input type="password" name="password"
                            class="lux-input @error('password') lux-input-error @enderror" placeholder="••••••••"
                            autocomplete="new-password">
                        @error('password')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="lux-input" placeholder="••••••••"
                            autocomplete="new-password">
                    </div>

                    <div class="lux-form-group"
                        style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                        <input type="hidden" name="is_active" value="0">

                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <span>Akun Aktif</span>
                        </label>
                        <p class="text-muted" style="font-size: 0.8rem; margin: 5px 0 0 28px;">
                            Nonaktifkan akun untuk mencegah user login sementara waktu.
                        </p>
                    </div>
                </div>

                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Hak Akses (Roles)</h3>
                        <i class="ri-shield-user-line" style="color: var(--accent);"></i>
                    </div>

                    <p class="text-muted" style="margin-bottom: 1.5rem;">
                        Pilih satu atau lebih role yang akan diberikan kepada user ini.
                    </p>

                    <div style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
                        @forelse($roles as $role)
                            <label class="lux-role-option"
                                style="
                        display: flex; 
                        align-items: flex-start; 
                        gap: 12px; 
                        padding: 12px; 
                        margin-bottom: 8px; 
                        border: 1px solid #e2e8f0; 
                        border-radius: var(--radius);
                        cursor: pointer;
                        transition: var(--transition);
                        background: white;
                    "
                                onmouseover="this.style.borderColor='var(--primary)'"
                                onmouseout="this.style.borderColor='#e2e8f0'">

                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                    {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}
                                    style="margin-top: 4px; width: 18px; height: 18px; accent-color: var(--primary);">

                                <div>
                                    <span
                                        style="font-weight: 600; color: var(--text-main);">{{ $role->display_name ?? $role->name }}</span>
                                    @if ($role->description)
                                        <br>
                                        <span class="text-muted"
                                            style="font-size: 0.85rem;">{{ $role->description }}</span>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <p class="text-muted text-center" style="padding: 2rem;">
                                <i class="ri-information-line"></i> Tidak ada role aktif yang tersedia.
                            </p>
                        @endforelse
                    </div>

                    @error('roles')
                        <p class="lux-error-text" style="margin-top: 1rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <a href="{{ route('admin.users.index') }}" class="lux-btn lux-btn-outline">
                    Batal
                </a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Update User
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('input[name="roles[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.closest('label');
                if (this.checked) {
                    label.style.background = '#f0f9ff';
                    label.style.borderColor = 'var(--primary)';
                } else {
                    label.style.background = 'white';
                    label.style.borderColor = '#e2e8f0';
                }
            });
            if (checkbox.checked) checkbox.dispatchEvent(new Event('change'));
        });
    </script>
@endpush
