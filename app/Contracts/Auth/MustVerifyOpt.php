<?php

namespace App\Contracts\Auth;

interface MustVerifyOpt
{
    /**
     * Send the email opt notification.
     *
     * @return void
     */
    public function sendEmailOptNotification(): void;
}
