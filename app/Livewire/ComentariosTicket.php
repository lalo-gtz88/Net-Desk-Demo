<?php

namespace App\Livewire;

use App\Events\CambiosTicket;
use App\Models\Seguimiento;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithFileUploads;

class ComentariosTicket extends Component
{
    use WithFileUploads;

    /**
     * Reactive ticket identifier.
     * Used to keep the component in sync with external updates.
     */
    #[Reactive]
    public $ticketID;

    /** Current ticket instance */
    public $ticket;

    /** Ticket instance used to track changes */
    public $ticketNew;

    /** Editable fields */
    public $tema;
    public $descripcion;

    /** Comment input */
    public $mensaje = '';

    /** Temporary uploaded files */
    public $attachments = [];

    /**
     * Livewire listeners.
     */
    protected $listeners = [
        'ticket-actualizado' => 'ticketActualizado',
    ];

    /**
     * Render component view and keep ticket data updated.
     */
    public function render()
    {
        $this->ticket = $this->obtenerDatos();
        $this->tema = $this->ticket->tema;
        $this->descripcion = strip_tags($this->ticket->descripcion);

        return view('livewire.comentarios-ticket');
    }

    /**
     * Retrieve the current ticket from database.
     */
    protected function obtenerDatos()
    {
        return Ticket::find($this->ticketID);
    }

    /**
     * Main save handler.
     * Persists comments, status changes and attachments.
     */
    public function guardar($status = null)
    {
        // Save user comment (if any)
        $this->guardarComentario();

        // Update ticket status when provided
        $this->guardarStatus($status);

        // Store uploaded attachments
        $this->guardarAdjuntos();

        // Scroll UI to latest comment
        $this->dispatch('setScroll');

        // Reset rich text editor
        $this->dispatch('limpiarSummerNote');
    }

    /**
     * Persist a new comment linked to the ticket.
     */
    protected function guardarComentario()
    {
        if ($this->mensaje === '') {
            return;
        }

        $seguimiento = new Seguimiento();
        $seguimiento->notas = $this->mensaje;
        $seguimiento->ticket = $this->ticketID;
        $seguimiento->usuario = Auth::user()->id;
        $seguimiento->print = 1;
        $seguimiento->save();

        $this->reset('mensaje');
        $this->dispatch('limpiarMensaje');
    }

    /**
     * Update ticket status and dispatch change tracking event.
     */
    protected function guardarStatus($status = null)
    {
        if (!$status) {
            return;
        }

        $this->ticketNew = $this->obtenerDatos();
        $this->ticketNew->status = $status;
        $this->ticketNew->save();

        if ($this->ticket->status !== $this->ticketNew->status) {
            $this->dispatch(
                'alerta',
                type: 'success',
                msg: 'Status ha cambiado a ' . $this->ticketNew->status
            );

            // Dispatch event using replicated models to preserve original state
            CambiosTicket::dispatch(
                $this->ticket->replicate(),
                $this->ticketNew->replicate()
            );
        }
    }

    /**
     * Store uploaded attachments and link them as ticket comments.
     */
    protected function guardarAdjuntos()
    {
        if (count($this->attachments) === 0) {
            return;
        }

        $this->validate([
            'attachments.*' => 'nullable|mimes:jpg,png,pdf',
        ]);

        foreach ($this->attachments as $item) {
            if (!$item) {
                continue;
            }

            $filename = $item->store('public/documents');
            $basename = explode('/', $filename)[2];
            $path = asset('storage/documents') . '/' . $basename;

            $seguimiento = new Seguimiento();
            $seguimiento->notas =
                "<a href=\"{$path}\" target=\"_blank\">
                    <img src=\"{$path}\" class=\"imgAttachment\" alt=\"attachment\" />
                 </a>
                 <span class=\"deleteAttachment\" title=\"Eliminar\">
                    <i class=\"fa fa-times-circle text-danger\"></i>
                 </span>";
            $seguimiento->ticket = $this->ticketID;
            $seguimiento->file = $basename;
            $seguimiento->usuario = Auth::user()->id;
            $seguimiento->save();
        }

        // Reset temporary uploads
        $this->reset('attachments');
    }

    /**
     * Remove a temporary attachment before saving.
     */
    public function deleteAttachment($index)
    {
        unset($this->attachments[$index]);
    }

    /**
     * Delete stored attachment and its related comment.
     */
    #[On('borrarArchivo')]
    public function borrarComentarioArchivo($id)
    {
        $seguimiento = Seguimiento::find($id);

        // Remove physical file
        Storage::delete("public/documents/{$seguimiento->file}");

        // Remove database record
        $seguimiento->delete();
    }

    /**
     * Update ticket subject.
     */
    public function actualizarTema()
    {
        $this->ticket->tema = $this->tema;
        $this->ticket->save();

        $this->dispatch('alerta', type: 'success', msg: 'Cambios guardados!');
    }

    /**
     * Update ticket description.
     */
    public function actualizarDescripcion()
    {
        $this->ticket->descripcion = $this->descripcion;
        $this->ticket->save();

        $this->dispatch('alerta', type: 'success', msg: 'Cambios guardados!');
    }

    /**
     * Cancel description edit and restore state.
     */
    public function cancelEditarDesc()
    {
        $this->reset('descripcion');
        $this->dispatch('cancelEditarDesc');
    }

    /**
     * Refresh component when ticket updates externally.
     */
    public function ticketActualizado()
    {
        $this->dispatch('$refresh');
        $this->dispatch('setScroll');
    }
}
