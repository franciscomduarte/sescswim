<?php

namespace App\Models;

use App\Services\CategoriaEsportiva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atleta extends Model
{
    protected $fillable = ['nome', 'data_nascimento', 'codigo_federacao', 'sexo', 'clube_id'];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Leitura: filtra pelo clube do usuário logado (exceto super_admin)
        static::addGlobalScope('clube', function (Builder $builder) {
            if (auth()->check() && ! auth()->user()->is_super_admin) {
                $builder->where('atletas.clube_id', auth()->user()->clube_id);
            }
        });

        // Escrita: seta clube_id automaticamente ao criar
        static::creating(function (Atleta $atleta) {
            if (! isset($atleta->clube_id) && auth()->check()) {
                $atleta->clube_id = auth()->user()->clube_id;
            }
        });
    }

    public function clube(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Clube::class);
    }

    public function categoria(): string
    {
        return CategoriaEsportiva::calcular($this->data_nascimento);
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
