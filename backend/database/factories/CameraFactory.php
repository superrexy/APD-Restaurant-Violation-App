<?php

namespace Database\Factories;

use App\Models\Camera;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Camera>
 */
class CameraFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Camera::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->optional()->text(),
            'location' => fake()->streetAddress(),
            'code' => fake()->unique()->regexify('[A-Z0-9]{8}'),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
            'connected_at' => fake()->optional()->dateTime(),
            'disconnected_at' => fake()->optional()->dateTime(),
            'last_maintenance_at' => fake()->optional()->dateTime(),
            'yolo_detection_status' => false,
        ];
    }
}
