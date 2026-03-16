<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $headquarter = Branch::create([
            'id' => Str::uuid(),
            'name' => 'Kantor Pusat',
            'code' => 'PUSAT',
            'is_headquarter' => true,
            'is_active' => '1'
        ]);

        $branches = [
            ['name' => 'Cabang Jakarta', 'code' => 'JKT', 'active' => '0'],
            ['name' => 'Cabang Bandung', 'code' => 'BDG', 'active' => '0'],
            ['name' => 'Cabang Surabaya', 'code' => 'SBY', 'active' => '0'],
            ['name' => 'Cabang Semarang', 'code' => 'SMG', 'active' => '0'],
        ];

        foreach ($branches as $branch) {
            Branch::create([
                'id' => Str::uuid(),
                'name' => $branch['name'],
                'code' => $branch['code'],
                'is_headquarter' => false,
                'is_active' => $branch['active']
            ]);
        }
    }
}