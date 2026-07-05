<?php

namespace Modules\Access\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Access\Models\Role;
use Modules\Access\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::where('name', 'admin')->first();

        User::firstOrCreate(
            ['email' => 'root@test.com'],
            [
                'name' => 'Root User',
                'password' => 'password',
                'role_id' => $admin?->id,
            ]
        );
    }
}
