<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::create([
            'name' => 'Gudang Pusat',
            'code' => 'PST-01',
            'is_central_warehouse' => true,
            'address' => 'Jl. Industri Raya No. 1, Jakarta',
        ]);

        Branch::create([
            'name' => 'Cabang Bandung',
            'code' => 'BDG-01',
            'is_central_warehouse' => false,
            'address' => 'Jl. Asia Afrika No. 10, Bandung',
        ]);

        Branch::create([
            'name' => 'Cabang Surabaya',
            'code' => 'SBY-01',
            'is_central_warehouse' => false,
            'address' => 'Jl. Tunjungan No. 5, Surabaya',
        ]);
    }
}