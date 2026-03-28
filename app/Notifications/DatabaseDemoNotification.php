<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Tipo referenciado por las filas de `notifications` creadas en DemoContentSeeder.
 */
class DatabaseDemoNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Demo',
            'body' => 'Notificación de prueba',
        ];
    }
}
