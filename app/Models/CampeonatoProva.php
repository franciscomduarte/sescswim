<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampeonatoProva extends Model
{
    protected $table = 'campeonato_provas';

    protected $fillable = ['campeonato_id', 'prova_id', 'distancia_id'];

    public function prova(): BelongsTo
    {
        return $this->belongsTo(Prova::class);
    }

    public function distancia(): BelongsTo
    {
        return $this->belongsTo(Distancia::class);
    }

    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class);
    }
}
