<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguradora extends Model
{
    protected $table = 'seguradoras';

    protected $fillable = [
        'nome',
        'codigo',
        'ativa',
    ];

    protected $casts = [
        'ativa' => 'boolean',
    ];

    public function seguros()
    {
        return $this->hasMany(Seguro::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativa', true);
    }
}
