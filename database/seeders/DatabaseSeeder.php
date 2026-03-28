<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\JobOffer;
use App\Models\JobApplication;
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

        // Crear 15 Usuarios
        User::factory(15)->create()->each(function (User $user) {
            // Crear 1 Perfil por usuario
            Profile::factory()->create(['user_id' => $user->id]);

            // Crear 3 Publicaciones (Posts) por usuario
            Post::factory(3)->create(['user_id' => $user->id])->each(function (Post $post) {
                // Crear 2 Comentarios por Post
                Comment::factory(2)->create(['post_id' => $post->id]);
                // Crear 3 Likes por Post (Relación polimórfica)
                Like::factory(3)->create([
                    'likeable_id' => $post->id,
                    'likeable_type' => Post::class,
                ]);
            });

            // Crear 1 Oferta de Empleo por usuario
            JobOffer::factory(1)->create(['user_id' => $user->id])->each(function (JobOffer $job) {
                // Crear 3 Aplicaciones a la oferta por usuarios aleatorios (Faker los maneja)
                JobApplication::factory(3)->create(['job_offer_id' => $job->id]);
            });
        });
    }
}
