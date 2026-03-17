<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    // Posição → estilo no revezamento medley
    const MEDLEY_ESTILOS = [
        1 => 'Borboleta',
        2 => 'Costas',
        3 => 'Peito',
        4 => 'Livre',
    ];

    protected $fillable = [
        'campeonato_id', 'distancia_id', 'nome', 'modalidade', 'tipo',
        'ordem_execucao', 'status', 'tempo', 'colocacao', 'medalha',
        'rco', 'status_lancamento', 'data_lancamento',
    ];

    protected $casts = [
        'rco'             => 'boolean',
        'data_lancamento' => 'datetime',
    ];

    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class);
    }

    public function distancia(): BelongsTo
    {
        return $this->belongsTo(Distancia::class);
    }

    public function membros(): HasMany
    {
        return $this->hasMany(EquipeAtleta::class)->orderBy('posicao');
    }
}
