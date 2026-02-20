<?php

namespace Database\Factories;

use App\Models\Violation;
use App\Models\ViolationDetail;
use App\Models\ViolationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ViolationDetail>
 */
class ViolationDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ViolationDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'violation_id' => Violation::factory(),
            'violation_type_id' => ViolationType::factory(),
            'confidence_score' => fake()->optional()->randomFloat(2, 0, 1),
            'additional_info' => fake()->optional()->text(),
            'status' => fake()->randomElement(['unverified', 'confirmed', 'dismissed']),
        ];
    }
}
