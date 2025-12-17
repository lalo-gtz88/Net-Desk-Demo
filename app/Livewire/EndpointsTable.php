<?php

namespace App\Livewire;

use App\Models\Endpoint;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class EndpointsTable extends Component
{
    /**
     * Search query synced with URL.
     */
    #[Url(as: 'q', history: true)]
    public string $buscar = '';

    /**
     * Render endpoints table with basic search.
     */
    public function render()
    {
        $endpoints = Endpoint::where('nombre', 'like', "%{$this->buscar}%")
            // Convert stored integer IP to readable format for search
            ->orWhere(DB::raw('INET_NTOA(ip)'), 'like', "%{$this->buscar}%")
            ->orWhere('ubicacion', 'like', "%{$this->buscar}%")
            ->orderBy('ip')
            ->get();

        return view('livewire.endpoints-table', compact('endpoints'));
    }
}
