<?php

namespace Database\Seeders;

use App\Models\brances;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $headquarter = Branch::where('is_headquarter', true)->first();

        $administrator = User::firstOrCreate(
            ['email' => 'vickypplg12@gmail.com'],
            [
                'id' => Str::uuid(),
                'branch_id' => $headquarter?->id,
                'name' => 'Administrator',
                'password' => Hash::make('R4hasia4dmin1strator'),
                'is_active' => true,
            ]
        );
        $administrator->assignRole([Role::where('name', 'administrator')->first()->id]);
    }
}