<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campeonato extends Model
{
    protected $fillable = ['nome', 'data_inicio', 'data_fim', 'piscina', 'clube_id'];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
    ];

    protected static function booted(): void
    {
        // Leitura: filtra pelo clube do usuário logado (exceto super_admin)
        static::addGlobalScope('clube', function (Builder $builder) {
            if (auth()->check() && ! auth()->user()->is_super_admin) {
                $builder->where('campeonatos.clube_id', auth()->user()->clube_id);
            }
        });

        // Escrita: seta clube_id automaticamente ao criar
        static::creating(function (Campeonato $campeonato) {
            if (! isset($campeonato->clube_id) && auth()->check()) {
                $campeonato->clube_id = auth()->user()->clube_id;
            }
        });
    }

    public function clube(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Clube::class);
    }

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
