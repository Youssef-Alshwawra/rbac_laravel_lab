<?php

namespace Modules\Agents\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Agents\Models\Agent::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

