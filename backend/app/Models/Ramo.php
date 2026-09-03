<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ramo extends Model
{
    protected $table = 'ramos';

    protected $fillable = [
        'nome',
        'codigo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function seguros()
    {
        return $this->hasMany(Seguro::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
