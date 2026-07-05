<?php

namespace Modules\Access\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Access\Models\Permission::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

