<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Premiacao extends Model
{
    const TIPOS = ['Eficiência Técnica', 'Índice Técnico'];

    protected $table = 'premiacoes';

    protected $fillable = ['campeonato_id', 'atleta_id', 'tipo', 'observacao'];

    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class);
    }

    public function atleta(): BelongsTo
    {
        return $this->belongsTo(Atleta::class);
    }

    /** Retorna true quando é premiação de equipe (sem atleta específico) */
    public function isEquipe(): bool
    {
        return is_null($this->atleta_id);
    }
}
