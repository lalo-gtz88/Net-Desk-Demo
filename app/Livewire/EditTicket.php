<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Attributes\On;
use Livewire\Component;

class EditTicket extends Component
{
    /** Ticket identifier */
    public $ticketID;

    /** Editable fields */
    public $tema = '';
    public $descripcion = '';

    /**
     * Render edit ticket modal.
     */
    public function render()
    {
        return view('livewire.edit-ticket');
    }

    /**
     * Load ticket data into the edit form.
     * Triggered from parent component.
     */
    #[On('editar')]
    public function setValores($id)
    {
        $ticket = Ticket::find($id);

        $this->ticketID = $id;
        $this->tema = $ticket->tema;
        $this->descripcion = $ticket->descripcion;

        // Send current description to the editor (e.g. WYSIWYG)
        $this->dispatch('showEditTicket', descripcion: $this->descripcion);
    }

    /**
     * Persist ticket changes.
     */
    public function save()
    {
        $this->validate([
            'tema' => 'required|max:255',
            'descripcion' => 'required',
        ]);

        $ticket = Ticket::find($this->ticketID);
        $ticket->tema = $this->tema;
        $ticket->descripcion = $this->descripcion;
        $ticket->save();

        // Notify ticket list to refresh data
        $this->dispatch('ticket-saved')->to('TicketsList');

        // UI feedback
        $this->dispatch('alerta', type: 'success', msg: 'Changes saved successfully');

        // Close modal and reset form state
        $this->dispatch('cerrarModal');
        $this->reset('tema', 'ticketID', 'descripcion');
    }
}
