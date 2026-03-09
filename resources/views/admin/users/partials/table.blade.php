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
            @foreach ($user->roles as $role)
                <span class="lux-badge lux-badge-primary">{{ $role->display_name }}</span>
            @endforeach
        </td>
        <td>
            @if ($user->is_active)
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
                @if (auth()->user()->hasPermission('users.edit'))
                    <button class="lux-btn lux-btn-sm lux-btn-outline btn-edit" data-id="{{ $user->id }}">
                        <i class="ri-edit-line"></i>
                    </button>
                @endif

                @if (auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                    <button class="lux-btn lux-btn-sm lux-btn-danger btn-delete" data-id="{{ $user->id }}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center text-muted py-4">Belum ada data user</td>
    </tr>
@endforelse
