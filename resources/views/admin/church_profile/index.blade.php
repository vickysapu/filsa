@extends('layouts.admin')

@section('title', 'Profile Gereja')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="ri-building-line me-2"></i>Profile Gereja</h4>
            <small class="text-muted-custom">Informasi identitas gereja Anda</small>
        </div>
        <div class="d-flex gap-2">
            @if ($churchProfile)
                <a href="{{ route('admin.church_profile.preview', $churchProfile) }}" class="btn btn-light">
                    <i class="ri-eye-line me-1"></i>Preview Publik
                </a>
                <a href="{{ route('admin.church_profile.edit', $churchProfile) }}" class="btn btn-custom">
                    <i class="ri-pencil-line me-1"></i>Edit Profile
                </a>
                @can('church_profile.delete')
                    <button class="btn btn-outline-danger"
                        onclick="confirmDelete('{{ $churchProfile->id }}', '{{ addslashes($churchProfile->church_name) }}')">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                @endcan
            @else
                <a href="{{ route('admin.church_profile.create') }}" class="btn btn-custom">
                    <i class="ri-add-line me-1"></i>Buat Profile Pertama
                </a>
            @endif
        </div>
    </div>

    @if (!$churchProfile)
        <div class="card card-custom text-center py-5">
            <div class="card-body">
                <i class="ri-building-line fs-1 text-muted mb-3"></i>
                <h5 class="mb-3">Belum ada profile gereja</h5>
                <p class="text-muted mb-4">Silakan buat profile gereja untuk mulai mengelola informasi publik.</p>
                <a href="{{ route('admin.church_profile.create') }}" class="btn btn-custom">
                    <i class="ri-add-line me-1"></i>Buat Profile Sekarang
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-custom text-center">
                    <div class="card-body">
                        <img src="{{ $churchProfile->logo_url }}" class="rounded mb-3" style="max-width:150px">
                        <h5 class="fw-bold">{{ $churchProfile->church_name }}</h5>
                        <p class="text-muted">{{ $churchProfile->tagline ?: '-' }}</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-custom">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="ri-list-check me-1"></i>Daftar Profile</span>
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama gereja..."
                    value="{{ request('search') }}" style="width:250px">
                <button type="submit" class="btn btn-sm btn-custom">Cari</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Logo</th>
                            <th>Nama Gereja</th>
                            <th>Tagline</th>
                            <th>Kontak</th>
                            <th width="100">Status</th>
                            <th width="180" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profiles as $profile)
                            <tr>
                                <td>
                                    <img src="{{ $profile->logo_url }}" alt="Logo" class="rounded"
                                        style="width:50px;height:50px;object-fit:cover">
                                </td>
                                <td>
                                    <div class="fw-500">{{ $profile->church_name }}</div>
                                    <small class="text-muted">{{ $profile->city ?? '-' }},
                                        {{ $profile->province ?? '' }}</small>
                                </td>
                                <td>{{ Str::limit($profile->tagline, 40) ?: '-' }}</td>
                                <td>
                                    <small class="d-block">{{ $profile->phone ?: '-' }}</small>
                                    <small class="text-muted">{{ $profile->email ?: '-' }}</small>
                                </td>
                                <td>
                                    @if ($profile->is_published)
                                        <span class="badge bg-success-subtle text-success">Published</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.church_profile.preview', $profile) }}"
                                        class="btn btn-sm btn-light" title="Preview"><i class="ri-eye-line"></i></a>
                                    <a href="{{ route('admin.church_profile.edit', $profile) }}"
                                        class="btn btn-sm btn-light" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <button class="btn btn-sm btn-light text-danger"
                                        onclick="deleteProfile('{{ $profile->id }}', '{{ addslashes($profile->church_name) }}')"
                                        title="Hapus"><i class="ri-delete-bin-line"></i></button>
                                    <button class="btn btn-sm {{ $profile->is_published ? 'btn-warning' : 'btn-success' }}"
                                        onclick="togglePublish('{{ $profile->id }}')"
                                        title="{{ $profile->is_published ? 'Unpublish' : 'Publish' }}">
                                        <i class="ri-{{ $profile->is_published ? 'eye-off' : 'eye' }}-line"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ri-building-line fs-1 d-block mb-3"></i>
                                    Belum ada data profile gereja
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="col-lg-8">
                @if ($churchProfile->history)
                    <div class="card card-custom mb-4">
                        <div class="card-header"><i class="ri-time-line me-1"></i>Sejarah</div>
                        <div class="card-body">{!! nl2br(e($churchProfile->history)) !!}</div>
                    </div>
                @endif

                <div class="row g-4">
                    @if ($churchProfile->vision)
                        <div class="col-md-6">
                            <div class="card card-custom h-100">
                                <div class="card-header"><i class="ri-eye-line me-1"></i>Visi</div>
                                <div class="card-body">{!! nl2br(e($churchProfile->vision)) !!}</div>
                            </div>
                        </div>
                    @endif
                    @if ($churchProfile->mission)
                        <div class="col-md-6">
                            <div class="card card-custom h-100">
                                <div class="card-header"><i class="ri-target-line me-1"></i>Misi</div>
                                <div class="card-body">{!! nl2br(e($churchProfile->mission)) !!}</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($churchProfile->documents)
                    <div class="card card-custom mt-4">
                        <div class="card-header"><i class="ri-file-list-line me-1"></i>Dokumen</div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach ($churchProfile->documents as $doc)
                                    <a href="{{ Storage::url($doc) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                        target="_blank">
                                        <span><i class="ri-file-line me-2"></i>{{ basename($doc) }}</span>
                                        <i class="ri-download-line"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            const confirmDelete = (id, name) => {
                if (!confirm(`Hapus profile "${name}"? Aksi ini tidak dapat dibatalkan.`)) return;
                fetch(`/admin/church-profile/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.message || 'Gagal menghapus');
                    })
                    .catch(() => alert('Kesalahan koneksi'));
            };
        </script>
    @endpush
@endsection
