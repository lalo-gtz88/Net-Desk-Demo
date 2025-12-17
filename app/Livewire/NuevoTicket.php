<?php

namespace App\Livewire;

use App\Jobs\SendTelegramNotification;
use App\Models\AlertasUsers;
use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Edificio;
use App\Models\Equipo;
use App\Models\Seguimiento;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class NuevoTicket extends Component
{
    use WithFileUploads;

    /**
     * Optional reference ticket ID (used when cloning an existing ticket).
     */
    public $uniqueId;

    /** Ticket core fields */
    public $tema = '';
    public $descripcion = '';
    public $prioridad = 'Baja';
    public $quien_reporta = '';
    public $telefono = '';
    public $edificio = '';
    public $departamento = '';
    public $ip = '';
    public $usuario_red = '';
    public $asignado = '';
    public $categoria = '';
    public $autoriza = '';
    public $fecha_de_atencion;
    public $unidad;

    /** Network / equipment helpers */
    public $direccionIp;
    public $equipos = [];
    public $equipo;

    /** Attachments */
    public $attachment = [];

    /**
     * Initialize component state.
     * If uniqueId exists, preload ticket data as a template.
     */
    public function mount()
    {
        if (!$this->uniqueId) {
            return;
        }

        $ticket = Ticket::findOrFail($this->uniqueId);

        $this->fill([
            'tema'          => $ticket->tema,
            'descripcion'   => $ticket->descripcion,
            'quien_reporta' => $ticket->reporta,
            'telefono'      => $ticket->telefono,
            'edificio'      => $ticket->edificio,
            'departamento'  => $ticket->departamento,
            'ip'            => $ticket->ip,
            'autoriza'      => $ticket->autoriza,
            'categoria'     => $ticket->categoria,
            'asignado'      => $ticket->asignado,
            'prioridad'     => $ticket->prioridad,
        ]);

        // Sync rich text editor content
        $this->dispatch('setEditor', contenido: $this->descripcion);
    }

    /**
     * Render new ticket form.
     */
    public function render()
    {
        return view('livewire.nuevo-ticket', [
            'tecnicos'     => $this->getTecnicos(),
            'categorias'   => $this->getCategorias(),
            'departamentos'=> $this->getDepartamentos(),
            'edificios'    => $this->getEdificios(),
        ]);
    }

    /** Catalog helpers */

    protected function getCategorias()
    {
        return Categoria::where('active', 1)->orderBy('name')->get();
    }

    protected function getEdificios()
    {
        return Edificio::where('active', 1)->orderBy('nombre')->get();
    }

    protected function getDepartamentos()
    {
        return Departamento::where('active', 1)->orderBy('nombre')->get();
    }

    protected function getTecnicos()
    {
        return User::where('activo', 1)
            ->where('tecnico', 1)
            ->orderBy('name')
            ->get();
    }

    /**
     * Persist a new ticket and related attachments.
     */
    public function guardar()
    {
        if ($this->direccionIp) {
            $this->ip = "172.16.{$this->direccionIp}";
        }

        $this->validate([
            'tema'            => 'required|string|max:100',
            'descripcion'     => 'required|string',
            'telefono'        => 'required|string',
            'attachment.*'    => 'nullable|mimes:jpg,png,pdf',
            'ip'              => 'nullable|ip',
        ]);

        $ticket = Ticket::create([
            'tema'          => mb_strtoupper($this->tema),
            'descripcion'   => $this->descripcion,
            'reporta'       => $this->quien_reporta,
            'asignado'      => $this->asignado ?: 0,
            'creador'       => Auth::id(),
            'prioridad'     => $this->prioridad,
            'categoria'     => $this->categoria,
            'usuario_red'   => $this->usuario_red,
            'status'        => 'Abierto',
            'telefono'      => $this->telefono,
            'departamento'  => $this->departamento,
            'edificio'      => $this->edificio,
            'ip'            => $this->direccionIp,
            'autoriza'      => $this->autoriza,
            'usuario'       => Auth::id(),
            'fecha_atencion'=> $this->fecha_de_atencion,
            'unidad'        => $this->unidad,
        ]);

        // Store attachments as ticket follow-ups
        $this->attachFiles($this->attachment, $ticket->id);

        // Async notification
        SendTelegramNotification::dispatch($ticket->id);

        $this->dispatch('alerta', msg: 'Ticket created successfully!', type: 'success');

        // Reset form but keep reference ID if exists
        $this->resetExcept('uniqueId');
        $this->dispatch('limpiarDescripcion');
    }

    /**
     * Remove a pending attachment before save.
     */
    public function delFile(int $index)
    {
        $this->attachment[$index] = null;
    }

    /**
     * Persist uploaded files as ticket comments.
     */
    protected function attachFiles(array $attachments, int $ticketId): void
    {
        foreach ($attachments as $file) {
            if (!$file) {
                continue;
            }

            $storedPath = $file->store('public/documents');
            $filename = basename($storedPath);
            $publicPath = asset("storage/documents/{$filename}");

            Seguimiento::create([
                'notas'   => "<a href=\"{$publicPath}\" target=\"_blank\">
                                <img src=\"{$publicPath}\" class=\"imgAttachment\" alt=\"attachment\" />
                              </a>",
                'ticket'  => $ticketId,
                'file'    => $filename,
                'usuario' => Auth::id(),
            ]);
        }
    }

    /**
     * Reset component state via external event.
     */
    #[On('limpiar')]
    public function limpiar()
    {
        $this->reset();
    }

    /**
     * Load equipment suggestions based on partial IP.
     */
    public function updatedDireccionIp()
    {
        $this->reset('equipo');

        if (Str::length($this->direccionIp) < 3) {
            $this->equipos = [];
            return;
        }

        $this->equipos = Equipo::whereRaw(
            'INET_NTOA(direccion_ip) LIKE ?',
            ["%172.16.{$this->direccionIp}%"]
        )->get();
    }

    /**
     * Select an equipment from suggestion list.
     */
    public function seleccionarEquipo(int $id)
    {
        $this->equipo = Equipo::find($id);
        $this->equipos = [];
    }
}
