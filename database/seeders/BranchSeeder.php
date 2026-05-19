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
            'name' => 'Administrator',
            'code' => 'ADMINISTRATOR',
            'is_headquarter' => true,
            'is_active' => '1'
        ]);

        $branches = [
            ['name' => 'Filadelfia Muara Sari', 'code' => 'FMS', 'active' => '1'],
            ['name' => 'Filadelfia Kopo', 'code' => 'FKP', 'active' => '1'],
            ['name' => 'Filadelfia Pusat Jabar', 'code' => 'SPJ', 'active' => '1'],
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