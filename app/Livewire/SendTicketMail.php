<?php

namespace App\Livewire;

use App\Jobs\EnviarTicketDetalleCorreo;
use App\Models\Ticket;
use App\TicketCorreoData;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class SendTicketMail extends Component
{
    /**
     * Ticket instance to be sent by email.
     */
    public Ticket $ticket;

    /**
     * Email subject.
     */
    public string $asunto = '';

    /**
     * Optional custom message body.
     */
    public ?string $mensaje = null;

    /**
     * Comma-separated list of recipients.
     */
    public string $destinatarios = '';

    /**
     * Initialize component with ticket data.
     */
    public function mount(int $id): void
    {
        $this->ticket = Ticket::findOrFail($id);
        $this->asunto = "Detalle de ticket #{$this->ticket->id}";
    }

    /**
     * Render mail form view.
     */
    public function render()
    {
        return view('livewire.send-ticket-mail');
    }

    /**
     * Validate input and dispatch email jobs.
     */
    public function enviarCorreo(): void
    {
        $this->validate([
            'asunto' => 'required|string',
            'destinatarios' => 'required|string',
        ]);

        $destinatarios = $this->destinatarios2Array($this->destinatarios);

        // Validate each email address individually
        $validator = Validator::make(
            ['emails' => $destinatarios],
            ['emails.*' => ['email']]
        );

        if ($validator->fails()) {
            $this->addError('mailfail', 'One or more email addresses are invalid.');
            return;
        }

        // Dispatch one job per recipient
        foreach ($destinatarios as $destinatario) {
            $data = new TicketCorreoData(
                ticket_id: $this->ticket->id,
                destinatario: $destinatario,
                asunto: $this->asunto,
                mensaje: $this->mensaje
            );

            EnviarTicketDetalleCorreo::dispatch($data);
        }

        $this->dispatch('alerta', msg: 'Email sent successfully!', type: 'success');
        $this->dispatch('backTickets');
    }

    /**
     * Convert comma-separated emails into a clean array.
     */
    private function destinatarios2Array(string $destinatarios): array
    {
        return collect(explode(',', $destinatarios))
            ->map(fn ($correo) => trim($correo))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
