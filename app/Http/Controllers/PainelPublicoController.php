<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Equipe;
use App\Models\Resultado;
use App\Services\CategoriaEsportiva;
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

            // Agrupamento: Sexo → Categoria → Prova
            $grupos = $query->get()
                ->groupBy([
                    fn ($r) => $r->atleta->sexo === 'masculino' ? 'Masculino' : 'Feminino',
                    fn ($r) => CategoriaEsportiva::calcular($r->atleta->data_nascimento),
                    fn ($r) => $r->distancia->metragem . ' ' . $r->prova->nome,
                ])
                ->sortKeys()   // Feminino antes de Masculino
                ->map(fn ($porCat) => $porCat
                    ->sortBy(fn ($_, $cat) => $catOrdem[$cat] ?? 99)   // ordem etária
                    ->map(fn ($porProva) => $porProva->sortKeys())      // provas em ordem alfabética
                );

            // Revezamentos: agrupados por Sexo → Prova
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
            'campeonatos', 'campeonato', 'grupos', 'gruposEquipes', 'sexo', 'busca'
        ));
    }
}
