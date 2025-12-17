<?php

namespace App\Livewire;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GraficaTicketsEdificio extends Component
{
    /** Chart labels (buildings) */
    public $edificioLabels = [];

    /** Chart data (ticket count) */
    public $edificioData = [];

    /**
     * Component initialization.
     */
    public function mount()
    {
        $this->obtenerDatos();
    }

    /**
     * Render chart component.
     */
    public function render()
    {
        $this->obtenerDatos();
        return view('livewire.grafica-tickets-edificio');
    }

    /**
     * Retrieve ticket statistics grouped by building.
     */
    protected function obtenerDatos()
    {
        $ticketsByEdificio = Ticket::select(
                'edificio',
                DB::raw('COUNT(*) as total')
            )
            ->where('status', 'Abierto')
            ->where('active', 1)
            ->where('asignado', '!=', 0)
            ->groupBy('edificio')
            ->get();

        $this->edificioLabels = $ticketsByEdificio
            ->pluck('edificio')
            ->toArray();

        $this->edificioData = $ticketsByEdificio
            ->pluck('total')
            ->toArray();
    }
}
