<?php

namespace Database\Factories;

use App\Models\Camera;
use App\Models\Violation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Violation>
 */
class ViolationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Violation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'camera_id' => Camera::factory(),
            'image_path' => 'violations/'.fake()->uuid().'.jpg',
            'status' => fake()->randomElement(['pending', 'reviewed', 'resolved']),
            'notes' => fake()->optional()->text(),
        ];
    }
}
