@extends('layouts.admin')

@section('title', 'Manajemen Roles')

@section('content')
<div class="lux-container">
    
    <div class="flex-between mb-5">
        <div>
            <h1 class="text-xl text-bold">Manajemen Roles</h1>
            <p class="text-muted text-sm">Kelola hak akses pengguna</p>
        </div>
        @if(auth()->user()->hasPermission('roles.create'))
        <a href="{{ route('admin.roles.create') }}" class="lux-btn lux-btn-primary">
            <i class="ri-add-line"></i> Tambah Role
        </a>
        @endif
    </div>

    <div class="lux-card">
        <div class="lux-table-container">
            <table class="lux-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Role</th>
                        <th>Deskripsi</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>
                            <span class="text-bold">{{ $role->display_name }}</span>
                            <br>
                            <span class="text-muted text-sm">{{ $role->name }}</span>
                        </td>
                        <td>{{ $role->description ?? '-' }}</td>
                        <td>
                            <span class="lux-badge lux-badge-primary">
                                {{ $role->users_count }} Users
                            </span>
                        </td>
                        <td>
                            <span class="lux-badge lux-badge-info">
                                {{ $role->permissions_count }} Permissions
                            </span>
                        </td>
                        <td>
                            @if($role->is_active)
                            <span class="lux-badge lux-badge-success">Aktif</span>
                            @else
                            <span class="lux-badge lux-badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1">
                                @if(auth()->user()->hasPermission('roles.edit'))
                                <a href="{{ route('admin.roles.edit', $role) }}" class="lux-btn lux-btn-sm lux-btn-outline">
                                    <i class="ri-edit-line"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->hasPermission('roles.delete') && $role->name !== 'admin')
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lux-btn lux-btn-sm lux-btn-danger" 
                                            data-lux-confirm="Hapus role ini?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data role</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    </div>

</div>
@endsection