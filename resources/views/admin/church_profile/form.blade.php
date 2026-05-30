@extends('layouts.admin')

@section('title', isset($churchProfile) ? 'Edit Profile' : 'Tambah Profile')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="ri-building-line me-2"></i>{{ isset($churchProfile) ? 'Edit' : 'Tambah' }} Profile
                Gereja</h4>
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
        action="{{ isset($churchProfile) ? route('admin.church_profile.update', $churchProfile) : route('admin.church_profile.store') }}"
        method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf @if (isset($churchProfile))
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-information-line me-1"></i>Informasi Dasar</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Gereja *</label>
                                <input type="text" name="church_name" class="form-control"
                                    value="{{ old('church_name', $churchProfile->church_name ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tagline</label>
                                <input type="text" name="tagline" class="form-control" maxlength="255"
                                    value="{{ old('tagline', $churchProfile->tagline ?? '') }}"
                                    placeholder="Contoh: Melayani dengan Kasih">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap *</label>
                                <textarea name="address" class="form-control" rows="3" required>{{ old('address', $churchProfile->address ?? '') }}</textarea>
                            </div>
                            @if (!$churchProfile?->branch_id)
                                <div class="col-md-6">
                                    <label class="form-label">Cabang</label>
                                    <select name="branch_id" class="form-select">
                                        <option value="">Pilih Cabang</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id', $churchProfile->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-book-line me-1"></i>Sejarah & Visi Misi</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Sejarah Gereja</label>
                            <textarea name="history" class="form-control rich-text" rows="5">{{ old('history', $churchProfile->history ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Visi</label>
                            <textarea name="vision" class="form-control rich-text" rows="3">{{ old('vision', $churchProfile->vision ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Misi</label>
                            <textarea name="mission" class="form-control rich-text" rows="3">{{ old('mission', $churchProfile->mission ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-image-line me-1"></i>Media</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Logo Gereja</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            @if (isset($churchProfile) && $churchProfile->logo_path)
                                <div class="mt-2">
                                    <img src="{{ $churchProfile->logo_url }}" class="rounded" style="max-width:150px">
                                </div>
                            @endif
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Dokumen</label>
                            <input type="file" name="documents[]" class="form-control" multiple accept=".pdf,.doc,.docx">
                            @if (isset($churchProfile) && $churchProfile->documents)
                                <div class="mt-2">
                                    @foreach ($churchProfile->documents as $doc)
                                        <div class="d-flex justify-content-between align-items-center small mb-1">
                                            <span><i class="ri-file-line"></i> {{ basename($doc) }}</span>
                                            <button type="button" class="btn btn-sm text-danger p-0"
                                                onclick="removeDocument('{{ $doc }}')"><i
                                                    class="ri-close-line"></i></button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-links-line me-1"></i>Kontak & Sosial</div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small">Telepon</label>
                            <input type="text" name="phone" class="form-control form-control-sm"
                                value="{{ old('phone', $churchProfile->phone ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control form-control-sm"
                                value="{{ old('whatsapp', $churchProfile->whatsapp ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm"
                                value="{{ old('email', $churchProfile->email ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Website</label>
                            <input type="url" name="website" class="form-control form-control-sm"
                                value="{{ old('website', $churchProfile->website ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Facebook</label>
                            <input type="url" name="facebook" class="form-control form-control-sm"
                                value="{{ old('facebook', $churchProfile->facebook ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Instagram</label>
                            <input type="url" name="instagram" class="form-control form-control-sm"
                                value="{{ old('instagram', $churchProfile->instagram ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">YouTube</label>
                            <input type="url" name="youtube" class="form-control form-control-sm"
                                value="{{ old('youtube', $churchProfile->youtube ?? '') }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">TikTok</label>
                            <input type="url" name="tiktok" class="form-control form-control-sm"
                                value="{{ old('tiktok', $churchProfile->tiktok ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-map-pin-line me-1"></i>Lokasi</div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small">Latitude</label>
                            <input type="text" name="map_latitude" class="form-control form-control-sm"
                                value="{{ old('map_latitude', $churchProfile->map_latitude ?? '') }}"
                                placeholder="-6.2088">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Longitude</label>
                            <input type="text" name="map_longitude" class="form-control form-control-sm"
                                value="{{ old('map_longitude', $churchProfile->map_longitude ?? '') }}"
                                placeholder="106.8456">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Embed Map Code</label>
                            <textarea name="map_embed_code" class="form-control form-control-sm" rows="2"
                                placeholder='<iframe src="..."></iframe>'>{{ old('map_embed_code', $churchProfile->map_embed_code ?? '') }}</textarea>
                        </div>
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

    <style>
        .rich-text {
            font-family: inherit;
            min-height: 120px;
        }
    </style>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const removeDocument = async (path) => {
            if (!confirm('Hapus dokumen ini?')) return;
            const id = '{{ $churchProfile->id ?? '' }}';
            if (!id) return;
            try {
                const res = await fetch(`/admin/church-profile/${id}/remove-document`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        doc_path: path
                    })
                });
                const result = await res.json();
                if (result.success) location.reload();
            } catch (e) {
                alert('Gagal menghapus');
            }
        };

        document.getElementById('profileForm')?.addEventListener('submit', (e) => {
            if (!e.target.checkValidity()) {
                e.preventDefault();
                e.target.classList.add('was-validated');
            }
        });
    </script>
@endsection
