@extends('layouts.admin')

@section('title', 'Manajemen Users')

@section('content')
<div class="lux-container">
    
    <div class="flex-between mb-5">
        <div>
            <h1 class="text-xl text-bold">Manajemen Users</h1>
            <p class="text-muted text-sm">Kelola akun pengguna sistem</p>
        </div>
        @if(auth()->user()->hasPermission('users.create'))
        <a href="{{ route('admin.users.create') }}" class="lux-btn lux-btn-primary">
            <i class="ri-add-line"></i> Tambah User
        </a>
        @endif
    </div>

    <div class="lux-card">
        <div class="lux-table-container">
            <table class="lux-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="lux-avatar lux-avatar-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-bold">{{ $user->name }}</p>
                                    <p class="text-muted text-sm">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @foreach($user->roles as $role)
                            <span class="lux-badge lux-badge-primary">{{ $role->display_name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->is_active)
                            <span class="lux-badge lux-badge-success">Aktif</span>
                            @else
                            <span class="lux-badge lux-badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                        </td>
                        <td>
                            <div class="flex gap-1">
                                @if(auth()->user()->hasPermission('users.edit'))
                                <a href="{{ route('admin.users.edit', $user) }}" class="lux-btn lux-btn-sm lux-btn-outline" data-modal-target="#modalTambahData">
                                    <i class="ri-edit-line"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lux-btn lux-btn-sm lux-btn-danger" 
                                            data-lux-confirm="Hapus user ini?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada data user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection