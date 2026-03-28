<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\Like;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 10 usuarios con datos Faker; contraseña común: password (UserFactory).
 * Ejecutar antes de DemoUsersSeeder para tener población variada + cuentas fijas.
 */
class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)->create()->each(function (User $user) {
            Profile::factory()->create(['user_id' => $user->id]);

            Post::factory(3)->create(['user_id' => $user->id])->each(function (Post $post) {
                Comment::factory(2)->create([
                    'post_id' => $post->id,
                    'user_id' => User::query()->inRandomOrder()->value('id'),
                ]);
                Like::factory(3)->create([
                    'user_id' => User::query()->inRandomOrder()->value('id'),
                    'likeable_id' => $post->id,
                    'likeable_type' => Post::class,
                ]);
            });

            JobOffer::factory(1)->create(['user_id' => $user->id])->each(function (JobOffer $job) {
                User::query()
                    ->whereKeyNot($job->user_id)
                    ->inRandomOrder()
                    ->limit(3)
                    ->pluck('id')
                    ->each(function (string $applicantId) use ($job) {
                        JobApplication::factory()->create([
                            'job_offer_id' => $job->id,
                            'user_id' => $applicantId,
                        ]);
                    });
            });
        });
    }
}
