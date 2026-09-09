<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\Like;
use App\Models\Message;
use App\Models\Post;
use App\Models\Profile;
use App\Models\ReferenceRequest;
use App\Models\SavedEvent;
use App\Models\SavedJob;
use App\Models\SocialAccount;
use App\Models\Sponsorship;
use App\Models\Sport;
use App\Models\SportsReference;
use App\Models\Story;
use App\Models\User;
use App\Models\UserSettings;
use App\Notifications\DatabaseDemoNotification;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoFullShowcaseSeeder extends Seeder
{
    private const PEERS = [
        ['email' => 'demo.peer.coach@hubsport.test', 'name' => 'Laura Méndez', 'first_name' => 'Laura', 'last_name' => 'Méndez', 'type' => 'coach'],
        ['email' => 'demo.peer.athlete@hubsport.test', 'name' => 'Andrés Castillo', 'first_name' => 'Andrés', 'last_name' => 'Castillo', 'type' => 'athlete'],
        ['email' => 'demo.peer.club@hubsport.test', 'name' => 'Club Horizonte', 'first_name' => 'Club', 'last_name' => 'Horizonte', 'type' => 'club'],
        ['email' => 'demo.peer.scout@hubsport.test', 'name' => 'Camila Ortiz', 'first_name' => 'Camila', 'last_name' => 'Ortiz', 'type' => 'coach'],
        ['email' => 'demo.peer.agency@hubsport.test', 'name' => 'Agencia Impulso', 'first_name' => 'Agencia', 'last_name' => 'Impulso', 'type' => 'club'],
    ];

    public function run(): void
    {
        $country = Country::query()->firstOrCreate(
            ['code' => 'DO'],
            ['name' => 'República Dominicana']
        );
        $sport = Sport::query()->firstOrCreate(
            ['name' => 'Atletismo'],
            ['description' => 'Carreras, saltos y pruebas combinadas']
        );

        $demo = $this->upsertUser(
            (string) config('demo.user_email', 'demo@hubsport.test'),
            'Sofía Ramírez'
        );
        $peers = collect(self::PEERS)->map(
            fn (array $peer) => $this->upsertUser($peer['email'], $peer['name'])
        );
        $users = collect([$demo])->merge($peers);

        $this->clearDemoContent($users);
        $this->seedProfiles($demo, $peers, $country, $sport);
        $posts = $this->seedPosts($demo, $peers);
        $this->seedConnections($demo, $peers);
        $this->seedConversations($demo, $peers);
        $this->seedJobs($demo, $peers, $sport);
        $this->seedReferences($demo, $peers);
        $this->seedEvents($demo, $peers, $sport);
        $this->seedSponsorships($demo, $peers, $sport);
        $this->seedSettingsSocialNotificationsAndStories($demo, $peers);
        $this->seedLikes($demo, $peers, $posts);
        $this->ensureAvatar($demo);
    }

    private function upsertUser(string $email, string $name): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => Hash::make('password'),
            'email_verified_at' => '2026-01-15 12:00:00',
            'is_demo' => true,
        ])->save();

        return $user;
    }

    private function clearDemoContent(Collection $users): void
    {
        $ids = $users->pluck('id');
        $conversationIds = DB::table('conversation_user')->whereIn('user_id', $ids)->pluck('conversation_id');
        DB::table('messages')->whereIn('conversation_id', $conversationIds)->delete();
        DB::table('conversation_user')->whereIn('conversation_id', $conversationIds)->delete();
        DB::table('conversations')->whereIn('id', $conversationIds)->delete();

        $postIds = DB::table('posts')->whereIn('user_id', $ids)->pluck('id');
        DB::table('comments')->where(function ($query) use ($ids, $postIds) {
            $query->whereIn('user_id', $ids)->orWhereIn('post_id', $postIds);
        })->delete();
        DB::table('likes')->where(function ($query) use ($ids, $postIds) {
            $query->whereIn('user_id', $ids)
                ->orWhere(function ($likeQuery) use ($postIds) {
                    $likeQuery->where('likeable_type', Post::class)->whereIn('likeable_id', $postIds);
                });
        })->delete();
        DB::table('posts')->whereIn('id', $postIds)->delete();

        DB::table('job_applications')->whereIn('user_id', $ids)->delete();
        DB::table('saved_jobs')->whereIn('user_id', $ids)->delete();
        DB::table('job_offers')->whereIn('user_id', $ids)->delete();

        DB::table('sports_references')->where(function ($query) use ($ids) {
            $query->whereIn('author_id', $ids)->orWhereIn('subject_user_id', $ids);
        })->delete();
        DB::table('reference_requests')->where(function ($query) use ($ids) {
            $query->whereIn('requester_id', $ids)->orWhereIn('recipient_id', $ids);
        })->delete();

        DB::table('event_participants')->whereIn('user_id', $ids)->delete();
        DB::table('saved_events')->whereIn('user_id', $ids)->delete();
        DB::table('events')->whereIn('user_id', $ids)->delete();

        DB::table('connections')->where(function ($query) use ($ids) {
            $query->whereIn('user_id', $ids)->orWhereIn('connected_user_id', $ids);
        })->delete();
        DB::table('sponsorships')->whereIn('user_id', $ids)->delete();
        DB::table('user_settings')->whereIn('user_id', $ids)->delete();
        DB::table('social_accounts')->whereIn('user_id', $ids)->delete();
        DB::table('notifications')->whereIn('notifiable_id', $ids)->delete();
        DB::table('stories')->whereIn('user_id', $ids)->delete();
        DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->whereIn('tokenable_id', $ids)
            ->delete();
    }

    private function seedProfiles(User $demo, Collection $peers, Country $country, Sport $sport): void
    {
        Profile::query()->updateOrCreate(['user_id' => $demo->id], [
            'country_id' => $country->id,
            'sport_id' => $sport->id,
            'profile_type' => 'athlete',
            'first_name' => 'Sofía',
            'last_name' => 'Ramírez',
            'phone_number' => '+18095550100',
            'phone_verified_at' => '2026-01-15 12:00:00',
            'verification_status' => 'verified',
            'verified_at' => '2026-01-15 12:00:00',
            'verified_badge' => true,
            'city' => 'Santo Domingo',
            'bio' => 'Velocista enfocada en rendimiento, comunidad y oportunidades deportivas.',
            'birth_date' => '1998-04-12',
            'position' => 'Velocista 400 m',
            'sport_level' => 'elite',
            'current_team' => 'Club Horizonte',
            'social_links' => ['instagram' => 'https://instagram.com/hubsport_demo'],
            'stats' => [
                ['value' => '52.40', 'label' => 'Mejor marca 400 m'],
                ['value' => '14', 'label' => 'Podios'],
                ['value' => '6', 'label' => 'Temporadas'],
            ],
            'experience' => [[
                'role' => 'Atleta de alto rendimiento',
                'organization' => 'Club Horizonte',
                'period_label' => '2022 - Actualidad',
                'is_current' => true,
                'description' => 'Preparación competitiva nacional e internacional.',
            ]],
            'achievements' => [[
                'title' => 'Campeona nacional de 400 m',
                'date_label' => 'Junio 2026',
                'image_urls' => [],
            ]],
            'education' => [[
                'institution' => 'Instituto Nacional de Deportes',
                'degree' => 'Gestión Deportiva',
                'period_label' => '2020 - 2024',
            ]],
        ]);

        foreach ($peers->values() as $index => $peer) {
            $definition = self::PEERS[$index];
            Profile::query()->updateOrCreate(['user_id' => $peer->id], [
                'country_id' => $country->id,
                'sport_id' => $sport->id,
                'profile_type' => $definition['type'],
                'first_name' => $definition['first_name'],
                'last_name' => $definition['last_name'],
                'phone_number' => '+1809555010'.($index + 1),
                'verification_status' => 'verified',
                'verified_at' => '2026-01-15 12:00:00',
                'verified_badge' => true,
                'city' => 'Santo Domingo',
                'bio' => 'Perfil coordinado para recorridos demostrativos de HubSport.',
                'birth_date' => '1992-06-15',
                'position' => $index === 0 ? 'Entrenadora' : 'Miembro de la comunidad deportiva',
            ]);
        }
    }

    private function seedPosts(User $demo, Collection $peers): Collection
    {
        $posts = collect([
            [$demo, 'Preparación para el campeonato', 'Semana de técnica, velocidad y recuperación completada.', 'published'],
            [$demo, 'Nueva mejor marca personal', 'El trabajo constante dio resultado: 52.40 segundos en 400 metros.', 'published'],
            [$peers[1], 'Entrenamiento abierto', 'El sábado compartiremos una sesión guiada para atletas juveniles.', 'published'],
            [$peers[2], 'Convocatoria del Club Horizonte', 'Abrimos cupos para el encuentro nacional de velocidad.', 'published'],
        ])->map(fn (array $row) => Post::query()->create([
            'user_id' => $row[0]->id,
            'title' => $row[1],
            'body' => $row[2],
            'status' => $row[3],
            'published_at' => '2026-08-20 14:00:00',
        ]));

        Comment::query()->create([
            'user_id' => $peers[0]->id,
            'post_id' => $posts[0]->id,
            'content' => 'Excelente disciplina durante toda la preparación.',
        ]);
        Comment::query()->create([
            'user_id' => $demo->id,
            'post_id' => $posts[2]->id,
            'content' => 'Allí estaré para apoyar a los participantes.',
        ]);

        return $posts;
    }

    private function seedConnections(User $demo, Collection $peers): void
    {
        foreach ($peers->take(3) as $peer) {
            Connection::query()->create([
                'user_id' => $demo->id,
                'connected_user_id' => $peer->id,
                'status' => 'accepted',
            ]);
        }

        Connection::query()->create([
            'user_id' => $peers[3]->id,
            'connected_user_id' => $demo->id,
            'status' => 'pending',
        ]);
        Connection::query()->create([
            'user_id' => $demo->id,
            'connected_user_id' => $peers[4]->id,
            'status' => 'pending',
        ]);
    }

    private function seedConversations(User $demo, Collection $peers): void
    {
        $threads = [
            [
                $peers[0],
                [
                    [$peers[0], 'La sesión de velocidad quedó confirmada para el jueves.'],
                    [$demo, 'Perfecto, llevaré el registro de marcas de esta semana.'],
                    [$peers[0], 'También revisaremos el plan de recuperación.'],
                ],
            ],
            [
                $peers[1],
                [
                    [$peers[1], '¿Vienes al entrenamiento abierto del sábado?'],
                    [$demo, 'Sí, llego a las 7:30 para calentar juntos.'],
                ],
            ],
            [
                $peers[2],
                [
                    [$peers[2], 'Tu inscripción al encuentro nacional ya está confirmada.'],
                    [$demo, 'Gracias. Enviaré el documento de acreditación hoy.'],
                ],
            ],
        ];

        foreach ($threads as [$peer, $messages]) {
            $conversation = Conversation::query()->create();
            $conversation->users()->attach([$demo->id, $peer->id]);

            foreach ($messages as [$sender, $text]) {
                Message::query()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender->id,
                    'text' => $text,
                    'is_read' => $sender->is($demo),
                ]);
            }
        }
    }

    private function seedJobs(User $demo, Collection $peers, Sport $sport): void
    {
        $coachJob = JobOffer::query()->create([
            'user_id' => $peers[2]->id,
            'sport_id' => $sport->id,
            'title' => 'Asistente de preparación física',
            'company' => 'Club Horizonte',
            'location' => 'Santo Domingo',
            'contract_type' => 'part_time',
            'application_type' => 'simple',
            'description' => 'Apoyo en sesiones juveniles, registro de marcas y coordinación de entrenamientos.',
            'deadline' => '2026-12-15',
        ]);
        $analystJob = JobOffer::query()->create([
            'user_id' => $demo->id,
            'sport_id' => $sport->id,
            'title' => 'Analista de rendimiento deportivo',
            'company' => 'HubSport Labs',
            'location' => 'Remoto',
            'contract_type' => 'full_time',
            'application_type' => 'web',
            'application_url' => 'https://example.test/demo/jobs/analyst',
            'description' => 'Análisis de carga, tendencias de rendimiento y reportes para equipos deportivos.',
            'deadline' => '2026-11-30',
        ]);

        JobApplication::query()->create([
            'job_offer_id' => $coachJob->id,
            'user_id' => $demo->id,
            'message' => 'Mi experiencia competitiva puede aportar una perspectiva práctica al equipo.',
            'status' => 'reviewed',
        ]);
        JobApplication::query()->create([
            'job_offer_id' => $analystJob->id,
            'user_id' => $peers[1]->id,
            'message' => 'Tengo experiencia registrando métricas de entrenamiento.',
            'status' => 'pending',
        ]);
        SavedJob::query()->create(['job_offer_id' => $coachJob->id, 'user_id' => $demo->id]);
    }

    private function seedReferences(User $demo, Collection $peers): void
    {
        $publishedRequest = ReferenceRequest::query()->create([
            'requester_id' => $demo->id,
            'recipient_id' => $peers[0]->id,
            'relationship_type' => 'coach',
            'message' => 'Referencia sobre preparación y disciplina competitiva.',
            'status' => 'completed',
            'requester_confirmed_at' => '2026-07-10 10:00:00',
            'recipient_confirmed_at' => '2026-07-10 11:00:00',
        ]);
        SportsReference::query()->create([
            'reference_request_id' => $publishedRequest->id,
            'author_id' => $peers[0]->id,
            'subject_user_id' => $demo->id,
            'relationship_type' => 'coach',
            'body' => 'Sofía mantiene una preparación consistente y una conducta ejemplar con el equipo.',
            'status' => 'published',
            'is_suspicious' => false,
        ]);

        $suspiciousRequest = ReferenceRequest::query()->create([
            'requester_id' => $demo->id,
            'recipient_id' => $peers[1]->id,
            'relationship_type' => 'teammate',
            'message' => 'Referencia pendiente de revisión automática.',
            'status' => 'completed',
        ]);
        SportsReference::query()->create([
            'reference_request_id' => $suspiciousRequest->id,
            'author_id' => $peers[1]->id,
            'subject_user_id' => $demo->id,
            'relationship_type' => 'teammate',
            'body' => 'Referencia demostrativa retenida para mostrar el flujo de moderación.',
            'status' => 'pending_confirmation',
            'is_suspicious' => true,
            'suspicious_reason' => 'Contenido de prueba marcado para revisión.',
        ]);

        ReferenceRequest::query()->create([
            'requester_id' => $peers[2]->id,
            'recipient_id' => $demo->id,
            'relationship_type' => 'club',
            'message' => 'Solicitud de referencia institucional para la ficha de atleta.',
            'status' => 'pending',
        ]);
    }

    private function seedEvents(User $demo, Collection $peers, Sport $sport): void
    {
        $event = Event::query()->create([
            'user_id' => $peers[2]->id,
            'sport_id' => $sport->id,
            'name' => 'Encuentro Nacional de Velocidad',
            'event_date' => '2026-12-05',
            'event_time' => '09:00:00',
            'location' => 'Estadio Olímpico',
            'city' => 'Santo Domingo',
            'country' => 'República Dominicana',
            'description' => 'Jornada de competencias, formación y conexión entre atletas.',
            'organizer_name' => 'Club Horizonte',
            'organizer_contact' => 'eventos@example.test',
            'participants_count' => 2,
        ]);
        EventParticipant::query()->create(['event_id' => $event->id, 'user_id' => $demo->id, 'status' => 'registered']);
        EventParticipant::query()->create(['event_id' => $event->id, 'user_id' => $peers[1]->id, 'status' => 'registered']);
        SavedEvent::query()->create(['event_id' => $event->id, 'user_id' => $demo->id]);

        Event::query()->create([
            'user_id' => $demo->id,
            'sport_id' => $sport->id,
            'name' => 'Clínica de arranque y relevos',
            'event_date' => '2026-10-18',
            'event_time' => '16:00:00',
            'location' => 'Pista Centro Olímpico',
            'city' => 'Santo Domingo',
            'country' => 'República Dominicana',
            'description' => 'Taller técnico para velocistas y entrenadores de la comunidad HubSport.',
            'organizer_name' => 'Sofía Ramírez',
            'organizer_contact' => 'sofia.demo@hubsport.test',
            'participants_count' => 1,
        ]);
    }

    private function seedSponsorships(User $demo, Collection $peers, Sport $sport): void
    {
        Sponsorship::query()->create([
            'user_id' => $demo->id,
            'sport_id' => $sport->id,
            'brand_name' => 'Impulso Performance',
            'sponsorship_type' => 'athlete_support',
            'requirements' => 'Participación en contenido deportivo y reporte trimestral de resultados.',
            'benefits' => 'Equipamiento, apoyo de viaje y sesiones de recuperación.',
            'contact' => 'alianzas@example.test',
        ]);
        Sponsorship::query()->create([
            'user_id' => $peers[2]->id,
            'sport_id' => $sport->id,
            'brand_name' => 'Nutrición Horizonte',
            'sponsorship_type' => 'event',
            'requirements' => 'Presencia de marca en actividades comunitarias.',
            'benefits' => 'Apoyo logístico para el encuentro nacional.',
            'contact' => 'patrocinios@example.test',
        ]);
        Sponsorship::query()->create([
            'user_id' => $peers[0]->id,
            'sport_id' => $sport->id,
            'brand_name' => 'Apex Performance',
            'sponsorship_type' => 'equipment',
            'requirements' => 'Calendario competitivo verificable y contenido mensual.',
            'benefits' => 'Kit técnico y sesiones de fuerza supervisadas.',
            'contact' => 'apex@example.test',
        ]);
    }

    private function seedSettingsSocialNotificationsAndStories(User $demo, Collection $peers): void
    {
        UserSettings::query()->create([
            'user_id' => $demo->id,
            'notifications' => ['pushEnabled' => true, 'emailEnabled' => true, 'messagesEnabled' => true, 'eventsEnabled' => true],
            'privacy' => ['publicProfile' => true, 'showStats' => true, 'allowMessagesFromAnyone' => false],
            'security' => ['is2FAEnabled' => false],
        ]);
        SocialAccount::query()->create([
            'user_id' => $demo->id,
            'provider' => 'google',
            'provider_id' => 'demo-google-account',
            'provider_token' => 'not-a-real-token',
        ]);

        foreach ([
            ['Nueva conexión', 'Laura Méndez aceptó tu conexión.', null],
            ['Oportunidad actualizada', 'Tu aplicación fue revisada.', '2026-08-21 15:00:00'],
            ['Evento guardado', 'El encuentro nacional está en tu calendario.', null],
        ] as $index => [$title, $body, $readAt]) {
            DatabaseNotification::query()->create([
                'id' => (string) Str::uuid(),
                'type' => DatabaseDemoNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $demo->id,
                'data' => ['title' => $title, 'body' => $body],
                'read_at' => $readAt,
            ]);
        }

        Story::query()->create([
            'user_id' => $demo->id,
            'media_url' => 'https://images.example.test/demo/track-session.jpg',
            'type' => 'image',
            'expires_at' => now()->addDay(),
        ]);
        Story::query()->create([
            'user_id' => $peers[1]->id,
            'media_url' => 'https://images.example.test/demo/community-training.jpg',
            'type' => 'image',
            'expires_at' => now()->addDay(),
        ]);
    }

    private function seedLikes(User $demo, Collection $peers, Collection $posts): void
    {
        Like::query()->create([
            'user_id' => $peers[0]->id,
            'likeable_id' => $posts[0]->id,
            'likeable_type' => Post::class,
        ]);
        Like::query()->create([
            'user_id' => $demo->id,
            'likeable_id' => $posts[2]->id,
            'likeable_type' => Post::class,
        ]);
    }

    private function ensureAvatar(User $demo): void
    {
        if ($demo->getMedia('avatar')->isNotEmpty()) {
            return;
        }

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmWQQAAAABJRU5ErkJggg==',
            true
        );
        $demo->addMediaFromString($png)
            ->usingFileName('demo-avatar.png')
            ->toMediaCollection('avatar');
    }
}
