<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CambiosTicket
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Ticket $ticketOld,
        public Ticket $ticketNew
    ) {}
}
