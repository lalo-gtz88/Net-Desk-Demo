<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Accesor: devuelve el nombre del departamento en mayúsculas
     */
    public function getNameAttribute($value): string
    {
        return strtoupper($value);
    }
}
