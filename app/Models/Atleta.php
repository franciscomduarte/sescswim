<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atleta extends Model
{
    protected $fillable = ['nome', 'data_nascimento', 'codigo_federacao', 'sexo'];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
        ];
    }

    public function inscricoes(): HasMany
    {
        return $this->hasMany(Inscricao::class);
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class);
    }
}
