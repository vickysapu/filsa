@extends('layouts.admin')

@section('title', 'Tambah User')

@section('content')
<div class="lux-container">
    
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="lux-btn lux-btn-ghost">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
        <h1 class="text-xl text-bold mt-3">{{ isset($user) ? 'Edit' : 'Tambah' }} User</h1>
    </div>

    <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="lux-grid lux-grid-2">
            <div class="lux-card">
                <h3 class="lux-card-title mb-4">Informasi Akun</h3>
                
                <div class="lux-form-group">
                    <label class="lux-label">Nama Lengkap *</label>
                    <input type="text" name="name" class="lux-input @error('name') lux-input-danger @enderror" 
                           value="{{ old('name', $user->name ?? '') }}" placeholder="Nama lengkap">
                    @error('name')
                    <p class="lux-form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lux-form-group">
                    <label class="lux-label">Email *</label>
                    <input type="email" name="email" class="lux-input @error('email') lux-input-danger @enderror" 
                           value="{{ old('email', $user->email ?? '') }}" placeholder="email@contoh.com">
                    @error('email')
                    <p class="lux-form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lux-form-group">
                    <label class="lux-label">{{ isset($user) ? 'Password Baru' : 'Password' }} 
                        @if(isset($user)) <span class="text-muted">(kosongkan jika tidak ingin mengubah)</span> @endif *
                    </label>
                    <input type="password" name="password" class="lux-input @error('password') lux-input-danger @enderror" 
                           placeholder="••••••••">
                    @error('password')
                    <p class="lux-form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lux-form-group">
                    <label class="lux-label">Konfirmasi Password *</label>
                    <input type="password" name="password_confirmation" class="lux-input" 
                           placeholder="••••••••">
                </div>

                @if(isset($user))
                <div class="lux-form-group">
                    <label class="lux-checkbox">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        Akun Aktif
                    </label>
                </div>
                @endif
            </div>

            <div class="lux-card">
                <h3 class="lux-card-title mb-4">Role Assignment</h3>
                <p class="text-muted text-sm mb-4">Pilih role untuk user ini</p>

                @foreach($roles as $role)
                <label class="lux-checkbox mb-3">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                           {{ in_array($role->id, old('roles', $userRoles ?? [])) ? 'checked' : '' }}>
                    <div>
                        <span class="text-bold">{{ $role->display_name }}</span>
                        <br>
                        <span class="text-muted text-sm">{{ $role->description ?? 'Tidak ada deskripsi' }}</span>
                    </div>
                </label>
                @endforeach

                @error('roles')
                <p class="lux-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex gap-2 mt-5">
            <button type="submit" class="lux-btn lux-btn-primary">
                <i class="ri-save-line"></i> Simpan
            </button>
            <a href="{{ route('admin.users.index') }}" class="lux-btn lux-btn-ghost">
                Batal
            </a>
        </div>
    </form>

</div>
@endsection