<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ListTicketsTablet extends Component
{
    use WithPagination;

    /** Tickets collection used for tablet view */
    public $tickets = [];

    /** Search term */
    #[Url(history: true)]
    public $search;

    /** Field used for filtering/sorting */
    #[Url(history: true)]
    public $fs = 'id';

    /** Assigned user filter */
    #[Url(history: true)]
    public $fu = '';

    /** Ticket status filter */
    #[Url(history: true)]
    public $fst = 'ABIERTO';

    /**
     * Initialize filters from URL if present.
     */
    public function mount($search = null, $fs = null, $fu = null, $fst = null)
    {
        if ($search !== null) $this->search = $search;
        if ($fs !== null) $this->fs = $fs;
        if ($fu !== null) $this->fu = $fu;
        if ($fst !== null) $this->fst = $fst;

        $this->loadTickets();
    }

    /**
     * Render tablet ticket list.
     */
    #[Layout('components.layouts.app2')]
    public function render()
    {
        $tecnicos = $this->getTecnicos();
        $user = Auth::user();

        // Restrict filter if user cannot view all tickets
        if (!$user->can('Mostrar todos los tickets')) {
            $this->fu = $user->id;
            $this->dispatch('disabledFiltro');
        }

        return view('livewire.list-tickets-tablet', compact('tecnicos'));
    }

    /**
     * Paginated ticket list (used when needed).
     */
    protected function getTickets()
    {
        return Ticket::where('active', 1)
            ->where($this->fs, 'LIKE', '%' . $this->search . '%')
            ->when(is_numeric($this->fu), fn ($q) => $q->where('asignado', $this->fu))
            ->where('status', $this->fst)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
    }

    /**
     * Retrieve active technicians.
     */
    protected function getTecnicos()
    {
        return User::where('activo', 1)
            ->where('tecnico', 1)
            ->orderBy('name')
            ->get();
    }

    /**
     * Soft delete ticket.
     */
    public function delete($id)
    {
        Ticket::find($id)?->delete();

        $this->dispatch('ticket-saved')->to(CajaEstadistica::class);
        $this->dispatch('alerta', type: 'success', msg: 'Record deleted successfully');
    }

    /**
     * Trigger filters manually (tablet submit action).
     */
    public function submitFilters()
    {
        $this->loadTickets();
    }

    /**
     * Load filtered ticket collection (non-paginated).
     */
    private function loadTickets()
    {
        $query = Ticket::query()->where('active', 1);

        // Search filter
        if ($this->search) {
            $query->where($this->fs, 'LIKE', '%' . $this->search . '%');
        }

        // Status filter
        if ($this->fst) {
            $query->where('status', strtolower($this->fst));
        }

        // Assigned user filter
        if (is_numeric($this->fu)) {
            $query->where('asignado', $this->fu);
        }

        $query->orderBy('created_at', 'DESC');

        $this->tickets = $query->get();
    }
}
