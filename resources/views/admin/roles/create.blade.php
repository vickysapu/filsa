@extends('layouts.admin')

@section('title', 'Tambah Role')

@section('content')
<div class="lux-container">
    
    <div class="mb-5">
        <a href="{{ route('admin.roles.index') }}" class="lux-btn lux-btn-ghost">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
        <h1 class="text-xl text-bold mt-3">{{ isset($role) ? 'Edit' : 'Tambah' }} Role</h1>
    </div>

    <form action="{{ isset($role) ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST">
        @csrf
        @if(isset($role)) @method('PUT') @endif

        <div class="lux-grid lux-grid-2">
            <div class="lux-card">
                <h3 class="lux-card-title mb-4">Informasi Role</h3>
                
                <div class="lux-form-group">
                    <label class="lux-label">Nama Role *</label>
                    <input type="text" name="name" class="lux-input @error('name') lux-input-danger @enderror" 
                           value="{{ old('name', $role->name ?? '') }}" 
                           {{ isset($role) ? 'readonly' : '' }}
                           placeholder="contoh: admin, manager, staff">
                    @error('name')
                    <p class="lux-form-error">{{ $message }}</p>
                    @enderror
                    @if(isset($role))
                    <p class="lux-form-help">Nama role tidak dapat diubah setelah dibuat</p>
                    @endif
                </div>

                <div class="lux-form-group">
                    <label class="lux-label">Display Name *</label>
                    <input type="text" name="display_name" class="lux-input @error('display_name') lux-input-danger @enderror" 
                           value="{{ old('display_name', $role->display_name ?? '') }}" 
                           placeholder="contoh: Administrator, Manager">
                    @error('display_name')
                    <p class="lux-form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lux-form-group">
                    <label class="lux-label">Deskripsi</label>
                    <textarea name="description" class="lux-textarea" rows="3" 
                              placeholder="Deskripsi role ini...">{{ old('description', $role->description ?? '') }}</textarea>
                    @error('description')
                    <p class="lux-form-error">{{ $message }}</p>
                    @enderror
                </div>

                @if(isset($role))
                <div class="lux-form-group">
                    <label class="lux-checkbox">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $role->is_active) ? 'checked' : '' }}>
                        Role Aktif
                    </label>
                </div>
                @endif
            </div>

            <div class="lux-card">
                <h3 class="lux-card-title mb-4">Permissions</h3>
                <p class="text-muted text-sm mb-4">Pilih hak akses untuk role ini</p>

                @foreach($permissionGroups as $group => $permissions)
                <div class="mb-4">
                    <h4 class="text-bold text-primary mb-2">{{ ucfirst($group) }}</h4>
                    <div class="lux-grid lux-grid-2">
                        @foreach($permissions as $permission)
                        <label class="lux-checkbox">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                   {{ in_array($permission->id, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                            {{ $permission->display_name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @error('permissions')
                <p class="lux-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex gap-2 mt-5">
            <button type="submit" class="lux-btn lux-btn-primary">
                <i class="ri-save-line"></i> Simpan
            </button>
            <a href="{{ route('admin.roles.index') }}" class="lux-btn lux-btn-ghost">
                Batal
            </a>
        </div>
    </form>

</div>
@endsection