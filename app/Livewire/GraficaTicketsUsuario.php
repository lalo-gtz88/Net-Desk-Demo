<?php

namespace App\Livewire;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GraficaTicketsUsuario extends Component
{
    /** Chart labels (technicians) */
    public $userLabels = [];

    /** Chart data (ticket count per technician) */
    public $userData = [];

    /**
     * Component initialization.
     */
    public function mount()
    {
        $this->obtenerDatos();
    }

    /**
     * Render technician chart component.
     */
    public function render()
    {
        $this->obtenerDatos();
        return view('livewire.grafica-tickets-usuario');
    }

    /**
     * Retrieve ticket statistics grouped by assigned technician.
     */
    protected function obtenerDatos()
    {
        $ticketsByUser = Ticket::select(
                'asignado',
                DB::raw('COUNT(*) as total')
            )
            ->where('active', 1)
            ->whereNotNull('asignado')
            ->where('status', 'abierto')
            ->groupBy('asignado')
            ->with('tecnico') // technician relationship
            ->get();

        $this->userLabels = $ticketsByUser
            ->map(fn ($t) => optional($t->tecnico)->name ?? 'Desconocido')
            ->toArray();

        $this->userData = $ticketsByUser
            ->pluck('total')
            ->toArray();
    }
}
