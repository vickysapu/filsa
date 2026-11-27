<?php

namespace Database\Seeders;

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
        $administratorRoleId = Role::where('name', 'administrator')->first()?->id;

        $administrator1 = User::firstOrCreate(
            ['email' => 'vickypplg12@gmail.com'],
            [
                'id' => Str::uuid(),
                'branch_id' => $headquarter?->id,
                'name' => 'Vicky Saputra',
                'password' => Hash::make('R4hasia4dmin1strator'),
                'is_active' => true,
            ],
        );
        $administrator1->assignRole([$administratorRoleId]);

        $administrator2 = User::firstOrCreate(
            ['email' => 'admin.pusat@gereja.local'],
            [
                'id' => Str::uuid(),
                'branch_id' => $headquarter?->id,
                'name' => 'Admin Gereja Pusat',
                'password' => Hash::make('R4hasia4dmin1strator'),
                'is_active' => true,
            ],
        );
        $administrator2->assignRole([$administratorRoleId]);

        $administrator3 = User::firstOrCreate(
            ['email' => 'spv.admin@gereja.local'],
            [
                'id' => Str::uuid(),
                'branch_id' => $headquarter?->id,
                'name' => 'Supervisor Admin',
                'password' => Hash::make('R4hasia4dmin1strator'),
                'is_active' => true,
            ],
        );
        $administrator3->assignRole([$administratorRoleId]);

        $administrator4 = User::firstOrCreate(
            ['email' => 'operator@gereja.local'],
            [
                'id' => Str::uuid(),
                'branch_id' => $headquarter?->id,
                'name' => 'Operator Sistem',
                'password' => Hash::make('R4hasia4dmin1strator'),
                'is_active' => true,
            ],
        );
        $administrator4->assignRole([$administratorRoleId]);

        $administrator5 = User::firstOrCreate(
            ['email' => 'backup.admin@gereja.local'],
            [
                'id' => Str::uuid(),
                'branch_id' => $headquarter?->id,
                'name' => 'Admin Backup',
                'password' => Hash::make('R4hasia4dmin1strator'),
                'is_active' => true,
            ],
        );
        $administrator5->assignRole([$administratorRoleId]);
    }
}