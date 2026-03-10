<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultado extends Model
{
    protected $fillable = [
        'atleta_id', 'prova_id', 'distancia_id', 'campeonato_id',
        'piscina', 'tempo', 'colocacao', 'medalha',
        'status_lancamento', 'data_lancamento',
    ];

    protected $casts = [
        'data_lancamento' => 'datetime',
    ];

    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class);
    }

    public function atleta(): BelongsTo
    {
        return $this->belongsTo(Atleta::class);
    }

    public function prova(): BelongsTo
    {
        return $this->belongsTo(Prova::class);
    }

    public function distancia(): BelongsTo
    {
        return $this->belongsTo(Distancia::class);
    }
}
