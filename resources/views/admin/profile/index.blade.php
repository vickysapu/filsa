@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Profil Saya</h1>
                <p class="text-muted-custom mb-0">Kelola informasi akun pribadi Anda</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <div class="row g-4">
            <!-- Form Profil -->
            <div class="col-lg-7">
                <div class="card-custom h-100">
                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-user-line me-2 text-primary"></i>Informasi Akun
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form id="formProfile">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control form-control-custom"
                                    value="{{ old('name', $user->name) }}" required maxlength="100"
                                    placeholder="Contoh: Budi Santoso">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control form-control-custom"
                                    value="{{ old('email', $user->email) }}" required maxlength="100"
                                    placeholder="email@perusahaan.com">
                            </div>

                            <div class="d-flex gap-2 pt-2">
                                <button type="submit" class="btn btn-custom" id="btnSaveProfile">
                                    <i class="ri-save-line me-1"></i> Simpan Perubahan
                                </button>
                                <button type="button" class="btn btn-outline-custom" id="btnReset">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Form Password -->
            <div class="col-lg-5">
                <div class="card-custom h-100">
                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-lock-line me-2 text-primary"></i>Ubah Password
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form id="formPassword">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Password Saat Ini <span
                                        class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control form-control-custom"
                                    required placeholder="••••••••" autocomplete="current-password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Password Baru <span
                                        class="text-danger">*</span></label>
                                <input type="password" name="password" id="newPassword"
                                    class="form-control form-control-custom" placeholder="••••••••"
                                    autocomplete="new-password">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-muted-custom">Konfirmasi Password Baru <span
                                        class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control form-control-custom"
                                    placeholder="••••••••" autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-custom w-100" id="btnSavePassword">
                                <i class="ri-key-line me-1"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Tambahan -->
            <div class="col-12">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted-custom d-block">Terdaftar Sejak</small>
                                <span class="fw-semibold">{{ $user->created_at?->format('d M Y') }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted-custom d-block">Last Login</small>
                                <span
                                    class="fw-semibold">{{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted-custom d-block">Status Akun</small>
                                <span
                                    class="badge {{ $user->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .page-wrapper {
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';

            // ===== FUNGSI UMUM: Show Alert =====
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;
                const alertClass = type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';
                container.innerHTML = `
            <div class="alert-custom ${alertClass}" data-auto-close>
                <i class="${icon}"></i><span>${message}</span>
                <button class="alert-close">&times;</button>
            </div>`;
                const alert = container.firstElementChild;
                setTimeout(() => {
                    if (alert) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
                alert?.querySelector('.alert-close')?.addEventListener('click', () => alert.remove());
            }

            // ===== FUNGSI UMUM: Handle Submit Form =====
            async function handleSubmit(formId, url, btnId, successMsg) {
                const form = document.getElementById(formId);
                const btn = document.getElementById(btnId);
                if (!form || !btn) return;

                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        return;
                    }

                    btn.disabled = true;
                    const originalBtnText = btn.innerHTML;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

                    const formData = new FormData(form);
                    const separator = url.includes('?') ? '&' : '?';
                    const fetchUrl = `${url}${separator}_token_nav=${currentToken}&_method=PUT`;

                    try {
                        const response = await fetch(fetchUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        const data = await response.json();
                        if (data.success) {
                            showAlert('success', successMsg);
                            if (formId === 'formProfile') {
                                // Update UI navbar jika nama berubah
                                document.querySelector('.user-name')?.textContent = data.user
                                    ?.name || form.querySelector('#name').value;
                            }
                            form.reset();
                            form.classList.remove('was-validated');
                        } else {
                            showAlert('error', data.message || 'Gagal memproses permintaan');
                        }
                    } catch (err) {
                        console.error(err);
                        showAlert('error', 'Terjadi kesalahan koneksi');
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                });
            }

            // ===== INIT: Profile Form =====
            handleSubmit('formProfile', '{{ route('profile.update') }}', 'btnSaveProfile',
                'Profil berhasil diperbarui!');

            // ===== INIT: Password Form =====
            handleSubmit('formPassword', '{{ route('profile.password.update') }}', 'btnSavePassword',
                'Password berhasil diubah!');

            // ===== Reset Button =====
            document.getElementById('btnReset')?.addEventListener('click', function() {
                document.getElementById('formProfile')?.reset();
                document.getElementById('formProfile')?.classList.remove('was-validated');
                showAlert('success', 'Form telah direset ke nilai awal');
            });

            // ===== Auto-close alerts (matching layout.admin) =====
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
@endsection
