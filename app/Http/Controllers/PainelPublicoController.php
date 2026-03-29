<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Clube;
use App\Models\Equipe;
use App\Models\Resultado;
use App\Services\CategoriaEsportiva;
use Illuminate\Http\Request;

class PainelPublicoController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $clube = Clube::where('slug', $slug)->where('ativo', true)->firstOrFail();

        $campeonatos = Campeonato::withoutGlobalScope('clube')
            ->where('clube_id', $clube->id)
            ->orderByDesc('data_inicio')
            ->get();

        $campeonatoId = $request->get('campeonato_id', $campeonatos->first()?->id);
        $campeonato   = $campeonatos->firstWhere('id', $campeonatoId);

        $sexo  = $request->get('sexo') ?: '';
        $busca = trim($request->get('busca', ''));

        $grupos        = collect();
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

            $catOrdem = array_flip(CategoriaEsportiva::todas());

            $grupos = $query->get()
                ->groupBy([
                    fn ($r) => $r->atleta->sexo === 'masculino' ? 'Masculino' : 'Feminino',
                    fn ($r) => CategoriaEsportiva::calcular($r->atleta->data_nascimento),
                    fn ($r) => $r->distancia->metragem . ' ' . $r->prova->nome,
                ])
                ->sortKeys()
                ->map(fn ($porCat) => $porCat
                    ->sortBy(fn ($_, $cat) => $catOrdem[$cat] ?? 99)
                    ->map(fn ($porProva) => $porProva->sortKeys())
                );

            $equipeQuery = Equipe::with(['distancia', 'membros.atleta'])
                ->where('campeonato_id', $campeonato->id)
                ->whereNotNull('tempo')
                ->whereNotNull('colocacao');

            if ($sexo) {
                $equipeQuery->where('modalidade', ucfirst($sexo));
            }

            $gruposEquipes = $equipeQuery->get()
                ->sortBy([
                    fn ($a, $b) => strcmp($a->modalidade, $b->modalidade),
                    fn ($a, $b) => strcmp($a->distancia->metragem . $a->tipo, $b->distancia->metragem . $b->tipo),
                    fn ($a, $b) => $a->colocacao <=> $b->colocacao,
                ])
                ->groupBy([
                    fn ($e) => $e->modalidade,
                    fn ($e) => $e->distancia->metragem . ' Revezamento ' . $e->tipo,
                ]);
        }

        return view('placar.index', compact(
            'clube', 'campeonatos', 'campeonato', 'grupos', 'gruposEquipes', 'sexo', 'busca'
        ));
    }
}
