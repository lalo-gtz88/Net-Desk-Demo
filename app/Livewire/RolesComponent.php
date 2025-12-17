<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class RolesComponent extends Component
{
    /**
     * User being edited.
     */
    public User $user;

    /**
     * All available permissions in the system.
     */
    public $permisos = [];

    /**
     * Permission names currently assigned to the user.
     */
    public $permisosAsignados = [];

    /**
     * Flag to optionally lazy-load data (kept for scalability).
     */
    public bool $readyToLoad = false;

    /**
     * Initialize component with user and permissions.
     */
    public function mount(int $id): void
    {
        $this->user = User::findOrFail($id);

        // Load all permissions (Spatie)
        $this->permisos = Permission::all();

        // Preload assigned permissions as plain names
        if ($this->user->permissions->isNotEmpty()) {
            $this->permisosAsignados = $this->user->getPermissionNames()->toArray();
        }
    }

    /**
     * Render permissions management view.
     */
    public function render()
    {
        return view('livewire.roles-component');
    }

    /**
     * Persist permission changes for the user.
     * All previous permissions are revoked and replaced.
     */
    public function store(): void
    {
        // Remove existing permissions to keep state clean and explicit
        if ($this->user->permissions->isNotEmpty()) {
            foreach ($this->user->getPermissionNames() as $permission) {
                $this->user->revokePermissionTo($permission);
            }
        }

        // Assign selected permissions
        if (!empty($this->permisosAsignados)) {
            $this->user->givePermissionTo($this->permisosAsignados);
        }

        $this->dispatch('alerta', msg: 'Permissions updated successfully!', type: 'success');
    }
}
