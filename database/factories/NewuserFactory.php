<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Newuser>
 */
class NewuserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' =>'abc',
            'email'=>fake()->unique()->email(),
            'description'=>fake()->paragraph(),
            'newcity'=>fake()->city(),
             'created_at' => now(),
             'updated_at' => now()
        ];
    }
}
