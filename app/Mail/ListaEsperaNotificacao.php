<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListaEsperaNotificacao extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nomeInteressado,
        public string $emailInteressado,
        public string $plano,
    ) {}

    public function envelope(): Envelope
    {
        $planoLabel = $this->plano === 'familia' ? 'Plano Família' : 'Plano Clube';

        return new Envelope(
            subject: "Nova inscrição na lista de espera – {$planoLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lista-espera',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
