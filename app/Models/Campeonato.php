<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campeonato extends Model
{
    protected $fillable = ['nome', 'data_inicio', 'data_fim', 'piscina'];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function inscricoes(): HasMany
    {
        return $this->hasMany(Inscricao::class);
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class);
    }

    public function equipes(): HasMany
    {
        return $this->hasMany(\App\Models\Equipe::class);
    }

    public function premiacoes(): HasMany
    {
        return $this->hasMany(\App\Models\Premiacao::class);
    }
}
