<?php

namespace App\Actions\User;

use App\Models\EmailVerification;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOptCode
{
    use AsAction;

    public function handle(User $user): string
    {
        $code = rand(1000, 9999);

        EmailVerification::create([
            'email' => $user->email,
            'code'  => $code
        ]);

        return $code;
    }
}
