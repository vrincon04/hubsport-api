<?php

namespace Database\Factories;

use App\Models\JobOffer;
use App\Models\User;
use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobOfferFactory extends Factory
{
    protected $model = JobOffer::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->jobTitle(),
            'sport_id' => Sport::factory(),
            'company' => $this->faker->company(),
            'location' => $this->faker->city() . ', ' . $this->faker->country(),
            'contract_type' => $this->faker->randomElement(['Jornada completa', 'Media jornada', 'Temporal', 'Freelance']),
            'application_type' => $this->faker->randomElement(['simple', 'web']),
            'application_url' => $this->faker->url(),
            'description' => $this->faker->paragraphs(3, true),
            'deadline' => $this->faker->dateTimeBetween('now', '+2 months'),
        ];
    }
}
