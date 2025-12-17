<?php

namespace App\Livewire;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    /** Ticket counters */
    public $openCount;
    public $inProgressCount;
    public $unAssigned;

    /** Chart data: ticket status */
    public $statusLabels = [];
    public $statusData = [];

    /** Chart data: tickets per day */
    public $dailyLabels = [];
    public $dailyData = [];

    /** Chart data: tickets per technician */
    public $userLabels = [];
    public $userData = [];

    /** Chart data: tickets per building */
    public $edificioLabels = [];
    public $edificioData = [];

    /** UI alert state */
    public bool $alertaVisible = false;
    public $alerta = '';

    /**
     * Component initialization.
     */
    public function mount()
    {
        $this->obtenerDatos();
    }

    /**
     * Render dashboard view.
     */
    public function render()
    {
        $this->obtenerDatos();
        return view('livewire.dashboard');
    }

    /**
     * Retrieve and prepare all dashboard metrics.
     */
    protected function obtenerDatos()
    {
        /*
        |--------------------------------------------------------------------------
        | Tickets by status
        |--------------------------------------------------------------------------
        */
        $this->openCount = Ticket::where('status', 'Abierto')
            ->where('active', 1)
            ->count();

        $this->inProgressCount = Ticket::where('status', 'Pendiente')
            ->where('active', 1)
            ->count();

        // Tickets without assigned technician
        $this->unAssigned = Ticket::where('status', 'Abierto')
            ->where('active', 1)
            ->where(function ($q) {
                $q->where('asignado', 0)
                  ->orWhere('asignado', 1);
            })
            ->count();

        $this->statusLabels = ['Abierto', 'Pendiente', 'Sin asignar'];
        $this->statusData = [
            $this->openCount,
            $this->inProgressCount,
            $this->unAssigned,
        ];

        /*
        |--------------------------------------------------------------------------
        | Tickets per day (last 7 days)
        |--------------------------------------------------------------------------
        */
        $dailyTickets = Ticket::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->reverse();

        $this->dailyLabels = $dailyTickets
            ->pluck('date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))
            ->toArray();

        $this->dailyData = $dailyTickets
            ->pluck('total')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Tickets per technician
        |--------------------------------------------------------------------------
        */
        $ticketsByUser = Ticket::select(
                'asignado',
                DB::raw('COUNT(*) as total')
            )
            ->where('active', 1)
            ->whereNotNull('asignado')
            ->where('status', 'abierto')
            ->groupBy('asignado')
            ->with('tecnico')
            ->get();

        $this->userLabels = $ticketsByUser
            ->map(fn ($t) => optional($t->tecnico)->name ?? 'Desconocido')
            ->toArray();

        $this->userData = $ticketsByUser
            ->pluck('total')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Tickets per building
        |--------------------------------------------------------------------------
        */
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
