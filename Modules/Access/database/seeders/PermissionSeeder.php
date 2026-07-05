<?php

namespace Modules\Access\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Access\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        $actions = config('access.actions', []);
        $resources = config('access.resources', []);

        foreach($resources as $resource) { 
            foreach($actions as $action) { 
                Permission::updateOrCreate(
                    [
                        'slug' => "{$resource}.{$action}"
                    ],
                    [
                        'name' => ucfirst($action) . ' ' . str_replace('_', ' ', $resource),
                        'description' => "Allowed users to {$action} {$resource}."
                    ]
                );
            }
        }
    }
}
