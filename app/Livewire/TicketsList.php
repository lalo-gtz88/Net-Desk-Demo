<?php

namespace App\Livewire;

use App\Events\CambiosTicket;
use App\Jobs\SendTelegramNotification;
use App\Models\Actividad;
use App\Models\ExternalReport;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UsuarioActividades;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketsList extends Component
{
    use WithPagination;

    // URL-bound filters
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $fs = 'id'; // filter column

    #[Url(history: true)]
    public string $fu = ''; // assigned user

    #[Url(history: true)]
    public string $fst = 'ABIERTO'; // status filter

    // UI state
    public ?int $ticketID = null;
    public $listaActividades = [];
    public $reportesExternos = [];
    public bool $actividadesCompletadas = false;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['ticket-saved' => '$refresh'];

    /**
     * Render tickets list with related dashboard data.
     */
    public function render()
    {
        $tecnicos = $this->getTecnicos();
        $user = Auth::user();

        // Restrict ticket visibility if user lacks permission
        if (! $user->can('Mostrar todos los tickets')) {
            $this->fu = (string) $user->id;
            $this->dispatch('disabledFiltro');
        }

        $tickets = $this->getTickets();
        $this->listaActividades = $this->getActividades();
        $this->reportesExternos = $this->getReportesExternos();
        $this->checkCompletedActivities();

        return view('livewire.tickets-list', compact('tickets', 'tecnicos'));
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when status filter changes.
     */
    public function updatedFst(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when assigned user filter changes.
     */
    public function updatedFu(): void
    {
        $this->resetPage();
    }

    /**
     * Get filtered and paginated tickets.
     */
    private function getTickets()
    {
        return Ticket::where('active', 1)
            ->where($this->fs, 'LIKE', '%' . $this->search . '%')
            ->when(is_numeric($this->fu), fn ($q) => $q->where('asignado', $this->fu))
            ->where('status', $this->fst)
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    /**
     * Retrieve active technicians.
     */
    private function getTecnicos()
    {
        return User::where('activo', 1)
            ->where('tecnico', 1)
            ->orderBy('name')
            ->get();
    }

    /**
     * Soft-delete a ticket.
     */
    public function delete(int $id): void
    {
        Ticket::findOrFail($id)->delete();

        $this->dispatch('ticket-saved')->to(CajaEstadistica::class);
        $this->dispatch('alerta', type: 'success', msg: 'Registro eliminado!');
    }

    /**
     * Assign a ticket to a technician.
     */
    public function asignar(int $ticketID, int $tecnicoID): void
    {
        $ticket = Ticket::findOrFail($ticketID);
        $ticketOld = clone $ticket;

        $ticket->asignado = $tecnicoID;
        $ticket->save();

        $this->dispatch('alerta', type: 'success', msg: 'Ticket assigned successfully.');

        // Track ticket changes
        CambiosTicket::dispatch($ticketOld, $ticket);

        // Notify via Telegram
        SendTelegramNotification::dispatch($ticket->id);
    }

    /**
     * Get pending user activities.
     */
    private function getActividades()
    {
        return UsuarioActividades::with('actividad')
            ->join('actividades', 'actividades.id', '=', 'usuario_actividades.actividad_id')
            ->where('actividades.active', 1)
            ->where(function ($q) {
                $q->where('usuario_id', Auth::id())
                  ->orWhere('creador', Auth::id());
            })
            // Prioritize activities with a valid date
            ->orderByRaw('CASE WHEN actividades.fecha IS NULL THEN 1 ELSE 0 END')
            ->orderBy('actividades.fecha')
            ->orderBy('actividades.hora')
            ->get()
            ->unique('actividad_id')
            ->values();
    }

    /**
     * Check if there are completed activities.
     */
    private function checkCompletedActivities(): void
    {
        if (! count($this->listaActividades)) {
            $this->actividadesCompletadas = false;
            return;
        }

        $this->actividadesCompletadas = $this->listaActividades->contains(
            fn ($item) => (bool) $item->actividad->status
        );
    }

    /**
     * Toggle activity completion status.
     */
    public function checkActividad(int $id): void
    {
        $actividad = Actividad::find($id);
        if (! $actividad) {
            return;
        }

        $actividad->status = ! $actividad->status;
        $actividad->save();
    }

    /**
     * Deactivate completed activities.
     */
    public function removeActividades(): void
    {
        $actividades = UsuarioActividades::with('actividad')
            ->whereRelation('actividad', 'status', 1)
            ->whereRelation('actividad', 'active', 1)
            ->where(function ($q) {
                $q->where('usuario_id', Auth::id())
                  ->orWhere('creador', Auth::id());
            })
            ->get();

        foreach ($actividades as $item) {
            $item->actividad->active = 0;
            $item->actividad->save();
        }

        $this->dispatch('alerta', type: 'success', msg: 'Completed activities removed.');
    }

    /**
     * Get pending external reports.
     */
    private function getReportesExternos()
    {
        return ExternalReport::where('estatus', 'pendiente')->get();
    }
}
