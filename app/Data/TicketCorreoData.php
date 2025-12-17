<?php

namespace App\Data;

class TicketCorreoData
{
    public function __construct(
        public int $ticket_id,
        public string $destinatario,
        public string $asunto,
        public string $mensaje
    ) {}
}
