<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuardianConsentMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $minor,
        public string $confirmUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirma la cuenta HubSport de un menor',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Hola,</p>'
                .'<p>'.e($this->minor->name).' quiere usar HubSport y te indicó como tutor.</p>'
                .'<p>Si autorizas la cuenta, confirma aquí:</p>'
                .'<p><a href="'.e($this->confirmUrl).'">'.e($this->confirmUrl).'</a></p>'
                .'<p>Si no reconoces esta solicitud, ignora este correo.</p>',
        );
    }
}
