<?php

namespace App\Http\Controllers;

use App\Models\Resultado;
use Illuminate\Http\Request;

class RelatorioBrasileiroController extends Controller
{
    private const CATEGORIAS = [
        'INF1' => 'Infantil 1',
        'INF2' => 'Infantil 2',
        'JUV1' => 'Juvenil 1',
        'JUV2' => 'Juvenil 2',
        'JR1' => 'Junior 1',
        'SENIOR' => 'Sênior',
    ];

    public function index(Request $request)
    {
        $sexo = $request->sexo ?? 'feminino';
        $categoria = $request->categoria ?? 'INF1';

        $indices = config('indices_tecnicos');

        $todosResultados = Resultado::with(['atleta', 'prova', 'distancia', 'campeonato'])
            ->whereNotNull('tempo')
            ->where('tempo', '!=', '')
            ->whereIn('status_lancamento', ['Lançado', 'Confirmado'])
            ->get();

        // Agrupa por atleta + prova + distância + piscina, mantém melhor tempo
        $melhores = $todosResultados
            ->map(function ($resultado) {
                $piscina = $resultado->piscina ?? $resultado->campeonato->piscina ?? '25m';
                $resultado->tempo_cs = $this->tempoParaCentesimos($resultado->tempo);
                $resultado->prova_chave = $this->normalizarProvaChave($resultado->distancia->metragem, $resultado->prova->nome);
                $resultado->piscina_resultado = $piscina;

                return $resultado;
            })
            ->filter(fn ($r) => $r->tempo_cs !== null)
            ->groupBy(fn ($r) => $r->atleta_id . '_' . $r->prova_chave . '_' . $r->piscina_resultado)
            ->map(fn ($grupo) => $grupo->sortBy('tempo_cs')->first());

        // Compara com índices e filtra apenas classificados
        $classificados = $melhores->map(function ($resultado) use ($indices, $sexo, $categoria) {
            $chave = $sexo . '_' . $resultado->piscina_resultado;
            $indicesProva = $indices[$chave][$resultado->prova_chave] ?? null;

            if (!$indicesProva) {
                return null;
            }

            $indiceValor = null;

            // Tenta sufixo _V (verão) e _I (inverno), usa o mais "fácil" (maior valor)
            $chaveVerao = $categoria . '_V';
            $chaveInverno = $categoria . '_I';

            $possiveis = [];
            if (isset($indicesProva[$chaveVerao])) {
                $possiveis[] = $indicesProva[$chaveVerao];
            }
            if (isset($indicesProva[$chaveInverno])) {
                $possiveis[] = $indicesProva[$chaveInverno];
            }
            if (isset($indicesProva[$categoria])) {
                $possiveis[] = $indicesProva[$categoria];
            }

            if (empty($possiveis)) {
                return null;
            }

            // Pega o índice mais "fácil" (maior tempo = mais fácil de atingir)
            $indiceValor = max($possiveis);

            if ($resultado->tempo_cs > $indiceValor) {
                return null; // Não classificado
            }

            $resultado->indice_cs = $indiceValor;
            $resultado->indice_formatado = IndicesController::centesimosParaTempo($indiceValor);

            return $resultado;
        })
            ->filter()
            ->sortBy(fn ($r) => $r->atleta->nome . '_' . $r->prova_chave)
            ->values();

        return view('relatorio-brasileiro.index', [
            'classificados' => $classificados,
            'sexo' => $sexo,
            'categoria' => $categoria,
            'categorias' => self::CATEGORIAS,
        ]);
    }

    private function normalizarProvaChave(string $metragem, string $provaNome): string
    {
        $metragem = preg_replace('/[^0-9]/', '', $metragem);
        $provaNome = mb_strtoupper(trim($provaNome));

        return $metragem . ' ' . $provaNome;
    }

    private function tempoParaCentesimos(string $tempo): ?int
    {
        if (!preg_match('/^(\d{2}):(\d{2})\.(\d{2})$/', $tempo, $m)) {
            return null;
        }

        return (int) $m[1] * 6000 + (int) $m[2] * 100 + (int) $m[3];
    }
}
