<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Cache;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker->seed(Cache::rememberForever('faker-seed', fn () => rand()));

        return [
            'name' => $this->faker->unique()->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'title' => $this->faker->unique()->jobTitle(),
            'role' => $this->faker->randomElement(['Admin', 'Manager', 'Editor', 'Viewer']),
        ];
    }
}
