<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seguimiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'ticket',
        'usuario',
        'notas',
        'file',
        'print',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'print' => 'boolean',
    ];

    /**
     * Relación con el usuario que hizo el comentario
     */
    public function userComment(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario');
    }
}
