<?php

namespace App\Demo;

final class DemoAccess
{
    public static function assert(\Illuminate\Http\Request $request): \App\Models\User
    {
        $user = $request->user();

        abort_unless(
            config('demo.enabled')
                && $user
                && hash_equals((string) config('demo.user_email'), (string) $user->email)
                && $user->tokenCan('demo')
                && ! $user->tokenCan('*'),
            \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN
        );

        return $user;
    }
}
