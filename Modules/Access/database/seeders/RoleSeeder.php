<?php

namespace Modules\Access\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Access\Models\Permission;
use Modules\Access\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        $admin = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Full access']);
        $admin->permissions()->sync(Permission::pluck('id'));
    }
}
