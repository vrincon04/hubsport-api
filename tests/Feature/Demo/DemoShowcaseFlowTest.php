<?php

namespace Tests\Feature\Demo;

use App\Models\User;
use Database\Seeders\DemoFullShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoShowcaseFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);
    }

    public function test_showcase_includes_group_covers_and_purchasable_products(): void
    {
        $token = $this->demoToken();
        $data = $this->withToken($token)->getJson('/api/v1/demo/showcase')->assertOk()->json('data');

        $this->assertTrue($data['groups'][0]['joined']);
        $this->assertNotEmpty($data['groups'][0]['image_url']);
        $this->assertNotEmpty($data['groups'][0]['posts']);
        $this->assertTrue($data['store_products'][0]['purchasable']);
        $this->assertNotEmpty($data['store_products'][0]['image_url']);
        $this->assertStringNotContainsString('no comprable', strtolower(json_encode($data)));
    }

    public function test_demo_user_can_join_group_post_checkout_and_apply(): void
    {
        $token = $this->demoToken();

        $this->withToken($token)
            ->postJson('/api/v1/demo/groups/grp_demo_coaches/join')
            ->assertOk()
            ->assertJsonPath('data.joined', true);

        $this->withToken($token)
            ->postJson('/api/v1/demo/groups/grp_demo_coaches/posts', ['body' => 'Listos para la clínica del sábado.'])
            ->assertCreated();

        $groups = collect($this->withToken($token)->getJson('/api/v1/demo/showcase')->json('data.groups'));
        $coaches = $groups->firstWhere('id', 'grp_demo_coaches');
        $this->assertTrue($coaches['joined']);
        $this->assertTrue(collect($coaches['posts'])->contains(fn ($post) => str_contains($post['body'], 'clínica')));

        $order = $this->withToken($token)
            ->postJson('/api/v1/demo/store/checkout', [
                'product_id' => 'prd_demo_spikes',
                'variant' => '41',
                'recipient_name' => 'Sofía Ramírez',
                'city' => 'Santo Domingo',
                'address' => 'Pista Centro Olímpico 12',
            ])
            ->assertCreated()
            ->json('data');

        $this->assertSame('confirmed', $order['status']);
        $this->assertSame('Clavos de pista 400 m', $order['product_name']);

        $this->withToken($token)
            ->postJson('/api/v1/demo/sponsorships/col_demo_training/apply', [
                'message' => 'Quiero apoyo de viaje para el encuentro nacional.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'received');

        $this->withToken($token)
            ->postJson('/api/v1/demo/sponsorships/brand-1/apply')
            ->assertNotFound();
    }

    private function demoToken(): string
    {
        $user = User::query()->where('email', config('demo.user_email'))->firstOrFail();

        return $user->createToken('showcase-flow', ['demo'])->plainTextToken;
    }
}
