<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $administratorRole = Role::firstOrCreate(
            ['name' => 'administrator'],
            [
                'branch_id' => null,
                'display_name' => 'Administrator',
                'description' => 'Akses penuh ke semua fitur sistem dan manajemen cabang',
                'is_active' => true,
            ],
        );
        $administratorRole->givePermission(Permission::pluck('id')->toArray());
    }
}
