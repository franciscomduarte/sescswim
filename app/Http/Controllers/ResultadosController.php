<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Campeonato;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultadosController extends Controller
{
    public function index(Request $request)
    {
        $campeonatos = Campeonato::orderByDesc('data_inicio')->get();
        $atletas = Atleta::orderBy('nome')->get();

        $filtros = [
            'campeonato_id' => $request->campeonato_id,
            'atleta_id' => $request->atleta_id,
            'periodo_inicio' => $request->periodo_inicio,
            'periodo_fim' => $request->periodo_fim,
        ];

        // KPI - Medalhas totais
        $queryMedalhas = Resultado::query();
        $this->aplicarFiltros($queryMedalhas, $filtros);

        $medalhas = [
            'ouro' => (clone $queryMedalhas)->where('medalha', 'Ouro')->count(),
            'prata' => (clone $queryMedalhas)->where('medalha', 'Prata')->count(),
            'bronze' => (clone $queryMedalhas)->where('medalha', 'Bronze')->count(),
        ];

        // KPI - Desempenho por atleta
        $queryAtletas = Resultado::select(
                'atleta_id',
                DB::raw("COUNT(*) as total_provas"),
                DB::raw("SUM(CASE WHEN medalha = 'Ouro' THEN 1 ELSE 0 END) as ouro"),
                DB::raw("SUM(CASE WHEN medalha = 'Prata' THEN 1 ELSE 0 END) as prata"),
                DB::raw("SUM(CASE WHEN medalha = 'Bronze' THEN 1 ELSE 0 END) as bronze"),
            )
            ->groupBy('atleta_id')
            ->with('atleta');

        $this->aplicarFiltros($queryAtletas, $filtros);
        $desempenhoAtletas = $queryAtletas->orderByDesc('ouro')->orderByDesc('prata')->orderByDesc('bronze')->get();

        // KPI - Resultados por competição
        $queryCompeticoes = Resultado::select(
                'campeonato_id',
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN medalha = 'Ouro' THEN 1 ELSE 0 END) as ouro"),
                DB::raw("SUM(CASE WHEN medalha = 'Prata' THEN 1 ELSE 0 END) as prata"),
                DB::raw("SUM(CASE WHEN medalha = 'Bronze' THEN 1 ELSE 0 END) as bronze"),
            )
            ->groupBy('campeonato_id')
            ->with('campeonato');

        $this->aplicarFiltros($queryCompeticoes, $filtros);
        $resultadosCompeticao = $queryCompeticoes->get();

        // KPI - Comparação 25m vs 50m
        $comparacaoPiscina = Resultado::select(
                'piscina',
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN medalha = 'Ouro' THEN 1 ELSE 0 END) as ouro"),
                DB::raw("SUM(CASE WHEN medalha = 'Prata' THEN 1 ELSE 0 END) as prata"),
                DB::raw("SUM(CASE WHEN medalha = 'Bronze' THEN 1 ELSE 0 END) as bronze"),
            )
            ->groupBy('piscina')
            ->get();

        return view('resultados.index', compact(
            'campeonatos', 'atletas', 'filtros', 'medalhas',
            'desempenhoAtletas', 'resultadosCompeticao', 'comparacaoPiscina'
        ));
    }

    private function aplicarFiltros($query, array $filtros): void
    {
        if (!empty($filtros['campeonato_id'])) {
            $query->where('campeonato_id', $filtros['campeonato_id']);
        }

        if (!empty($filtros['atleta_id'])) {
            $query->where('atleta_id', $filtros['atleta_id']);
        }

        if (!empty($filtros['periodo_inicio'])) {
            $query->whereHas('campeonato', fn($q) => $q->where('data_inicio', '>=', $filtros['periodo_inicio']));
        }

        if (!empty($filtros['periodo_fim'])) {
            $query->whereHas('campeonato', fn($q) => $q->where('data_fim', '<=', $filtros['periodo_fim']));
        }
    }
}
