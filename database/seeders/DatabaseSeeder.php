<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Orden: roles y deportes → países mínimos si faltan → 10 usuarios Faker
     * (SampleUsersSeeder) → demo/prueba (DemoUsersSeeder) → chat, conexiones,
     * historias, notificaciones, ajustes, noticias, resultados, saved jobs,
     * redes sociales, email_verifications y avatares (DemoContentSeeder) →
     * perfil demo completo tipo capturas de app (DemoShowcaseProfileSeeder).
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            SportSeeder::class,
        ]);

        if (Country::count() === 0) {
            Country::insert([
                ['id' => (string) Str::ulid(), 'name' => 'Argentina', 'code' => 'AR'],
                ['id' => (string) Str::ulid(), 'name' => 'Estados Unidos', 'code' => 'US'],
                ['id' => (string) Str::ulid(), 'name' => 'España', 'code' => 'ES'],
                ['id' => (string) Str::ulid(), 'name' => 'México', 'code' => 'MX'],
                ['id' => (string) Str::ulid(), 'name' => 'Colombia', 'code' => 'CO'],
            ]);
        }

        $this->call(SampleUsersSeeder::class);
        $this->call(DemoUsersSeeder::class);
        $this->call(DemoContentSeeder::class);
        $this->call(DemoShowcaseProfileSeeder::class);
    }
}
