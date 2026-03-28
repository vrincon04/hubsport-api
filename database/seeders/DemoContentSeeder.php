<?php

namespace Database\Seeders;

use App\Models\Connection;
use App\Models\Conversation;
use App\Models\EmailVerification;
use App\Models\JobOffer;
use App\Models\MatchResult;
use App\Models\Message;
use App\Models\News;
use App\Models\SavedJob;
use App\Models\SocialAccount;
use App\Models\Sport;
use App\Models\Story;
use App\Models\User;
use App\Models\UserSettings;
use App\Notifications\DatabaseDemoNotification;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * Puebla chat, conexiones, historias, notificaciones Laravel, ajustes de usuario,
 * noticias, resultados, trabajos guardados, cuentas sociales de demo, verificación
 * por email y avatares de muestra (media).
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $sportIds = Sport::query()->pluck('id');
        if ($sportIds->isEmpty()) {
            $this->command?->warn('DemoContentSeeder: sin deportes, se omite.');

            return;
        }

        $users = User::query()->orderBy('id')->get();
        if ($users->count() < 2) {
            $this->command?->warn('DemoContentSeeder: hacen falta al menos 2 usuarios.');

            return;
        }

        $sportA = $sportIds->get(0);
        $sportB = $sportIds->get(1) ?? $sportA;

        $this->seedUserSettings($users);
        $this->seedConnections($users);
        $this->seedConversationsAndMessages($users);
        $this->seedStories($users);
        $this->seedNews($sportA, $sportB);
        $this->seedMatchResults($sportA, $sportB);
        $this->seedSavedJobs($users);
        $this->seedSocialAccounts($users);
        $this->seedEmailVerifications();
        $this->seedNotifications($users);
        $this->seedAvatars($users);
    }

    private function seedUserSettings($users): void
    {
        foreach ($users as $user) {
            UserSettings::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'notifications' => [
                        'pushEnabled' => true,
                        'emailEnabled' => false,
                        'messagesEnabled' => true,
                        'eventsEnabled' => true,
                        'tipsEnabled' => true,
                    ],
                    'privacy' => [
                        'publicProfile' => true,
                        'showStats' => true,
                        'allowMessagesFromAnyone' => false,
                    ],
                    'security' => [
                        'is2FAEnabled' => false,
                    ],
                ]
            );
        }
    }

    private function seedConnections($users): void
    {
        $u = $users->values();
        $pairs = [
            [0, 1, 'accepted'],
            [0, 2, 'accepted'],
            [1, 3, 'pending'],
            [2, 4, 'pending'],
            [3, 5, 'accepted'],
            [0, 6, 'rejected'],
        ];

        foreach ($pairs as [$i, $j, $status]) {
            if (! isset($u[$i], $u[$j])) {
                continue;
            }
            $a = $u[$i]->id;
            $b = $u[$j]->id;
            Connection::firstOrCreate(
                ['user_id' => $a, 'connected_user_id' => $b],
                ['status' => $status]
            );
        }
    }

    private function seedConversationsAndMessages($users): void
    {
        $u = $users->values();
        if (! isset($u[0], $u[1])) {
            return;
        }

        $makeConv = function (User $one, User $two, array $lines) {
            $conv = new Conversation;
            $conv->save();
            $conv->users()->sync([$one->id, $two->id]);

            $sender = $one;
            foreach ($lines as $text) {
                Message::create([
                    'conversation_id' => $conv->id,
                    'sender_id' => $sender->id,
                    'text' => $text,
                    'is_read' => fake()->boolean(70),
                ]);
                $sender = $sender->id === $one->id ? $two : $one;
            }
        };

        $makeConv($u[0], $u[1], [
            'Hola, ¿sigues entrenando esta semana?',
            'Sí, el martes y jueves por la tarde.',
            'Perfecto, nos vemos en el centro.',
        ]);

        if (isset($u[2])) {
            $makeConv($u[0], $u[2], [
                'Te envío el enlace del torneo.',
                'Recibido, gracias.',
            ]);
        }
    }

    private function seedStories($users): void
    {
        $samples = [
            ['type' => 'image', 'url' => 'https://picsum.photos/seed/hubsport1/720/1280'],
            ['type' => 'image', 'url' => 'https://picsum.photos/seed/hubsport2/720/1280'],
            ['type' => 'video', 'url' => 'https://example.com/demo/story-placeholder.mp4'],
        ];

        foreach ($users->take(5) as $idx => $user) {
            $s = $samples[$idx % count($samples)];
            Story::create([
                'user_id' => $user->id,
                'media_url' => $s['url'],
                'type' => $s['type'],
                'expires_at' => now()->addDay(),
            ]);
        }
    }

    private function seedNews(string $sportA, ?string $sportB): void
    {
        $rows = [
            ['title' => 'Copa regional: calendario publicado', 'sport' => $sportA],
            ['title' => 'Nuevas becas para deportistas jóvenes', 'sport' => $sportB ?? $sportA],
            ['title' => 'Entrevista con la campeona nacional', 'sport' => $sportA],
        ];

        foreach ($rows as $i => $row) {
            News::create([
                'title' => $row['title'],
                'content' => 'Contenido de demostración para recorridos en la app. '.fake()->paragraph(),
                'image_url' => 'https://picsum.photos/seed/newshub'.($i + 1).'/800/450',
                'sport_id' => $row['sport'],
            ]);
        }
    }

    private function seedMatchResults(string $sportA, ?string $sportB): void
    {
        $base = now()->subDays(7);
        MatchResult::create([
            'team_a' => 'Club Norte',
            'team_b' => 'Club Sur',
            'score_a' => 2,
            'score_b' => 1,
            'match_date' => $base->copy()->addDay(),
            'status' => 'completed',
            'sport_id' => $sportA,
        ]);
        MatchResult::create([
            'team_a' => 'Unión Este',
            'team_b' => 'Atlético Oeste',
            'score_a' => 0,
            'score_b' => 0,
            'match_date' => $base->copy()->addDays(2),
            'status' => 'completed',
            'sport_id' => $sportB ?? $sportA,
        ]);
        MatchResult::create([
            'team_a' => 'Selección A',
            'team_b' => 'Selección B',
            'score_a' => 0,
            'score_b' => 0,
            'match_date' => $base->copy()->addDays(10),
            'status' => 'upcoming',
            'sport_id' => $sportA,
        ]);
    }

    private function seedSavedJobs($users): void
    {
        $demo = User::query()->where('email', 'demo@hubsport.test')->first();
        if (! $demo) {
            $demo = $users->first();
        }

        JobOffer::query()
            ->where('user_id', '!=', $demo->id)
            ->limit(3)
            ->get()
            ->each(function (JobOffer $job) use ($demo) {
                SavedJob::firstOrCreate(
                    [
                        'job_offer_id' => $job->id,
                        'user_id' => $demo->id,
                    ]
                );
            });

        $second = User::query()->whereKeyNot($demo->id)->first();
        if ($second) {
            JobOffer::query()
                ->where('user_id', '!=', $second->id)
                ->where('user_id', '!=', $demo->id)
                ->limit(1)
                ->get()
                ->each(function (JobOffer $job) use ($second) {
                    SavedJob::firstOrCreate([
                        'job_offer_id' => $job->id,
                        'user_id' => $second->id,
                    ]);
                });
        }
    }

    private function seedSocialAccounts($users): void
    {
        $demo = User::query()->where('email', 'demo@hubsport.test')->first();
        $prueba = User::query()->where('email', 'prueba@hubsport.test')->first();

        if ($demo) {
            SocialAccount::firstOrCreate(
                ['user_id' => $demo->id, 'provider' => 'google'],
                [
                    'provider_id' => 'demo-google-'.Str::lower(Str::random(12)),
                    'provider_token' => 'seed-token-demo',
                    'provider_refresh_token' => null,
                ]
            );
        }

        if ($prueba) {
            SocialAccount::firstOrCreate(
                ['user_id' => $prueba->id, 'provider' => 'facebook'],
                [
                    'provider_id' => 'demo-fb-'.Str::lower(Str::random(12)),
                    'provider_token' => 'seed-token-prueba',
                    'provider_refresh_token' => null,
                ]
            );
        }
    }

    private function seedEmailVerifications(): void
    {
        EmailVerification::updateOrCreate(
            ['email' => 'demo@hubsport.test'],
            ['code' => '12345678']
        );
        EmailVerification::updateOrCreate(
            ['email' => 'prueba@hubsport.test'],
            ['code' => '87654321']
        );
    }

    private function seedNotifications($users): void
    {
        $target = User::query()->where('email', 'demo@hubsport.test')->first() ?? $users->first();

        $payloads = [
            ['title' => 'Nuevo seguidor', 'body' => 'Alguien ha aceptado tu solicitud de conexión.', 'read' => false],
            ['title' => 'Mensaje', 'body' => 'Tienes un mensaje sin leer en el chat.', 'read' => true],
            ['title' => 'Empleo', 'body' => 'Hay una oferta que coincide con tu perfil.', 'read' => true],
        ];

        foreach ($payloads as $p) {
            DatabaseNotification::create([
                'id' => (string) Str::uuid(),
                'type' => DatabaseDemoNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $target->id,
                'data' => [
                    'title' => $p['title'],
                    'body' => $p['body'],
                ],
                'read_at' => $p['read'] ? now()->subHour() : null,
            ]);
        }

        $other = User::query()->whereKeyNot($target->id)->first();
        if ($other) {
            DatabaseNotification::create([
                'id' => (string) Str::uuid(),
                'type' => DatabaseDemoNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $other->id,
                'data' => [
                    'title' => 'Bienvenida',
                    'body' => 'Gracias por unirte a Hubsport (datos de prueba).',
                ],
                'read_at' => null,
            ]);
        }
    }

    private function seedAvatars($users): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmWQQAAAABJRU5ErkJggg==', true);

        foreach ($users->take(6) as $user) {
            if ($user->getMedia('avatar')->isNotEmpty()) {
                continue;
            }
            $user->addMediaFromString($png)
                ->usingFileName('seed-avatar-'.$user->id.'.png')
                ->toMediaCollection('avatar');
        }
    }
}
