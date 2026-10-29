<?php

namespace Database\Factories;

use App\Models\Solution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Solution>
 */
class SolutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'title_en' => fake()->sentence(3),
            'title_th' => 'โซลูชันพลังงานสะอาด',
            'title_zh' => '清洁能源解决方案',
            'summary_en' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_published' => true,
        ];
    }
}
