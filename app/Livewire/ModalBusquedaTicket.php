<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Component;

class ModalBusquedaTicket extends Component
{
    /**
     * Search input value.
     */
    public $buscar;

    /**
     * Collection of found tickets.
     */
    public $ticketsEncontrados = [];

    /**
     * Render ticket search modal.
     */
    public function render()
    {
        return view('livewire.modal-busqueda-ticket');
    }

    /**
     * Search tickets by ID or text fields.
     */
    public function buscarTicket()
    {
        $this->ticketsEncontrados = [];

        if (empty($this->buscar)) {
            return;
        }

        $this->ticketsEncontrados = Ticket::query()
            ->where('id', $this->buscar)
            ->orWhere('tema', 'like', "%{$this->buscar}%")
            ->orWhere('descripcion', 'like', "%{$this->buscar}%")
            ->orWhere('reporta', 'like', "%{$this->buscar}%")
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Redirect to ticket detail/edit view.
     */
    public function irATicket(int $id)
    {
        $this->reset('buscar', 'ticketsEncontrados');

        return redirect()->route('editarTicket', $id);
    }
}
