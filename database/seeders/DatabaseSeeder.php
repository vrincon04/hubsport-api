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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // LLamar a seeders base
        $this->call([
            RolSeeder::class,
            SportSeeder::class,
        ]);

        // Asegurar que existan países antes de crear perfiles
        if (\App\Models\Country::count() == 0) {
            \App\Models\Country::insert([
                ['id' => (string) \Illuminate\Support\Str::ulid(), 'name' => 'Argentina', 'code' => 'AR'],
                ['id' => (string) \Illuminate\Support\Str::ulid(), 'name' => 'Estados Unidos', 'code' => 'US'],
                ['id' => (string) \Illuminate\Support\Str::ulid(), 'name' => 'España', 'code' => 'ES'],
                ['id' => (string) \Illuminate\Support\Str::ulid(), 'name' => 'México', 'code' => 'MX'],
                ['id' => (string) \Illuminate\Support\Str::ulid(), 'name' => 'Colombia', 'code' => 'CO'],
            ]);
        }

        // Usuarios de prueba (Faker + contraseña "password" en UserFactory)
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

        $this->call(DemoUsersSeeder::class);
    }
}
