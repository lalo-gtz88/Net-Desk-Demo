<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'tema',
        'descripcion',
        'telefono',
        'departamento',
        'ip',
        'asignado',
        'edificio',
        'usuario_red',
        'autoriza',
        'creador',
        'prioridad',
        'categoria',
        'status',
        'usuario',
        'reporta',
        'fecha_atencion',
    ];

    /**
     * Technician assigned to the ticket
     */
    public function tecnico(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'asignado');
    }

    /**
     * User who created the ticket
     */
    public function userCreador(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'creador');
    }

    /**
     * Ticket activity log / comments
     */
    public function seguimientos(): HasMany
    {
        return $this->hasMany(Seguimiento::class, 'ticket', 'id');
    }

    /**
     * Dynamic color based on ticket priority
     */
    public function getColorPrioridadAttribute(): string
    {
        return match ($this->prioridad) {
            'Baja'  => 'success',
            'Media' => 'warning',
            'Alta'  => 'danger',
            default => 'secondary',
        };
    }
}
