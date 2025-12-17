<?php

namespace App\Livewire;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GraficaTicketsPrioridad extends Component
{
    /** Chart labels (priority levels) */
    public $userLabels = [];

    /** Chart data (ticket count per priority) */
    public $userData = [];

    /**
     * Component initialization.
     */
    public function mount()
    {
        $this->obtenerDatos();
    }

    /**
     * Render priority chart component.
     */
    public function render()
    {
        $this->obtenerDatos();
        return view('livewire.grafica-tickets-prioridad');
    }

    /**
     * Retrieve ticket statistics grouped by priority.
     */
    protected function obtenerDatos()
    {
        $tickets = Ticket::where('active', 1)
            ->where('status', 'abierto')
            ->select('prioridad', DB::raw('COUNT(*) as total'))
            ->groupBy('prioridad')
            ->get();

        // Default dataset to keep chart consistent
        $data = [
            'Baja'  => 0,
            'Media' => 0,
            'Alta'  => 0,
        ];

        // Normalize and map query results
        foreach ($tickets as $ticket) {
            $prioridad = ucfirst(strtolower($ticket->prioridad));
            if (isset($data[$prioridad])) {
                $data[$prioridad] = $ticket->total;
            }
        }
