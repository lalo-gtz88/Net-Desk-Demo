<?php

namespace App\Livewire;

use App\Models\Edificio;
use App\Models\Endpoint;
use Exception;
use Livewire\Component;

class FrmEndPoint extends Component
{
    /** Endpoint identifier (edit mode) */
    public $endpoint_id = null;

    /** Endpoint fields */
    public $nombre;
    public $ip;
    public $tipo;
    public $fails_count = 0;
    public $enviar_alerta = 1;
    public $notas;
    public $ubicacion = '';

    /** Catalog data */
    public $cat_ubicaciones = [];

    /**
     * Validation rules.
     */
    protected function rules()
    {
        return [
            'nombre'        => 'required|string|max:255',
            'ip'            => 'required|string|max:255',
            'tipo'          => 'nullable|string',
            'ubicacion'     => 'nullable|string',
            'enviar_alerta' => 'required|boolean',
            'notas'         => 'nullable|string',
        ];
    }

    /**
     * Initialize component.
     * If an ID is provided, load endpoint data for editing.
     */
    public function mount($id = null)
    {
        // Load active locations (buildings)
        $this->cat_ubicaciones = Edificio::where('active', 1)
            ->orderBy('nombre')
            ->get();

        if ($id) {
            $this->endpoint_id = $id;
            $endpoint = Endpoint::findOrFail($id);

            $this->nombre        = $endpoint->nombre;
            $this->ip            = long2ip($endpoint->ip);
            $this->tipo          = $endpoint->tipo;
            $this->ubicacion     = $endpoint->ubicacion;
            $this->enviar_alerta = $endpoint->enviar_alerta;
            $this->notas         = $endpoint->notas;
        }
    }

    /**
     * Render endpoint form.
     */
    public function render()
    {
        return view('livewire.frm-end-point');
    }

    /**
     * Create or update endpoint record.
     */
    public function save()
    {
        try {
            $this->validate();

            Endpoint::updateOrCreate(
                ['id' => $this->endpoint_id],
                [
                    'nombre'        => $this->nombre,
                    'ip'            => ip2long($this->ip),
                    'tipo'          => $this->tipo,
                    'enviar_alerta' => $this->enviar_alerta,
                    'ubicacion'     => $this->ubicacion,
                    'notas'         => $this->notas,
                ]
            );

            $this->dispatch('alerta', msg: 'Endpoint saved successfully', type: 'success');
        } catch (Exception $ex) {
            // Generic error handling for demo purposes
            $this->dispatch('alerta', msg: 'Error: ' . $ex->getMessage(), type: 'error');
        }
    }

    /**
     * Delete endpoint record.
     */
    public function eliminar()
    {
        $endpoint = Endpoint::findOrFail($this->endpoint_id);
        $endpoint->delete();

        $this->redirect(route('endpoint.list'));
    }
}
