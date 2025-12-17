<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Segmento extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'nombre',
        'subred_inicio',
        'subred_fin',
        'mascara',
        'hosts_disponibles',
        'edificio_id',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'subred_inicio'     => 'integer',
        'subred_fin'        => 'integer',
        'hosts_disponibles' => 'integer',
    ];

    /**
     * Relationships
     */
    public function relEdificio(): BelongsTo
    {
        return $this->belongsTo(Edificio::class, 'edificio_id');
    }
}
