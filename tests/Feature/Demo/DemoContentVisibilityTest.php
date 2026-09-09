<?php

namespace Tests\Feature\Demo;

use App\Models\Event;
use App\Models\JobOffer;
use App\Models\Post;
use App\Models\Sponsorship;
use App\Models\Sport;
use App\Models\User;
use Database\Seeders\DemoFullShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoContentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);
    }

    public function test_real_user_feed_hides_demo_posts_and_keeps_real_ones(): void
    {
        $realUser = User::factory()->create();
        $realPost = Post::factory()->create(['user_id' => $realUser->id]);

        $ids = collect(
            $this->withToken($this->tokenFor($realUser))
                ->getJson('/api/v1/posts')
                ->assertOk()
                ->json('data')
        )->pluck('id');

        $this->assertContains($realPost->id, $ids);
        $this->assertEmpty($ids->intersect($this->demoIdsFrom('posts')));
    }

    public function test_demo_session_still_sees_its_own_feed(): void
    {
        $demoUser = $this->demoUser();

        $ids = collect(
            $this->withToken($this->tokenFor($demoUser, ['demo']))
                ->getJson('/api/v1/posts')
                ->assertOk()
                ->json('data')
        )->pluck('id');

        $this->assertNotEmpty($ids->intersect($this->demoIdsFrom('posts')));
    }

    public function test_real_user_listings_hide_demo_events_sponsorships_and_jobs(): void
    {
        $realUser = User::factory()->create();
        $sport = Sport::query()->firstOrFail();

        $realEvent = Event::create([
            'user_id' => $realUser->id,
            'sport_id' => $sport->id,
            'name' => 'Torneo abierto de la liga',
            'event_date' => '2026-11-20',
            'location' => 'Estadio Central',
            'city' => 'Santo Domingo',
            'country' => 'República Dominicana',
            'description' => 'Competencia abierta para clubes federados.',
            'organizer_name' => $realUser->name,
            'organizer_contact' => $realUser->email,
        ]);
        $realSponsorship = Sponsorship::create([
            'user_id' => $realUser->id,
            'sport_id' => $sport->id,
            'brand_name' => 'Marca Real',
            'sponsorship_type' => 'Equipamiento',
            'requirements' => 'Competir en liga nacional.',
            'benefits' => 'Uniformes y viáticos.',
        ]);
        $realJob = JobOffer::factory()->create([
            'user_id' => $realUser->id,
            'sport_id' => $sport->id,
        ]);

        $token = $this->tokenFor($realUser);

        $eventIds = $this->listIds($token, '/api/v1/events');
        $this->assertContains($realEvent->id, $eventIds);
        $this->assertEmpty($eventIds->intersect($this->demoIdsFrom('events')));

        $sponsorshipIds = $this->listIds($token, '/api/v1/sponsorships');
        $this->assertContains($realSponsorship->id, $sponsorshipIds);
        $this->assertEmpty($sponsorshipIds->intersect($this->demoIdsFrom('sponsorships')));

        $jobIds = $this->listIds($token, '/api/v1/jobs');
        $this->assertContains($realJob->id, $jobIds);
        $this->assertEmpty($jobIds->intersect($this->demoIdsFrom('job_offers')));
    }

    public function test_real_user_search_hides_demo_accounts_and_their_content(): void
    {
        $realUser = User::factory()->create();

        $data = $this->withToken($this->tokenFor($realUser))
            ->getJson('/api/v1/search?q='.urlencode('Ramírez'))
            ->assertOk()
            ->json('data');

        $demoUserIds = $this->demoUserIds();

        $this->assertEmpty(collect($data['users'])->pluck('id')->intersect($demoUserIds));
        $this->assertEmpty(collect($data['posts'])->pluck('id')->intersect($this->demoIdsFrom('posts')));
        $this->assertEmpty(collect($data['events'])->pluck('id')->intersect($this->demoIdsFrom('events')));
        $this->assertEmpty(collect($data['sponsorships'])->pluck('id')->intersect($this->demoIdsFrom('sponsorships')));
        $this->assertEmpty(collect($data['opportunities'])->pluck('id')->intersect($this->demoIdsFrom('job_offers')));
    }

    public function test_demo_session_search_still_finds_demo_accounts(): void
    {
        $demoUser = $this->demoUser();

        $users = $this->withToken($this->tokenFor($demoUser, ['demo']))
            ->getJson('/api/v1/search?q='.urlencode('Ramírez'))
            ->assertOk()
            ->json('data.users');

        $this->assertContains($demoUser->id, collect($users)->pluck('id'));
    }

    public function test_real_user_cannot_open_demo_detail_endpoints(): void
    {
        $realUser = User::factory()->create();
        $token = $this->tokenFor($realUser);

        $demoPost = Post::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();
        $demoEvent = Event::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();
        $demoSponsorship = Sponsorship::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();
        $demoJob = JobOffer::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();
        $demoUser = $this->demoUser();

        $this->withToken($token)->getJson('/api/v1/posts/'.$demoPost->id)->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/posts/'.$demoPost->slug)->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/posts/'.$demoPost->id.'/comments')->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/events/'.$demoEvent->id)->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/sponsorships/'.$demoSponsorship->id)->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/jobs/'.$demoJob->id)->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/user/'.$demoUser->id)->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/references/users/'.$demoUser->id)->assertNotFound();
    }

    public function test_real_user_cannot_interact_with_demo_opportunities(): void
    {
        $realUser = User::factory()->create();
        $token = $this->tokenFor($realUser);

        $demoEvent = Event::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();
        $demoJob = JobOffer::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();

        $this->withToken($token)->postJson('/api/v1/events/'.$demoEvent->id.'/participate')->assertNotFound();
        $this->withToken($token)->postJson('/api/v1/jobs/'.$demoJob->id.'/apply')->assertNotFound();
    }

    public function test_demo_session_can_still_open_its_own_detail_endpoints(): void
    {
        $demoUser = $this->demoUser();
        $token = $this->tokenFor($demoUser, ['demo']);

        $demoPost = Post::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();
        $demoEvent = Event::query()->whereIn('user_id', $this->demoUserIds())->firstOrFail();

        $this->withToken($token)->getJson('/api/v1/posts/'.$demoPost->slug)->assertOk();
        $this->withToken($token)->getJson('/api/v1/events/'.$demoEvent->id)->assertOk();
        $this->withToken($token)->getJson('/api/v1/user/'.$demoUser->id)->assertOk();
    }

    private function demoUser(): User
    {
        return User::query()->where('email', config('demo.user_email'))->firstOrFail();
    }

    private function tokenFor(User $user, array $abilities = ['*']): string
    {
        return $user->createToken('visibility-test', $abilities)->plainTextToken;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function demoUserIds(): \Illuminate\Support\Collection
    {
        return User::query()->where('is_demo', true)->pluck('id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function demoIdsFrom(string $table): \Illuminate\Support\Collection
    {
        return DB::table($table)->whereIn('user_id', $this->demoUserIds())->pluck('id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function listIds(string $token, string $endpoint): \Illuminate\Support\Collection
    {
        $payload = $this->withToken($token)->getJson($endpoint)->assertOk()->json();

        return collect($payload['data'] ?? [])->pluck('id');
    }
}
