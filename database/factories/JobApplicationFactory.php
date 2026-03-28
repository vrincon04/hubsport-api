<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    public function definition()
    {
        return [
            'job_offer_id' => JobOffer::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'reviewed', 'rejected', 'accepted']),
        ];
    }
}
