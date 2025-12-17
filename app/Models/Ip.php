<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ip extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function relSegmento(): BelongsTo
    {
        return $this->belongsTo(Segmento::class, 'segmento_id');
    }

    public function equipo(): HasOne
    {
        return $this->hasOne(Equipo::class, 'direccion_ip', 'ip');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Attribute casting
     */
    protected $casts = [
        'en_uso' => 'boolean',
        'ip'     => 'integer',
    ];

    /**
     * Accessor: usage status icon
     */
    public function getIconUsoAttribute(): string
    {
        return $this->en_uso ? '❌' : '✅';
    }
}
