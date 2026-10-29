<?php

namespace Database\Factories;

use App\Models\ContactEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactEnquiry>
 */
class ContactEnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company' => fake()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'services' => ['Solar PPA'],
            'budget' => '฿1–5 million',
            'project_location' => fake()->city(),
            'message' => fake()->sentence(),
            'source' => 'contact',
            'status' => 'new',
        ];
    }
}
