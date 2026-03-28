<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Equipe;
use App\Models\Resultado;
use Illuminate\Http\Request;

class PainelPublicoController extends Controller
{
    public function index(Request $request)
    {
        $campeonatos = Campeonato::orderByDesc('data_inicio')->get();

        $campeonatoId = $request->get('campeonato_id', $campeonatos->first()?->id);
        $campeonato   = $campeonatos->firstWhere('id', $campeonatoId);

        $sexo  = $request->get('sexo') ?: '';  // '' = todos, 'masculino' ou 'feminino'
        $busca = trim($request->get('busca', ''));

        $grupos       = collect();
        $gruposEquipes = collect();

        if ($campeonato) {
            $query = Resultado::with(['atleta', 'prova', 'distancia'])
                ->where('campeonato_id', $campeonato->id)
                ->whereNotNull('tempo')
                ->whereNotNull('colocacao');

            if ($sexo) {
                $query->whereHas('atleta', fn ($q) => $q->where('sexo', $sexo));
            }

            if ($busca) {
                $query->whereHas('atleta', fn ($q) => $q->where('nome', 'like', "%{$busca}%"));
            }

            $resultados = $query->get()->sortBy([
                fn ($a, $b) => strcmp(
                    $a->distancia->metragem . $a->prova->nome . $a->atleta->sexo,
                    $b->distancia->metragem . $b->prova->nome . $b->atleta->sexo
                ),
                fn ($a, $b) => $a->colocacao <=> $b->colocacao,
            ]);

            $grupos = $resultados->groupBy(function ($r) {
                $modalidade = $r->atleta->sexo === 'masculino' ? 'Masculino' : 'Feminino';
                return $r->distancia->metragem . ' ' . $r->prova->nome . '|' . $modalidade;
            });

            $equipeQuery = Equipe::with(['distancia', 'membros.atleta'])
                ->where('campeonato_id', $campeonato->id)
                ->whereNotNull('tempo')
                ->whereNotNull('colocacao');

            if ($sexo) {
                $equipeQuery->where('modalidade', ucfirst($sexo)); // 'masculino' → 'Masculino'
            }

            $gruposEquipes = $equipeQuery->get()
                ->sortBy([
                    fn ($a, $b) => strcmp($a->distancia->metragem . $a->tipo . $a->modalidade, $b->distancia->metragem . $b->tipo . $b->modalidade),
                    fn ($a, $b) => $a->colocacao <=> $b->colocacao,
                ])
                ->groupBy(fn ($e) => $e->distancia->metragem . ' Revezamento ' . $e->tipo . '|' . $e->modalidade);
        }

        return view('placar.index', compact(
            'campeonatos', 'campeonato', 'grupos', 'gruposEquipes', 'sexo', 'busca'
        ));
    }
}
