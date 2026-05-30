@extends('layouts.admin')

@section('title', 'Profile Gereja')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="ri-building-line me-2"></i>Profile Gereja</h4>
            <small class="text-muted-custom">Kelola informasi dan identitas gereja Anda</small>
        </div>
        <a href="{{ route('admin.church_profile.create') }}" class="btn btn-custom">
            <i class="ri-add-line me-1"></i>Tambah Profile
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
        </div>
        @if ($profiles->hasPages())
            <div class="card-footer">{{ $profiles->withQueryString()->links() }}</div>
        @endif
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const deleteProfile = async (id, name) => {
            if (!confirm(`Hapus profile "${name}"? Aksi ini tidak dapat dibatalkan.`)) return;
            try {
                const res = await fetch(`/admin/church-profile/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const result = await res.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Gagal menghapus');
                }
            } catch (e) {
                alert('Kesalahan koneksi');
            }
        };

        const togglePublish = async (id) => {
            try {
                const res = await fetch(`/admin/church-profile/${id}/toggle-publish`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const result = await res.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Gagal mengubah status');
                }
            } catch (e) {
                alert('Kesalahan koneksi');
            }
        };
    </script>
@endsection
