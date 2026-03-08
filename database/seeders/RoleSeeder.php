<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Role (All Permissions)
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Akses penuh ke semua fitur sistem',
                'is_active' => true,
            ]
        );
        $adminRole->givePermission(Permission::pluck('id')->toArray());
    }
}