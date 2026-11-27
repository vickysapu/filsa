@extends('layouts.admin')

@section('title', isset($churchProfile) ? 'Edit Profile' : 'Tambah Profile')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="ri-building-line me-2"></i>{{ $churchProfile ? 'Edit' : 'Tambah' }} Profile Gereja</h4>
            <small class="text-muted-custom">Lengkapi informasi identitas gereja Anda</small>
        </div>
        <a href="{{ route('admin.church_profile.index') }}" class="btn btn-light">
            <i class="ri-arrow-left-line me-1"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="profileForm"
        action="{{ $churchProfile
            ? route('admin.church_profile.update', $churchProfile)
            : route('admin.church_profile.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if ($churchProfile)
            @method('PUT')
        @endif

        <div class="row g-4">

            {{-- KIRI --}}
            <div class="col-lg-8">

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-information-line me-1"></i>Informasi Gereja</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Gereja <span class="text-danger">*</span></label>
                                <input type="text" name="church_name" class="form-control" maxlength="255" required
                                    value="{{ old('church_name', $churchProfile->church_name ?? '') }}">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Alamat</label>
                                <input type="text" name="address" class="form-control" maxlength="1000"
                                    value="{{ old('address', $churchProfile->address ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Kota</label>
                                <input type="text" name="city" class="form-control" maxlength="100"
                                    value="{{ old('city', $churchProfile->city ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="phone" class="form-control" maxlength="50"
                                    value="{{ old('phone', $churchProfile->phone ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" maxlength="255"
                                    value="{{ old('email', $churchProfile->email ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-book-line me-1"></i>Sejarah, Visi & Misi</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Sejarah</label>
                            <textarea name="history" class="form-control" rows="5">{{ old('history', $churchProfile->history ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Visi</label>
                            <textarea name="vision" class="form-control" rows="3">{{ old('vision', $churchProfile->vision ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Misi</label>
                            <textarea name="mission" class="form-control" rows="3">{{ old('mission', $churchProfile->mission ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KANAN --}}
            <div class="col-lg-4">

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-image-line me-1"></i>Logo</div>
                    <div class="card-body">
                        @if ($churchProfile?->logo_path)
                            <img src="{{ $churchProfile->logo_url }}" class="rounded mb-2"
                                style="max-width:150px;height:auto">
                        @endif
                        <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-muted">Maksimal 5 MB. Kosongkan jika tidak ingin mengganti.</small>
                    </div>
                </div>

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-links-line me-1"></i>Sosial Media</div>
                    <div class="card-body">
                        @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $field => $label)
                            <div class="{{ $loop->last ? 'mb-0' : 'mb-2' }}">
                                <label class="form-label small">{{ $label }}</label>
                                <input type="url" name="{{ $field }}" class="form-control form-control-sm"
                                    maxlength="255" placeholder="https://"
                                    value="{{ old($field, $churchProfile->$field ?? '') }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card card-custom">
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published"
                                value="1"
                                {{ old('is_published', $churchProfile->is_published ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Publikasikan Profile</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom">
                                <i class="ri-save-line me-1"></i>Simpan Profile
                            </button>
                            <a href="{{ route('admin.church_profile.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.getElementById('profileForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="ri-loader-4-line me-1"></i>Menyimpan...';
            }
        });
    </script>
@endsection