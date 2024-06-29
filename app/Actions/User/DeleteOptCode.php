<?php

namespace App\Actions\User;

use App\Models\EmailVerification;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteOptCode
{
    use AsAction;

    public function handle(User $user): void
    {
        EmailVerification::where(['email' => $user->email])
            ->delete();
    }
}
