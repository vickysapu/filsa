@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
    <div class="lux-container">

        <!-- Header Section -->
        <div class="mb-5">
            <a href="{{ route('admin.roles.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">Edit Role</h1>
            <p class="text-muted">Memperbarui hak akses untuk role: <strong>{{ $role->display_name }}</strong></p>
        </div>

        <!-- Notifikasi Error Global -->
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

        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="lux-grid lux-grid-2">

                <!-- Kolom Kiri: Informasi Role -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Informasi Role</h3>
                        <i class="ri-shield-key-line" style="color: var(--primary);"></i>
                    </div>

                    <!-- Nama Role (Readonly saat Edit) -->
                    <div class="lux-form-group">
                        <label class="lux-label">Nama Role (System Name) *</label>
                        <input type="text" class="lux-input" value="{{ $role->name }}" readonly
                            style="background: #f1f5f9; cursor: not-allowed; color: var(--text-muted);">
                        <p class="lux-form-help" style="margin-top: 6px; font-size: 0.8rem;">
                            <i class="ri-information-line"></i> Nama role tidak dapat diubah setelah dibuat untuk menjaga
                            konsistensi sistem.
                        </p>
                    </div>

                    <!-- Display Name -->
                    <div class="lux-form-group">
                        <label class="lux-label">Display Name *</label>
                        <input type="text" name="display_name"
                            class="lux-input @error('display_name') lux-input-error @enderror"
                            value="{{ old('display_name', $role->display_name) }}"
                            placeholder="Contoh: Administrator, Manager Keuangan">
                        @error('display_name')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="lux-form-group">
                        <label class="lux-label">Deskripsi</label>
                        <textarea name="description" class="lux-textarea @error('description') lux-input-error @enderror" rows="4"
                            placeholder="Jelaskan tanggung jawab dan cakupan role ini...">{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <p class="lux-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="lux-form-group"
                        style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                        {{-- Hidden input untuk menangani checkbox --}}
                        <input type="hidden" name="is_active" value="0">

                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <span>Role Aktif</span>
                        </label>
                        <p class="text-muted" style="font-size: 0.8rem; margin: 5px 0 0 28px;">
                            Nonaktifkan role untuk mencegah pengguna dengan role ini mengakses sistem.
                        </p>
                    </div>
                </div>

                <!-- Kolom Kanan: Permissions -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Hak Akses (Permissions)</h3>
                        <i class="ri-key-2-line" style="color: var(--accent);"></i>
                    </div>

                    <p class="text-muted" style="margin-bottom: 1.5rem;">
                        Pilih permissions yang akan dimiliki oleh role ini. Gunakan pencarian untuk menemukan permission
                        dengan cepat.
                    </p>

                    <!-- Search Filter (Optional) -->
                    <div class="lux-form-group">
                        <input type="text" id="permissionSearch" class="lux-input" placeholder="🔍 Cari permission..."
                            style="font-size: 0.9rem;">
                    </div>

                    <!-- Permission Groups -->
                    <div style="max-height: 450px; overflow-y: auto; padding-right: 5px;" id="permissionList">
                        @forelse($permissionGroups as $group => $permissions)
                            <div class="permission-group" data-group="{{ strtolower($group) }}"
                                style="margin-bottom: 1.5rem;">
                                <!-- Group Header with Select All -->
                                <div
                                    style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #f8fafc; border-radius: var(--radius); margin-bottom: 10px;">
                                    <span
                                        style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">{{ ucfirst($group) }}</span>
                                    <label
                                        style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; cursor: pointer; color: var(--text-muted);">
                                        <input type="checkbox" class="select-all-group"
                                            data-group="{{ strtolower($group) }}"
                                            style="width: 16px; height: 16px; accent-color: var(--primary);">
                                        Pilih Semua
                                    </label>
                                </div>

                                <!-- Permission Items -->
                                <div style="padding-left: 8px;">
                                    @foreach ($permissions as $permission)
                                        <label class="lux-permission-item"
                                            style="
                                display: flex; 
                                align-items: center; 
                                gap: 10px; 
                                padding: 8px 12px; 
                                margin-bottom: 4px; 
                                border-radius: 8px;
                                cursor: pointer;
                                transition: var(--transition);
                            "
                                            onmouseover="this.style.background='#f8fafc'"
                                            onmouseout="this.style.background='transparent'">

                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                class="permission-checkbox" data-group="{{ strtolower($group) }}"
                                                {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                style="width: 16px; height: 16px; accent-color: var(--primary);">

                                            <span
                                                style="font-size: 0.9rem; color: var(--text-main);">{{ $permission->display_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center" style="padding: 2rem;">
                                <i class="ri-information-line"></i> Tidak ada permission yang tersedia.
                            </p>
                        @endforelse
                    </div>

                    @error('permissions')
                        <p class="lux-error-text" style="margin-top: 1rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end; align-items: center;">
                <span class="text-muted" style="font-size: 0.85rem; margin-right: auto;">
                    <i class="ri-time-line"></i> Terakhir diubah: {{ $role->updated_at->format('d M Y H:i') }}
                </span>
                <a href="{{ route('admin.roles.index') }}" class="lux-btn lux-btn-outline">
                    Batal
                </a>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-save-line"></i> Update Role
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // 1. Search Filter untuk Permissions
            const searchInput = document.getElementById('permissionSearch');
            const permissionItems = document.querySelectorAll('.lux-permission-item');
            const permissionGroups = document.querySelectorAll('.permission-group');

            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    const keyword = e.target.value.toLowerCase();

                    permissionItems.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        const parentGroup = item.closest('.permission-group');

                        if (text.includes(keyword)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Hide empty groups
                    permissionGroups.forEach(group => {
                        const visibleItems = group.querySelectorAll(
                            '.lux-permission-item[style="display: flex;"], .lux-permission-item:not([style*="display"])'
                            );
                        const hasVisible = Array.from(visibleItems).some(item => item.style
                            .display !== 'none');
                        group.style.display = hasVisible || keyword === '' ? 'block' : 'none';
                    });
                });
            }

            // 2. Select All per Group
            document.querySelectorAll('.select-all-group').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const group = this.dataset.group;
                    const groupCheckboxes = document.querySelectorAll(
                        `.permission-checkbox[data-group="${group}"]`);

                    groupCheckboxes.forEach(cb => {
                        // Hanya checkbox yang visible (tidak difilter)
                        if (cb.closest('.lux-permission-item').style.display !== 'none') {
                            cb.checked = this.checked;
                            updatePermissionItemStyle(cb);
                        }
                    });
                });
            });

            // 3. Visual Feedback & Update Select All State
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updatePermissionItemStyle(this);
                    updateSelectAllState(this.dataset.group);
                });
                // Init style
                updatePermissionItemStyle(checkbox);
            });

            function updatePermissionItemStyle(checkbox) {
                const label = checkbox.closest('.lux-permission-item');
                if (checkbox.checked) {
                    label.style.background = '#f0f9ff';
                    label.style.borderLeft = '3px solid var(--primary)';
                } else {
                    label.style.background = 'transparent';
                    label.style.borderLeft = '3px solid transparent';
                }
            }

            function updateSelectAllState(group) {
                const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                const visibleCheckboxes = Array.from(groupCheckboxes).filter(cb =>
                    cb.closest('.lux-permission-item').style.display !== 'none'
                );
                const selectAll = document.querySelector(`.select-all-group[data-group="${group}"]`);

                if (selectAll && visibleCheckboxes.length > 0) {
                    const allChecked = visibleCheckboxes.every(cb => cb.checked);
                    const someChecked = visibleCheckboxes.some(cb => cb.checked);

                    selectAll.checked = allChecked;
                    selectAll.indeterminate = someChecked && !allChecked;
                }
            }

            // Init select all states
            document.querySelectorAll('.select-all-group').forEach(checkbox => {
                updateSelectAllState(checkbox.dataset.group);
            });
        });
    </script>
@endpush
