<?php

namespace App\Demo;

use Illuminate\Support\Facades\Cache;

final class DemoOverlay
{
    public static function key(string $userId): string
    {
        return 'demo:state:'.$userId.':v1';
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $userId): array
    {
        $state = Cache::get(self::key($userId));

        return is_array($state) ? $state : [
            'joined_groups' => ['grp_demo_runners'],
            'group_posts' => [],
            'orders' => [],
            'sponsorship_requests' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function put(string $userId, array $state): void
    {
        Cache::put(self::key($userId), $state, now()->addHours(6));
    }

    public static function forget(string $userId): void
    {
        Cache::forget(self::key($userId));
    }
}
