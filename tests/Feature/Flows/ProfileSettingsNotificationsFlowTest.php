<?php

namespace Tests\Feature\Flows;

use App\Models\User;
use App\Notifications\DatabaseDemoNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ProfileSettingsNotificationsFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_me_settings_and_notifications_flow(): void
    {
        $user = User::factory()->create(['name' => 'Perfil Flow']);
        $other = User::factory()->create(['name' => 'Otro Perfil']);

        $this->asApiUser($user)
            ->getJson('/api/v1/user/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);

        $this->asApiUser($user)
            ->getJson("/api/v1/user/{$other->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $other->id);

        $this->asApiUser($user)
            ->getJson('/api/v1/user/settings')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->asApiUser($user)
            ->patchJson('/api/v1/user/settings', [
                'privacy' => ['publicProfile' => false],
                'notifications' => ['emailEnabled' => true],
            ])
            ->assertOk()
            ->assertJsonPath('data.privacy.publicProfile', false)
            ->assertJsonPath('data.notifications.emailEnabled', true);

        $notificationId = (string) Str::uuid();
        DatabaseNotification::query()->create([
            'id' => $notificationId,
            'type' => DatabaseDemoNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'Nueva conexión',
                'body' => 'Alguien quiere conectar contigo',
                'type' => 'connection',
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->asApiUser($user)
            ->getJson('/api/v1/user/notifications')
            ->assertOk()
            ->assertJsonFragment(['id' => $notificationId]);

        $this->asApiUser($user)
            ->patchJson("/api/v1/user/notifications/{$notificationId}/read")
            ->assertOk();

        $this->assertNotNull(
            DatabaseNotification::query()->whereKey($notificationId)->value('read_at')
        );

        $this->asApiUser($user)
            ->postJson('/api/v1/user/notifications/read-all')
            ->assertOk();
    }
}
