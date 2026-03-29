<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Clube;
use App\Models\Resultado;
use Illuminate\Http\Request;

class EvolutionController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $clube = Clube::where('slug', $slug)->where('ativo', true)->firstOrFail();

        $atletas = Atleta::withoutGlobalScope('clube')
            ->where('clube_id', $clube->id)
            ->orderBy('nome')
            ->get(['id', 'nome', 'sexo', 'data_nascimento']);

        $atletaId = $request->get('atleta_id');
        $atleta   = $atletaId ? $atletas->firstWhere('id', $atletaId) : null;

        $evolucao = collect();
        $resumo   = [];

        if ($atleta) {
            $resultados = Resultado::with(['prova', 'distancia', 'campeonato'])
                ->where('atleta_id', $atleta->id)
                ->whereNotNull('tempo')
                ->whereIn('status_lancamento', ['Lançado', 'Confirmado'])
                ->get();

            $evolucao = $resultados
                ->groupBy(fn ($r) => $r->prova_id . '-' . $r->distancia_id)
                ->map(function ($items) {
                    $sorted    = $items->sortBy(fn ($r) => $r->campeonato->data_inicio->timestamp)->values();
                    $temposSec = $sorted->map(fn ($r) => $this->parseTime($r->tempo));

                    $melhorSec    = $temposSec->min();
                    $melhorResult = $sorted->sortBy(fn ($r) => $this->parseTime($r->tempo))->first();
                    $delta        = $sorted->count() > 1
                        ? round($temposSec->first() - $temposSec->last(), 2)
                        : null;

                    return [
                        'label'       => $sorted->first()->distancia->metragem . ' ' . $sorted->first()->prova->nome,
                        'resultados'  => $sorted,
                        'melhor'      => $melhorResult,
                        'melhor_sec'  => $melhorSec,
                        'delta'       => $delta,
                        'labels_json' => $sorted->map(fn ($r) => $r->campeonato->nome)->values()->toJson(),
                        'times_json'  => $temposSec->values()->toJson(),
                    ];
                })
                ->sortBy('label')
                ->values();

            $resumo = [
                'campeonatos'  => $resultados->pluck('campeonato_id')->unique()->count(),
                'provas'       => $evolucao->count(),
                'melhor_coloc' => $resultados->whereNotNull('colocacao')->min('colocacao'),
                'medalhas'     => $resultados->whereIn('medalha', ['Ouro', 'Prata', 'Bronze'])->count(),
                'ouros'        => $resultados->where('medalha', 'Ouro')->count(),
                'pratas'       => $resultados->where('medalha', 'Prata')->count(),
                'bronzes'      => $resultados->where('medalha', 'Bronze')->count(),
            ];
        }

        return view('evolucao.index', compact('clube', 'atletas', 'atleta', 'evolucao', 'resumo'));
    }

    private function parseTime(string $tempo): float
    {
        if (!str_contains($tempo, ':')) {
            return (float) $tempo;
        }
        [$min, $rest] = explode(':', $tempo);
        return (int) $min * 60 + (float) $rest;
    }
}
