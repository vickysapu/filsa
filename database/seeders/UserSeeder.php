<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'vickypplg12@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('p4ssword'),
                'is_active' => true,
            ]
        );
        $admin->assignRole([Role::where('name', 'admin')->first()->id]);
    }
}