<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $centralWarehouse = Branch::where('is_central_warehouse', true)->first();
        $bandung = Branch::where('code', 'BDG-01')->first();

        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@inventyou.test',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'branch_id' => null,
        ]);

        User::create([
            'name' => 'Warehouse Admin Pusat',
            'email' => 'warehouseadmin@inventyou.test',
            'password' => bcrypt('password'),
            'role' => 'warehouse_admin',
            'branch_id' => $centralWarehouse->id,
        ]);

        User::create([
            'name' => 'Staff Bandung',
            'email' => 'staff@inventyou.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'branch_id' => $bandung->id,
        ]);
    }
}