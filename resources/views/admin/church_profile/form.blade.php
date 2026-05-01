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
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $churchProfile->address ?? '') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kota</label>
                                <input type="text" name="city" class="form-control"
                                    value="{{ old('city', $churchProfile->city ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Provinsi</label>
                                <input type="text" name="province" class="form-control"
                                    value="{{ old('province', $churchProfile->province ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="postal_code" class="form-control"
                                    value="{{ old('postal_code', $churchProfile->postal_code ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Negara</label>
                                <input type="text" name="country" class="form-control"
                                    value="{{ old('country', $churchProfile->country ?? 'Indonesia') }}">
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
                        <div class="mb-0">
                            <label class="form-label">Nilai-Nilai (Values)</label>
                            <div id="valuesContainer">
                                @php $values = old('values', $churchProfile->values ?? []); @endphp
                                @forelse($values as $index => $value)
                                    <div class="input-group mb-2">
                                        <input type="text" name="values[{{ $index }}][label]"
                                            class="form-control" placeholder="Nama nilai"
                                            value="{{ $value['label'] ?? '' }}" required>
                                        <input type="text" name="values[{{ $index }}][description]"
                                            class="form-control" placeholder="Deskripsi singkat"
                                            value="{{ $value['description'] ?? '' }}">
                                        <button type="button" class="btn btn-outline-danger"
                                            onclick="removeValue(this)">×</button>
                                    </div>
                                @empty
                                    <div class="input-group mb-2">
                                        <input type="text" name="values[0][label]" class="form-control"
                                            placeholder="Nama nilai" required>
                                        <input type="text" name="values[0][description]" class="form-control"
                                            placeholder="Deskripsi singkat">
                                        <button type="button" class="btn btn-outline-danger"
                                            onclick="removeValue(this)">×</button>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addValue()">
                                <i class="ri-add-line"></i> Tambah Nilai
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-calendar-line me-1"></i>Jadwal Ibadah</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ibadah Minggu</label>
                                <input type="time" name="service_time_sunday" class="form-control"
                                    value="{{ old('service_time_sunday', $churchProfile->service_time_sunday ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ibadah Rabu</label>
                                <input type="time" name="service_time_wednesday" class="form-control"
                                    value="{{ old('service_time_wednesday', $churchProfile->service_time_wednesday ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ibadah Jumat</label>
                                <input type="time" name="service_time_friday" class="form-control"
                                    value="{{ old('service_time_friday', $churchProfile->service_time_friday ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan Tambahan</label>
                                <textarea name="service_note" class="form-control" rows="2" maxlength="500">{{ old('service_note', $churchProfile->service_note ?? '') }}</textarea>
                            </div>
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
                        <div class="mb-3">
                            <label class="form-label">Galeri Foto</label>
                            <input type="file" name="gallery_images[]" class="form-control" multiple
                                accept="image/*">
                            @if (isset($churchProfile) && $churchProfile->gallery_images)
                                <div class="d-flex flex-wrap gap-2 mt-2" id="galleryPreview">
                                    @foreach ($churchProfile->gallery_images as $img)
                                        <div class="position-relative">
                                            <img src="{{ Storage::url($img) }}" class="rounded"
                                                style="width:80px;height:60px;object-fit:cover">
                                            <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0"
                                                style="width:20px;height:20px;border-radius:50%"
                                                onclick="removeGalleryImage('{{ $img }}')">×</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Dokumen</label>
                            <input type="file" name="documents[]" class="form-control" multiple
                                accept=".pdf,.doc,.docx">
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

                <div class="card card-custom mb-4">
                    <div class="card-header"><i class="ri-seo-line me-1"></i>SEO</div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control form-control-sm" maxlength="255"
                                value="{{ old('meta_title', $churchProfile->meta_title ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Meta Description</label>
                            <textarea name="meta_description" class="form-control form-control-sm" rows="2" maxlength="500">{{ old('meta_description', $churchProfile->meta_description ?? '') }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Keywords (pisahkan dengan koma)</label>
                            <input type="text" name="meta_keywords[]" class="form-control form-control-sm"
                                value="{{ old('meta_keywords', $churchProfile->meta_keywords ?? []) ? implode(', ', $churchProfile->meta_keywords) : '' }}"
                                placeholder="gereja, ibadah, komunitas">
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

        #valuesContainer .input-group {
            transition: all 0.2s;
        }

        #valuesContainer .input-group:hover {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 4px;
        }
    </style>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const addValue = () => {
            const container = document.getElementById('valuesContainer');
            const index = container.children.length;
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" name="values[${index}][label]" class="form-control" placeholder="Nama nilai" required>
                <input type="text" name="values[${index}][description]" class="form-control" placeholder="Deskripsi singkat">
                <button type="button" class="btn btn-outline-danger" onclick="removeValue(this)">×</button>
            `;
            container.appendChild(div);
        };

        const removeValue = (btn) => btn.closest('.input-group').remove();

        const removeGalleryImage = async (path) => {
            if (!confirm('Hapus gambar ini?')) return;
            const id = '{{ $churchProfile->id ?? '' }}';
            if (!id) return;
            try {
                const res = await fetch(`/admin/church-profile/${id}/remove-gallery`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        image_path: path
                    })
                });
                const result = await res.json();
                if (result.success) location.reload();
            } catch (e) {
                alert('Gagal menghapus');
            }
        };

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
