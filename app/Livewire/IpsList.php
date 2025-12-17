<?php

namespace App\Livewire;

use App\Models\Equipo;
use App\Models\Ip;
use App\Models\Segmento;
use Livewire\Component;
use Livewire\WithPagination;

class IpsList extends Component
{
    use WithPagination;

    /** Search input for IP filtering */
    public $search = '';

    /** Network segments catalog */
    public $segmentos = [];

    /** Selected segment */
    public $segmento;

    /** IP selected for assignment */
    public $ipAsignar;

    /** Equipment search input */
    public $busEq;

    /** Equipment search results */
    public $equipos_encontrados = [];

    /** Selected equipment ID */
    public $idEq;

    /**
     * Load initial catalog data.
     */
    public function mount()
    {
        $this->segmentos = Segmento::orderBy('nombre')->get();
    }

    /**
     * Render IP list.
     */
    public function render()
    {
        $ips = $this->getIps();
        return view('livewire.ips-list', compact('ips'));
    }

    /**
     * Reset pagination when search input changes.
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Retrieve paginated IP list with filters applied.
     */
    protected function getIps()
    {
        $query = Ip::with('equipo');

        // Filter by network segment
        if ($this->segmento) {
            $query->where('segmento_id', $this->segmento);
        }

        // Filter by IP (INET_NTOA allows human-readable search)
        if (!empty($this->search)) {
            $termino = preg_replace('/[^0-9\.]/', '', $this->search);
            $query->whereRaw('INET_NTOA(ip) LIKE ?', ["%{$termino}%"]);
        }

        return $query->paginate(20);
    }

    /**
     * Find first available IP within the selected segment.
     */
    public function buscarIpDisponible()
    {
        if (!$this->segmento) {
            $this->dispatch('alerta', msg: 'Select a segment first', type: 'warning');
            return;
        }

        $this->resetPage();

        $ip = Ip::where('segmento_id', $this->segmento)
            ->where('en_uso', false)
            ->orderBy('ip')
            ->first();

        if ($ip) {
            $this->search = long2ip($ip->ip);
        } else {
            $this->search = null;
            $this->dispatch('alerta', msg: 'No available IPs in this segment', type: 'warning');
        }
    }

    /**
     * Open IP assignment modal.
     */
    public function showModalAsignarIp($ip)
    {
        $this->ipAsignar = $ip;
        $this->dispatch('showModal');
    }

    /**
     * Search equipment by service tag.
     */
    public function buscarEquipo()
    {
        $this->equipos_encontrados = Equipo::where(
            'service_tag',
            'like',
            "%{$this->busEq}%"
        )->get();
    }

    /**
     * Select equipment from search results.
     */
    public function selectEquipo($id)
    {
        $eq = Equipo::find($id);

        $this->busEq = "{$eq->service_tag} [{$eq->relTipoEquipo->nombre}]";
        $this->idEq = $eq->id;
        $this->equipos_encontrados = [];
    }

    /**
     * Assign selected IP to equipment.
     */
    public function asginarIp()
    {
        $eq = Equipo::find($this->idEq);

        if ($eq) {
            // Release previous IP if exists
            if ($eq->direccion_ip) {
                $oldIp = Ip::where('ip', $eq->direccion_ip)->first();
                if ($oldIp) {
                    $oldIp->en_uso = false;
                    $oldIp->save();
                }
            }

            // Assign new IP to equipment
            $eq->direccion_ip = ip2long($this->ipAsignar);
            $eq->save();

            // Mark IP as in use
            $ip = Ip::where('ip', ip2long($this->ipAsignar))->first();
            $ip->en_uso = true;
            $ip->save();

            $this->dispatch('alerta', msg: 'IP address assigned successfully', type: 'success');
            $this->dispatch('cerrarModal');
        } else {
            $this->dispatch('alerta', msg: 'Equipment not found', type: 'warning');
        }
    }

    /**
     * Release IP from equipment.
     */
    public function liberarIp($id)
    {
        $ip = Ip::where('ip', ip2long($id))->first();
        $ip->en_uso = false;
        $ip->save();

        $eq = Equipo::where('direccion_ip', $ip->ip)->first();
        if ($eq) {
            $eq->direccion_ip = '';
            $eq->save();
        }

        $this->dispatch('alerta', msg: 'IP address released', type: 'success');
    }
}
