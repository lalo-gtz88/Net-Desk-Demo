<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endpoint extends Model
{
    use HasFactory;

    protected $table = 'endpoints';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'nombre',
        'ip',
        'tipo',
        'monitoreable',
        'status',
        'last_status',
        'fails_count',
        'enviar_alerta',
        'notas',
        'ubicacion',
    ];

    /**
     * Attribute casting for consistency
     */
    protected $casts = [
        'monitoreable'  => 'boolean',
        'enviar_alerta' => 'boolean',
        'fails_count'   => 'integer',
        'ip'            => 'integer',
    ];
}
