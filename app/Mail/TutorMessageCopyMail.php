<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorMessageCopyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $minor,
        public User $adult,
        public string $messageText,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Copia: mensaje a '.($this->minor->name ?? 'un menor').' en HubSport',
        );
    }

    public function content(): Content
    {
        $preview = e(mb_substr($this->messageText, 0, 500));

        return new Content(
            htmlString: '<p>Hola,</p>'
                .'<p>Un adulto (<strong>'.e($this->adult->name).'</strong>) envió un mensaje a '
                .e($this->minor->name).' en HubSport. Como tutor, recibes esta copia:</p>'
                .'<blockquote style="border-left:3px solid #3B82F6;padding-left:12px;color:#334155;">'
                .$preview
                .'</blockquote>'
                .'<p>Si el mensaje te parece inapropiado, pide al menor que use Reportar en la app.</p>',
        );
    }
}
