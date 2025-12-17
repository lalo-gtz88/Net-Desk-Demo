<?php

namespace App\Livewire;

use Livewire\Component;

class HistorialTicket extends Component
{
    /** Ticket identifier */
    public $ticketID;

    /** Ticket and related equipment (loaded in child components) */
    public $ticket;
    public $equipo;

    /**
     * Unique key used to force component re-hydration.
     */
    public $uniqueId;

    /**
     * Listen for external refresh events.
     */
    protected $listeners = ['refrescar' => '$refresh'];

    /**
     * Component initialization.
     *
     * A unique identifier is generated to allow full component re-mounting
     * when required by parent components.
     */
    public function mount($id)
    {
        $this->uniqueId = now()->timestamp;
        $this->ticketID = $id;
    }

    /**
     * Render ticket history container.
     */
    public function render()
    {
        return view('livewire.historial-ticket');
    }
}

