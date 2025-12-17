<?php

namespace App\Mail;

use App\Models\Seguimiento;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketDetalleMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Ticket comments / follow-ups
     */
    public array $comentarios = [];

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $asunto,
        public ?string $mensaje = null
    ) {
        // Load ticket comments
        $this->comentarios = Seguimiento::where('ticket', $this->ticket->id)->get()->toArray();
    }

    /**
     * Define the email subject.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    /**
     * Define the email content view.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-detalle',
        );
    }

    /**
     * Attach related files to the email.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $seguimientos = Seguimiento::where('ticket', $this->ticket->id)
            ->whereNotNull('file')
            ->get();

        return $seguimientos
            ->map(function ($seguimiento) {
                $path = "documents/{$seguimiento->file}";

                return Attachment::fromStorageDisk('public', $path)
                    ->as($seguimiento->file);
            })
            ->toArray();
    }
}
