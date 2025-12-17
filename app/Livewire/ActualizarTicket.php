<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

/**
 * Demo Livewire Component
 *
 * Sanitized version of a production ticket update component.
 * All database writes, external services, and infrastructure integrations
 * have been removed or simulated for public portfolio purposes.
 */
class ActualizarTicket extends Component
{
    use WithFileUploads;

    // --- Reactive properties ---
    #[Reactive]
    public $ticketID;

    public $ticket;
    public $ticketNew;

    // --- Ticket fields ---
    public $tema = '';
    public $descripcion = '';
    public $prioridad = 'Baja';
    public $quien_reporta = '';
    public $telefono = '';
    public $edificio = '';
    public $departamento = '';
    public $ip = '';
    public $asignado = '';
    public $asignadoOld = '';
    public $categoria = '';
    public $autoriza = '';
    public $fecha_de_atencion;

    // --- Flags / options ---
    public bool $correoNuevo = false;
    public ?string $email = null;
    public bool $siga = false;
    public bool $turnos = false;
    public bool $nav = false;
    public bool $impresora = false;
    public bool $smar = false;
    public bool $eset = false;
    public bool $office = false;

    // --- Comments & attachments (demo only) ---
    public $mensaje = '';
    public $attachments = [];

    // --- Equipment demo data ---
    public $equipo;

    public function mount()
    {
        $this->loadDemoData();

        // Demo event dispatch (no real lookup)
        $this->dispatch('buscarEquipo', ip: $this->ip);
    }

    public function render()
    {
        // Demo static collections
        $tecnicos = $this->getTecnicos();
        $categorias = $this->getCategorias();
        $edificios = $this->getEdificios();
        $departamentos = $this->getDepartamentos();

        $this->getEquipo();

        return view('livewire.actualizar-ticket', compact(
            'tecnicos',
            'categorias',
            'departamentos',
            'edificios'
        ));
    }

    /**
     * Load demo ticket data.
     * Replaces real database access.
     */
    public function loadDemoData()
    {
        $this->ticket = (object) [
            'id' => $this->ticketID,
            'reporta' => 'Demo User',
            'telefono' => '555-0000',
            'edificio' => 'Main Office',
            'departamento' => 'IT',
            'ip' => '192.168.0.10',
            'autoriza' => 'Demo Manager',
            'categoria' => 'Support',
            'asignado' => 1,
            'prioridad' => 'Media',
            'fecha_atencion' => now(),
            'correo_nuevo' => false,
            'email' => null,
            'status' => 'Abierto',
        ];

        $this->quien_reporta = $this->ticket->reporta;
        $this->telefono = $this->ticket->telefono;
        $this->edificio = $this->ticket->edificio;
        $this->departamento = $this->ticket->departamento;
        $this->ip = $this->ticket->ip;
        $this->autoriza = $this->ticket->autoriza;
        $this->categoria = $this->ticket->categoria;
        $this->asignado = $this->ticket->asignado;
        $this->asignadoOld = $this->ticket->asignado;
        $this->prioridad = $this->ticket->prioridad;
        $this->fecha_de_atencion = $this->ticket->fecha_atencion;
    }

    public function updatedIp()
    {
        // Demo-only event
        $this->dispatch('buscarEquipo', ip: $this->ip);
    }

    // --- Demo static data providers ---

    public function getCategorias()
    {
        return collect([
            ['name' => 'Support'],
            ['name' => 'Networking'],
            ['name' => 'Hardware'],
        ]);
    }

    public function getEdificios()
    {
        return collect([
            ['nombre' => 'Main Office'],
            ['nombre' => 'Branch Office'],
        ]);
    }

    public function getDepartamentos()
    {
        return collect([
            ['nombre' => 'IT'],
            ['nombre' => 'Administration'],
        ]);
    }

    public function getTecnicos()
    {
        return collect([
            ['name' => 'John Doe'],
            ['name' => 'Jane Smith'],
        ]);
    }

    /**
     * Simulated save operation.
     * No database writes or external notifications.
     */
    public function guardar($status = null)
    {
        $this->dispatch('alerta', msg: 'Demo: ticket changes simulated', type: 'info');
        $this->dispatch('ticket-actualizado');
        $this->dispatch('setScroll');
        $this->dispatch('limpiarSummerNote');
    }

    public function limpiarEquipo()
    {
        $this->dispatch('limpiarEquipo');
    }

    public function updatedEmail()
    {
        $this->validateOnly('email');
    }

    public function guardarComentario()
    {
        if ($this->mensaje === '') {
            return;
        }

        // Demo: comment not persisted
        $this->reset('mensaje');
        $this->dispatch('limpiarMensaje');
    }

    public function guardarStatus($status = null)
    {
        if ($status) {
            $this->dispatch('alerta', type: 'success', msg: "Demo: status changed to {$status}");
        }
    }

    public function guardarAdjuntos()
    {
        // Demo only: attachments are not stored
        $this->reset('attachments');
    }

    public function deleteAttachment($index)
    {
        unset($this->attachments[$index]);
    }

    #[On('borrarArchivo')]
    public function borrarComentarioArchivo($id)
    {
        // Demo only: no file deletion
    }

    public function actualizarTema()
    {
        $this->dispatch('alerta', type: 'success', msg: 'Demo: topic updated');
    }

    public function actualizarDescripcion()
    {
        $this->dispatch('alerta', type: 'success', msg: 'Demo: description updated');
    }

    public function cancelEditarDesc()
    {
        $this->reset('descripcion');
        $this->dispatch('cancelEditarDesc');
    }

    public function ticketActualizado()
    {
        $this->dispatch('$refresh');
        $this->dispatch('setScroll');
    }

    protected function rules()
    {
        return [
            'email' => $this->correoNuevo
                ? 'required|email'
                : 'nullable',
        ];
    }

    public function guardarCorreo($status = null)
    {
        // Demo only: email change simulated
    }

    /**
     * Simulated equipment lookup.
     * Production network queries removed.
     */
    public function getEquipo()
    {
        $this->equipo = [
            'hostname' => 'demo-device',
            'ip' => '192.168.0.10',
            'status' => 'online',
        ];
    }
}
