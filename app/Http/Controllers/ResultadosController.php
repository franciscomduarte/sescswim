<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Campeonato;
use App\Models\Equipe;
use App\Models\Premiacao;
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

        // KPI - Medalhas totais (individuais)
        $queryMedalhas = Resultado::query();
        $this->aplicarFiltros($queryMedalhas, $filtros);

        $medalhas = [
            'ouro' => (clone $queryMedalhas)->where('medalha', 'Ouro')->count(),
            'prata' => (clone $queryMedalhas)->where('medalha', 'Prata')->count(),
            'bronze' => (clone $queryMedalhas)->where('medalha', 'Bronze')->count(),
        ];

        // KPI - Medalhas de revezamento (1 por atleta da equipe)
        $equipesComMedalha = $this->queryEquipes($filtros)
            ->whereNotNull('medalha')
            ->with('membros')
            ->get();

        $medalhasRevezamento = [
            'ouro'   => $equipesComMedalha->where('medalha', 'Ouro')->sum(fn($e) => $e->membros->count()),
            'prata'  => $equipesComMedalha->where('medalha', 'Prata')->sum(fn($e) => $e->membros->count()),
            'bronze' => $equipesComMedalha->where('medalha', 'Bronze')->sum(fn($e) => $e->membros->count()),
        ];

        // Relay medals per athlete, per piscina and per campeonato
        $relayMedalsPorAtleta = [];
        $relayMedalsPorPiscina = [];
        $relayMedalsPorCampeonato = [];
        foreach ($equipesComMedalha as $equipe) {
            $key = strtolower($equipe->medalha); // 'ouro', 'prata', 'bronze'
            $membrosCount = $equipe->membros->count();
            $piscina = $equipe->campeonato->piscina;
            $campId  = $equipe->campeonato_id;

            if (!isset($relayMedalsPorPiscina[$piscina])) {
                $relayMedalsPorPiscina[$piscina] = ['ouro' => 0, 'prata' => 0, 'bronze' => 0];
            }
            $relayMedalsPorPiscina[$piscina][$key] += $membrosCount;

            if (!isset($relayMedalsPorCampeonato[$campId])) {
                $relayMedalsPorCampeonato[$campId] = ['ouro' => 0, 'prata' => 0, 'bronze' => 0];
            }
            $relayMedalsPorCampeonato[$campId][$key] += $membrosCount;

            foreach ($equipe->membros as $membro) {
                $id = $membro->atleta_id;
                if (!isset($relayMedalsPorAtleta[$id])) {
                    $relayMedalsPorAtleta[$id] = ['ouro' => 0, 'prata' => 0, 'bronze' => 0];
                }
                $relayMedalsPorAtleta[$id][$key]++;
            }
        }

        // KPI - Total RCOs individuais
        $queryRcos = Resultado::where('rco', true);
        $this->aplicarFiltros($queryRcos, $filtros);
        $totalRcos = $queryRcos->count();

        // KPI - Desempenho por atleta
        $queryAtletas = Resultado::select(
                'atleta_id',
                DB::raw("COUNT(*) as total_provas"),
                DB::raw("SUM(CASE WHEN medalha = 'Ouro' THEN 1 ELSE 0 END) as ouro"),
                DB::raw("SUM(CASE WHEN medalha = 'Prata' THEN 1 ELSE 0 END) as prata"),
                DB::raw("SUM(CASE WHEN medalha = 'Bronze' THEN 1 ELSE 0 END) as bronze"),
                DB::raw("SUM(CASE WHEN colocacao = 4 THEN 1 ELSE 0 END) as quarto"),
                DB::raw("SUM(CASE WHEN colocacao = 5 THEN 1 ELSE 0 END) as quinto"),
                DB::raw("SUM(CASE WHEN colocacao = 6 THEN 1 ELSE 0 END) as sexto"),
                DB::raw("SUM(CASE WHEN colocacao = 7 THEN 1 ELSE 0 END) as setimo"),
                DB::raw("SUM(CASE WHEN colocacao = 8 THEN 1 ELSE 0 END) as oitavo"),
                DB::raw("SUM(CASE WHEN rco = true THEN 1 ELSE 0 END) as rcos"),
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

        // Revezamentos (até 8º lugar)
        $revezamentos = $this->queryEquipes($filtros)
            ->whereNotNull('colocacao')
            ->where('colocacao', '<=', 8)
            ->orderBy('colocacao')
            ->get()
            ->groupBy(fn($e) => $e->campeonato->nome);

        // Recordes individuais (RCO)
        $queryRecordesInd = Resultado::with(['atleta', 'prova', 'distancia', 'campeonato'])
            ->where('rco', true);
        $this->aplicarFiltros($queryRecordesInd, $filtros);
        $recordesIndividuais = $queryRecordesInd
            ->orderBy('campeonato_id')
            ->get()
            ->groupBy(fn($r) => $r->campeonato->nome);

        // Recordes de revezamento (RCO)
        $recordesRevezamento = $this->queryEquipes($filtros)
            ->where('rco', true)
            ->get()
            ->groupBy(fn($e) => $e->campeonato->nome);

        // Premiações especiais
        $queryPremiacoes = Campeonato::with([
                'premiacoes' => fn($q) => $q->with('atleta')->orderBy('tipo'),
            ])
            ->whereHas('premiacoes');

        if (!empty($filtros['campeonato_id'])) {
            $queryPremiacoes->where('id', $filtros['campeonato_id']);
        }
        if (!empty($filtros['periodo_inicio'])) {
            $queryPremiacoes->where('data_inicio', '>=', $filtros['periodo_inicio']);
        }
        if (!empty($filtros['periodo_fim'])) {
            $queryPremiacoes->where('data_fim', '<=', $filtros['periodo_fim']);
        }
        $premiacoes = $queryPremiacoes->orderByDesc('data_inicio')->get();

        return view('resultados.index', compact(
            'campeonatos', 'atletas', 'filtros', 'medalhas', 'medalhasRevezamento', 'totalRcos',
            'desempenhoAtletas', 'relayMedalsPorAtleta', 'relayMedalsPorPiscina', 'relayMedalsPorCampeonato',
            'resultadosCompeticao', 'comparacaoPiscina',
            'revezamentos', 'recordesIndividuais', 'recordesRevezamento', 'premiacoes'
        ));
    }

    private function queryEquipes(array $filtros)
    {
        $query = Equipe::with(['campeonato', 'distancia', 'membros.atleta']);

        if (!empty($filtros['campeonato_id'])) {
            $query->where('campeonato_id', $filtros['campeonato_id']);
        }
        if (!empty($filtros['periodo_inicio'])) {
            $query->whereHas('campeonato', fn($q) => $q->where('data_inicio', '>=', $filtros['periodo_inicio']));
        }
        if (!empty($filtros['periodo_fim'])) {
            $query->whereHas('campeonato', fn($q) => $q->where('data_fim', '<=', $filtros['periodo_fim']));
        }

        return $query;
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
