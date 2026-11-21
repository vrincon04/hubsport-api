<?php

namespace Tests\Feature\Flows;

use App\Models\Connection;
use App\Models\Country;
use App\Models\News;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class PrototypeFeaturesFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_feed_sport_filter_circle_talent_jobs_and_public_share(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $football = Sport::factory()->create(['name' => 'fútbol']);
        $baseball = Sport::factory()->create(['name' => 'béisbol']);

        $viewer = User::factory()->create(['name' => 'Viewer', 'slug' => 'viewer-hub']);
        Profile::query()->create([
            'user_id' => $viewer->id,
            'first_name' => 'View',
            'last_name' => 'Er',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $football->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => '1999-01-01',
        ]);

        $footballAthlete = User::factory()->create(['name' => 'Foot Athlete', 'slug' => 'foot-athlete']);
        Profile::query()->create([
            'user_id' => $footballAthlete->id,
            'first_name' => 'Foot',
            'last_name' => 'Athlete',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $football->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'verified_badge' => true,
            'birth_date' => '2001-01-01',
        ]);

        $baseballAthlete = User::factory()->create(['name' => 'Base Athlete', 'slug' => 'base-athlete']);
        Profile::query()->create([
            'user_id' => $baseballAthlete->id,
            'first_name' => 'Base',
            'last_name' => 'Athlete',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $baseball->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => '2000-01-01',
        ]);

        $postFoot = Post::query()->create([
            'user_id' => $footballAthlete->id,
            'title' => 'Gol',
            'body' => 'Golazo',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Post::query()->create([
            'user_id' => $baseballAthlete->id,
            'title' => 'HR',
            'body' => 'Home run',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $mutual = User::factory()->create(['name' => 'Mutual Friend']);
        Profile::query()->create([
            'user_id' => $mutual->id,
            'first_name' => 'Mu',
            'last_name' => 'Tual',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $football->id,
            'profile_type' => 'fan',
            'account_status' => 'active',
            'birth_date' => '1995-01-01',
        ]);

        Connection::query()->create([
            'user_id' => $viewer->id,
            'connected_user_id' => $mutual->id,
            'status' => 'accepted',
        ]);
        Connection::query()->create([
            'user_id' => $footballAthlete->id,
            'connected_user_id' => $mutual->id,
            'status' => 'accepted',
        ]);
        Connection::query()->create([
            'user_id' => $viewer->id,
            'connected_user_id' => $footballAthlete->id,
            'status' => 'accepted',
        ]);

        $mySport = $this->asApiUser($viewer)->getJson('/api/v1/posts?my_sport=1');
        $mySport->assertSuccessful();
        $ids = collect($mySport->json('data'))->pluck('id')->all();
        $this->assertContains($postFoot->id, $ids);
        $this->assertCount(1, $ids);

        $this->asApiUser($viewer)
            ->getJson('/api/v1/user/'.$footballAthlete->id)
            ->assertSuccessful()
            ->assertJsonPath('data.handle', '@foot-athlete')
            ->assertJsonPath('data.mutual_connections_count', 1)
            ->assertJsonPath('data.connections_count', 2);

        $this->asApiUser($viewer)
            ->getJson('/api/v1/connections/users/'.$footballAthlete->id.'/circle')
            ->assertSuccessful()
            ->assertJsonPath('data.user.mutual_connections_count', 1);

        $recruiter = User::factory()->create(['name' => 'Org']);
        Profile::query()->create([
            'user_id' => $recruiter->id,
            'first_name' => 'Org',
            'last_name' => 'Rec',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $football->id,
            'profile_type' => 'recruiter',
            'account_status' => 'active',
            'verified_badge' => false,
            'birth_date' => '1985-01-01',
        ]);

        $this->asApiUser($recruiter)
            ->postJson('/api/v1/jobs', [
                'title' => 'Scout',
                'company' => 'Club',
                'location' => 'SD',
                'contract_type' => 'full_time',
                'description' => 'Desc',
                'application_type' => 'simple',
            ])
            ->assertStatus(403);

        $recruiter->profile->update(['verified_badge' => true]);

        $this->asApiUser($recruiter)
            ->getJson('/api/v1/search?talent=1&verified_only=1')
            ->assertSuccessful();

        $news = News::query()->create([
            'title' => 'Noticia pública',
            'content' => 'Contenido',
            'sport_id' => $football->id,
        ]);

        $this->getJson('/api/v1/public/posts/'.$postFoot->id)
            ->assertSuccessful()
            ->assertJsonPath('data.type', 'post')
            ->assertJsonPath('data.cta.register_path', '/register');

        $this->getJson('/api/v1/public/sport/news/'.$news->id)
            ->assertSuccessful()
            ->assertJsonPath('data.type', 'news');
    }
}
