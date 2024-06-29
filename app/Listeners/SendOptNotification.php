<?php

namespace App\Listeners;

use App\Contracts\Auth\MustVerifyOpt;
use Illuminate\Auth\Events\Registered;

class SendOptNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if ($event->user instanceof MustVerifyOpt) {
            $event->user->sendEmailOptNotification();
        }
    }
}
