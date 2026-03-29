<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clube extends Model
{
    protected $fillable = ['nome', 'slug', 'ativo'];

    public function atletas(): HasMany
    {
        return $this->hasMany(Atleta::class);
    }

    public function campeonatos(): HasMany
    {
        return $this->hasMany(Campeonato::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
