<?php

namespace App\Jobs;

use App\Mail\TicketDetalleMailable;
use App\Models\Ticket;
use App\TicketCorreoData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarTicketDetalleCorreo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TicketCorreoData $data
    ) {}

    public function handle(): void
    {
        $ticket = Ticket::find($this->data->ticket_id);

        if (!$ticket) {
            Log::warning("Ticket no encontrado. ID: {$this->data->ticket_id}");
            return;
        }

        Log::info("Enviando correo de detalle para ticket #{$ticket->id}");

        Mail::to($this->data->destinatario)->send(
            new TicketDetalleMailable(
                $ticket,
                $this->data->asunto,
                $this->data->mensaje
            )
        );
    }
}
