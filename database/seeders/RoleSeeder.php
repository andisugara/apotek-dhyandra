<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'Superadmin',
            'description' => 'Full access to all system features',
        ]);

        Role::create([
            'name' => 'Apoteker',
            'description' => 'Access to manage medications and prescriptions',
        ]);
    }
}
