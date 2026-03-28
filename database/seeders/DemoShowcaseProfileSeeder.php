<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Perfil completo tipo “Miguel Ovalles” para demo@hubsport.test (capturas de app).
 */
class DemoShowcaseProfileSeeder extends Seeder
{
    private function lorem(): string
    {
        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit urna. Pellentesque sit amet sapien fringilla.';
    }

    public function run(): void
    {
        $user = User::query()->where('email', 'demo@hubsport.test')->first();
        if (! $user) {
            return;
        }

        $user->name = 'Miguel Ovalles';
        $user->slug = Str::slug('miguel-ovalles').'-'.substr($user->id, -6);
        $user->saveQuietly();

        $f1 = Sport::query()->firstOrCreate(
            ['name' => 'Fórmula 1'],
            ['description' => 'Automovilismo de velocidad']
        );

        $us = Country::query()->where('code', 'US')->first()
            ?? Country::query()->first();

        if (! $us) {
            $this->command?->warn('DemoShowcaseProfileSeeder: sin país.');

            return;
        }

        $stats = [
            ['value' => '2024', 'label' => 'Temporada'],
            ['value' => '18', 'label' => 'Lugar'],
            ['value' => '6', 'label' => 'Pts'],
            ['value' => '22', 'label' => 'Partidas'],
            ['value' => '2', 'label' => 'Victorias'],
            ['value' => '1', 'label' => 'Poles'],
        ];

        $experience = [
            [
                'role' => 'Piloto Formula 1',
                'organization' => 'Sauber',
                'period_label' => 'Abril 2022 - Actualmente',
                'duration_label' => '2 años',
                'start' => '2022-04-01',
                'end' => null,
                'is_current' => true,
                'description' => $this->lorem(),
                'coach' => 'Max Verstappen',
            ],
            [
                'role' => 'Piloto Formula 1',
                'organization' => 'Mercedes',
                'period_label' => 'Enero 2022 - Abril 2022',
                'duration_label' => '3 meses',
                'start' => '2022-01-01',
                'end' => '2022-04-01',
                'is_current' => false,
                'description' => $this->lorem(),
                'coach' => 'Fernando Alonso',
            ],
        ];

        $achievements = [
            [
                'title' => 'Gran Premio de Malasia',
                'date_label' => 'Enero 2023',
                'image_urls' => [
                    'https://picsum.photos/seed/hubach1/300/300',
                    'https://picsum.photos/seed/hubach2/300/300',
                    'https://picsum.photos/seed/hubach3/300/300',
                ],
            ],
            [
                'title' => 'Novato del año • Autosport Awards',
                'date_label' => 'Abril 2022',
                'image_urls' => [
                    'https://picsum.photos/seed/hubach4/300/300',
                ],
            ],
            [
                'title' => 'Excelencia • Ferrari Driver Academy',
                'date_label' => 'Abril 2020',
                'image_urls' => [
                    'https://picsum.photos/seed/hubach5/300/300',
                ],
            ],
        ];

        $education = [
            [
                'institution' => 'Ferrari Driver Academy',
                'degree' => 'Technologist driver',
                'period_label' => 'Abril 2020 - Abril 2021',
                'duration_label' => '1 año',
                'certificate_url' => 'https://picsum.photos/seed/hubcert/400/280',
            ],
        ];

        Profile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'country_id' => $us->id,
                'sport_id' => $f1->id,
                'first_name' => 'Miguel',
                'last_name' => 'Ovalles',
                'phone_number' => '+1555'.substr(md5($user->id), 0, 7),
                'bio' => $this->lorem(),
                'birth_date' => '1990-05-30',
                'position' => 'Piloto de F1',
                'stats' => $stats,
                'experience' => $experience,
                'achievements' => $achievements,
                'education' => $education,
            ]
        );

        $user->getMedia('avatar')->each->delete();
        $this->attachAvatar($user);

        Post::withTrashed()->where('user_id', $user->id)->get()->each(function (Post $post) {
            $post->clearMediaCollection('gallery');
            $post->forceDelete();
        });

        $posts = [
            [
                'title' => 'Entrenamiento en pista',
                'body' => 'Gran sesión con el equipo hoy. Próximo GP muy cerca #F1',
                'images' => [
                    'https://picsum.photos/seed/hubpost1/800/600',
                    'https://picsum.photos/seed/hubpost2/800/600',
                ],
            ],
            [
                'title' => 'Podio en casa',
                'body' => 'Gracias a todos los fans por el apoyo. Podéis ver más detalles en la bio.',
                'images' => ['https://picsum.photos/seed/hubpost3/800/600'],
            ],
        ];

        foreach ($posts as $i => $row) {
            $post = Post::create([
                'user_id' => $user->id,
                'title' => $row['title'],
                'body' => $row['body'],
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(4 - $i),
            ]);
            foreach ($row['images'] as $url) {
                $this->tryAddMediaUrl($post, $url, 'gallery');
            }
        }
    }

    private function attachAvatar(User $user): void
    {
        $url = 'https://picsum.photos/seed/miguelovalles/512/512';
        if (! $this->tryAddMediaUrl($user, $url, 'avatar')) {
            $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmWQQAAAABJRU5ErkJggg==', true);
            $user->addMediaFromString($png)->usingFileName('avatar.png')->toMediaCollection('avatar');
        }
    }

    private function tryAddMediaUrl($model, string $url, string $collection): bool
    {
        try {
            $model->addMediaFromUrl($url)->toMediaCollection($collection);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
