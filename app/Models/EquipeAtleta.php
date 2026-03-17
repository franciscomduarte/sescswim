<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipeAtleta extends Model
{
    protected $table = 'equipe_atletas';

    protected $fillable = ['equipe_id', 'atleta_id', 'posicao'];

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class);
    }

    public function atleta(): BelongsTo
    {
        return $this->belongsTo(Atleta::class);
    }
}
