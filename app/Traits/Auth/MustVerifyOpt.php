<?php

namespace App\Traits\Auth;

use App\Actions\User\CreateOptCode;
use App\Notifications\Auth\OptNotification;
use Illuminate\Support\Facades\Log;

trait MustVerifyOpt
{
    public function sendEmailOptNotification(): void
    {
        $code = CreateOptCode::run($this);
        Log::info("OPT", ['code' => $code]);
        $this->notify(new OptNotification($code));
    }
}
