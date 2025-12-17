<?php

namespace App\Livewire;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class ModalAsignarIp extends Component
{
    /**
     * IP address to be assigned.
     * Reactive property updated from parent component.
     */
    #[Reactive]
    public $ipToAssigned;

    /**
     * Render IP assignment modal.
     */
    public function render()
    {
        return view('livewire.modal-asignar-ip');
    }
}
