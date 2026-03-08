@extends('layouts.admin')

@section('title', 'Manajemen Permissions')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Manajemen Permissions</h1>
                <p class="text-muted">Kelola hak akses sistem untuk roles dan users</p>
            </div>
            @if (auth()->user()->hasPermission('permissions.create'))
                <button class="lux-btn lux-btn-primary" data-lux-modal="modalPermission">
                    <i class="ri-add-line"></i> Tambah Permission
                </button>
            @endif
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--success); background: #f0fdf4; display: flex; align-items: center; gap: 10px;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Alert Error -->
        @if (session('error'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--danger); background: #fef2f2; display: flex; align-items: center; gap: 10px;">
                <i class="ri-error-warning-line" style="color: var(--danger); font-size: 1.5rem;"></i>
                <span style="color: #991b1b; font-weight: 600;">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Permission Groups -->
        @foreach ($permissionGroups as $group => $permissions)
            <div class="lux-card mb-4">
                <div class="lux-card-header">
                    <h3 class="lux-card-title">{{ ucfirst($group) }}</h3>
                    <span class="lux-badge lux-badge-info">{{ count($permissions) }} Permissions</span>
                </div>

                <div class="lux-grid lux-grid-4">
                    @foreach ($permissions as $permission)
                        <div class="lux-permission-item"
                            style="
                border: 1px solid #e2e8f0; 
                border-radius: var(--radius); 
                padding: 1rem; 
                display: flex; 
                flex-direction: column; 
                gap: 0.75rem;
                transition: var(--transition);
                background: white;
            "
                            onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='var(--shadow-md)'"
                            onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">

                            <!-- Info Permission -->
                            <div>
                                <p style="font-weight: 600; color: var(--text-main); margin-bottom: 4px;">
                                    {{ $permission->display_name }}
                                </p>
                                <p class="text-muted" style="font-size: 0.8rem; font-family: monospace;">
                                    {{ $permission->name }}
                                </p>
                                @if ($permission->description)
                                    <p class="text-muted" style="font-size: 0.8rem; margin-top: 4px;">
                                        {{ $permission->description }}
                                    </p>
                                @endif
                            </div>

                            <!-- Stats & Actions -->
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                <span
                                    class="lux-badge {{ $permission->roles_count > 0 ? 'lux-badge-warning' : 'lux-badge-success' }}">
                                    {{ $permission->roles_count }} Roles
                                </span>

                                @if (auth()->user()->hasPermission('permissions.delete'))
                                    @if ($permission->roles_count > 0)
                                        <!-- Tombol Disabled jika masih dipakai role -->
                                        <button class="lux-btn lux-btn-outline lux-btn-sm"
                                            style="border-color: #cbd5e1; color: #94a3b8; cursor: not-allowed;" disabled
                                            title="Tidak dapat dihapus: Permission ini masih digunakan oleh {{ $permission->roles_count }} role">
                                            <i class="ri-lock-line"></i>
                                        </button>
                                    @else
                                        <!-- Tombol Hapus Aktif (Gunakan data-attributes) -->
                                        <button class="lux-btn lux-btn-outline lux-btn-sm btn-delete-trigger"
                                            style="border-color: var(--danger); color: var(--danger);"
                                            data-id="{{ $permission->id }}"
                                            data-name="{{ addslashes($permission->display_name) }}"
                                            title="Hapus Permission">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Tambah Permission -->
    <div class="lux-modal-overlay" id="modalPermission" style="display: none;">
        <div class="lux-modal"
            style="
        position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: white; border-radius: var(--radius); width: 90%; max-width: 500px;
        box-shadow: var(--shadow-lg); z-index: 1001;
    ">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="lux-modal-header"
                    style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="lux-modal-title" style="font-weight: 700;">Tambah Permission</h3>
                    <button type="button" class="lux-modal-close" onclick="closeModal('modalPermission')"
                        style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <div class="lux-modal-body" style="padding: 1.5rem;">
                    <div class="lux-form-group">
                        <label class="lux-label">Nama Permission *</label>
                        <input type="text" name="name" class="lux-input" placeholder="contoh: users.create" required>
                        <p class="lux-form-help"><i class="ri-information-line"></i> Format: group.action (users.create,
                            roles.view, dll)</p>
                    </div>
                    <div class="lux-form-group">
                        <label class="lux-label">Display Name *</label>
                        <input type="text" name="display_name" class="lux-input" placeholder="contoh: Buat User"
                            required>
                    </div>
                    <div class="lux-form-group">
                        <label class="lux-label">Group *</label>
                        <input type="text" name="group" class="lux-input" placeholder="contoh: users, roles, settings"
                            required>
                    </div>
                    <div class="lux-form-group">
                        <label class="lux-label">Deskripsi</label>
                        <textarea name="description" class="lux-textarea" rows="2" placeholder="Jelaskan fungsi permission ini..."></textarea>
                    </div>
                </div>
                <div class="lux-modal-footer"
                    style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" class="lux-btn lux-btn-outline"
                        onclick="closeModal('modalPermission')">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary">Simpan</button>
                </div>
            </form>
        </div>
        <!-- Overlay Background -->
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;"
            onclick="closeModal('modalPermission')"></div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="lux-modal-overlay" id="modalDelete" style="display: none;">
        <div class="lux-modal"
            style="
        position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: white; border-radius: var(--radius); width: 90%; max-width: 450px;
        box-shadow: var(--shadow-lg); z-index: 1001;
    ">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="lux-modal-header"
                    style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px;">
                    <i class="ri-error-warning-line" style="color: var(--danger); font-size: 1.5rem;"></i>
                    <h3 class="lux-modal-title" style="font-weight: 700; color: var(--danger);">Konfirmasi Hapus</h3>
                </div>
                <div class="lux-modal-body" style="padding: 1.5rem;">
                    <p style="margin-bottom: 1rem;">
                        Anda yakin ingin menghapus permission <strong id="deletePermissionName"></strong>?
                    </p>
                    <div
                        style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius); padding: 1rem; color: #991b1b; font-size: 0.9rem;">
                        <i class="ri-alert-line"></i> <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.
                        Pastikan permission ini tidak digunakan di mana pun.
                    </div>
                </div>
                <div class="lux-modal-footer"
                    style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" class="lux-btn lux-btn-outline"
                        onclick="closeModal('modalDelete')">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary" style="background: var(--danger);">
                        <i class="ri-delete-bin-line"></i> Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
        <!-- Overlay Background -->
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;"
            onclick="closeModal('modalDelete')"></div>
    </div>

@endsection

@push('scripts')
    <script>
        /**
         * 🐛 DEBUG LOGGER - Helper untuk logging konsisten
         */
        const DebugLog = {
            prefix: '[PERMISSIONS]',
            log: (msg, data = null) => {
                console.log(`${DebugLog.prefix} ${msg}`, data || '');
            },
            warn: (msg, data = null) => {
                console.warn(`${DebugLog.prefix} ⚠️ ${msg}`, data || '');
            },
            error: (msg, data = null) => {
                console.error(`${DebugLog.prefix} ❌ ${msg}`, data || '');
            },
            info: (msg, data = null) => {
                console.info(`${DebugLog.prefix} ℹ️ ${msg}`, data || '');
            }
        };

        document.addEventListener('DOMContentLoaded', function() {

            DebugLog.info('=== SCRIPT INITIALIZED ===');
            DebugLog.log('DOM loaded, starting setup...');

            // ========================================
            // 1. SETUP MODAL "TAMBAH PERMISSION"
            // ========================================
            const addModalTriggers = document.querySelectorAll('[data-lux-modal]');
            DebugLog.log(`Found ${addModalTriggers.length} modal trigger buttons`);

            addModalTriggers.forEach((btn, index) => {
                DebugLog.log(`Binding trigger #${index + 1}:`, {
                    modalId: btn.dataset.luxModal,
                    element: btn
                });

                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    DebugLog.info('🔘 Add modal trigger clicked');

                    const modalId = this.dataset.luxModal;
                    DebugLog.log(`Target modal ID: ${modalId}`);

                    const modal = document.getElementById(modalId);
                    if (modal) {
                        DebugLog.log('✅ Modal element found, showing...');
                        modal.style.display = 'block';
                        document.body.style.overflow = 'hidden';
                        DebugLog.info(`📦 Modal '${modalId}' opened successfully`);
                    } else {
                        DebugLog.error(`❌ Modal element '#${modalId}' NOT FOUND!`);
                    }
                });
            });

            // ========================================
            // 2. GLOBAL CLOSE MODAL FUNCTION
            // ========================================
            window.closeModal = function(modalId) {
                DebugLog.info(`🔒 closeModal() called for: ${modalId}`);

                const modal = document.getElementById(modalId);
                if (modal) {
                    DebugLog.log('✅ Modal element found, hiding...');
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                    DebugLog.info(`Modal '${modalId}' closed successfully`);
                    return true;
                } else {
                    DebugLog.error(`❌ closeModal: Modal '#${modalId}' NOT FOUND!`);
                    return false;
                }
            };

            // ========================================
            // 3. SETUP DELETE BUTTONS
            // ========================================
            const deleteButtons = document.querySelectorAll('.btn-delete-trigger');
            DebugLog.log(`Found ${deleteButtons.length} delete trigger buttons`);

            deleteButtons.forEach((btn, index) => {
                DebugLog.log(`Binding delete button #${index + 1}:`, {
                    id: btn.dataset.id,
                    name: btn.dataset.name,
                    element: btn
                });

                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    DebugLog.info('🗑️ Delete button clicked');

                    const permissionId = this.dataset.id;
                    const permissionName = this.dataset.name;

                    DebugLog.log(`Permission data:`, {
                        id: permissionId,
                        name: permissionName
                    });

                    // Validasi data
                    if (!permissionId) {
                        DebugLog.error('❌ permissionId is empty! Check data-id attribute.');
                        return;
                    }

                    // Cari elemen modal
                    const nameElement = document.getElementById('deletePermissionName');
                    const form = document.getElementById('deleteForm');
                    const modal = document.getElementById('modalDelete');

                    DebugLog.log('Modal elements lookup:', {
                        nameElement: !!nameElement,
                        form: !!form,
                        modal: !!modal
                    });

                    if (nameElement && form && modal) {
                        DebugLog.info('✅ All modal elements found, preparing to show...');

                        // Set data ke modal
                        nameElement.textContent = permissionName;
                        form.action = '/admin/permissions/' + permissionId;

                        DebugLog.log(`Form action set to: ${form.action}`);

                        // Tampilkan modal
                        modal.style.display = 'block';
                        document.body.style.overflow = 'hidden';

                        DebugLog.info('✅ Delete modal opened successfully');

                        // Visual feedback di console
                        console.groupCollapsed('🔍 Delete Modal Details');
                        console.log('Permission Name:', permissionName);
                        console.log('Permission ID:', permissionId);
                        console.log('Form Action:', form.action);
                        console.log('Modal Display:', modal.style.display);
                        console.groupEnd();

                    } else {
                        DebugLog.error('❌ One or more modal elements NOT FOUND!', {
                            nameElement: nameElement ? 'OK' : 'MISSING',
                            form: form ? 'OK' : 'MISSING',
                            modal: modal ? 'OK' : 'MISSING'
                        });
                    }
                });
            });

            // ========================================
            // 4. OVERLAY CLICK TO CLOSE
            // ========================================
            const overlays = document.querySelectorAll('.lux-modal-overlay');
            DebugLog.log(`Found ${overlays.length} modal overlays`);

            overlays.forEach(overlay => {
                overlay.addEventListener('click', function(e) {
                    // Hanya tutup jika klik di overlay background, bukan di modal content
                    if (e.target === this) {
                        DebugLog.info('🖱️ Overlay background clicked, closing modal...');
                        const modalId = this.id;
                        closeModal(modalId);
                    } else {
                        DebugLog.log('Click inside modal content, ignoring...');
                    }
                });
            });

            // ========================================
            // 5. KEYBOARD: ESC TO CLOSE
            // ========================================
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    DebugLog.info('⌨️ ESC key pressed');
                    const closed1 = closeModal('modalPermission');
                    const closed2 = closeModal('modalDelete');
                    if (closed1 || closed2) {
                        DebugLog.info('✅ Modal(s) closed via ESC key');
                    } else {
                        DebugLog.warn('⚠️ No active modal to close with ESC');
                    }
                }
            });

            // ========================================
            // 6. DEBUG: CHECK MODAL VISIBILITY ON LOAD
            // ========================================
            DebugLog.info('=== INITIAL MODAL STATE ===');
            ['modalPermission', 'modalDelete'].forEach(id => {
                const modal = document.getElementById(id);
                if (modal) {
                    DebugLog.log(`${id}:`, {
                        display: modal.style.display,
                        computedDisplay: window.getComputedStyle(modal).display,
                        hasActiveClass: modal.classList.contains('active')
                    });
                } else {
                    DebugLog.warn(`Modal '#${id}' not found in DOM!`);
                }
            });

            DebugLog.info('=== SETUP COMPLETE ===');
        });

        // ========================================
        // DEBUG: Global error handler untuk catch JS errors
        // ========================================
        window.addEventListener('error', function(e) {
            console.error('💥 GLOBAL JS ERROR:', {
                message: e.message,
                source: e.filename,
                lineno: e.lineno,
                colno: e.colno,
                error: e.error
            });
        });

        // Debug: Catch unhandled promise rejections
        window.addEventListener('unhandledrejection', function(e) {
            console.error('💥 UNHANDLED PROMISE REJECTION:', e.reason);
        });
    </script>
@endpush
