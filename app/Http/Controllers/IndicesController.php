<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Campeonato;
use App\Models\Resultado;
use Illuminate\Http\Request;

class IndicesController extends Controller
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
        $atletas = Atleta::orderBy('nome')->get();
        $campeonatos = Campeonato::orderByDesc('data_inicio')->get();

        $filtros = [
            'atleta_id' => $request->atleta_id,
            'campeonato_id' => $request->campeonato_id,
            'sexo' => $request->sexo ?? 'feminino',
            'categoria' => $request->categoria ?? 'INF1',
        ];

        $resultados = collect();

        if (!empty($filtros['atleta_id'])) {
            $query = Resultado::with(['atleta', 'prova', 'distancia', 'campeonato'])
                ->where('atleta_id', $filtros['atleta_id'])
                ->whereNotNull('tempo')
                ->where('tempo', '!=', '');

            if (!empty($filtros['campeonato_id'])) {
                $query->where('campeonato_id', $filtros['campeonato_id']);
            }

            $resultados = $query->orderBy('campeonato_id')->get();

            $indices = config('indices_tecnicos');
            $categoria = $filtros['categoria'];

            $resultados = $resultados->map(function ($resultado) use ($indices, $filtros, $categoria) {
                $piscina = $resultado->piscina ?? $resultado->campeonato->piscina ?? '25m';
                $chave = $filtros['sexo'] . '_' . $piscina;
                $provaChave = $this->normalizarProvaChave($resultado->distancia->metragem, $resultado->prova->nome);

                $tempoCs = $this->tempoParaCentesimos($resultado->tempo);
                $resultado->tempo_cs = $tempoCs;
                $resultado->prova_chave = $provaChave;
                $resultado->piscina_resultado = $piscina;

                $indicesProva = $indices[$chave][$provaChave] ?? null;

                if ($indicesProva && $tempoCs) {
                    // Índice de Inverno (1º semestre) - sufixo _V no config
                    $chaveInverno = $categoria . '_V';
                    if (isset($indicesProva[$chaveInverno])) {
                        $resultado->indice_inverno = $indicesProva[$chaveInverno];
                        $resultado->diff_inverno = $tempoCs - $indicesProva[$chaveInverno];
                    } elseif (isset($indicesProva[$categoria])) {
                        $resultado->indice_inverno = $indicesProva[$categoria];
                        $resultado->diff_inverno = $tempoCs - $indicesProva[$categoria];
                    }

                    // Índice de Verão (2º semestre) - sufixo _I no config
                    $chaveVerao = $categoria . '_I';
                    if (isset($indicesProva[$chaveVerao])) {
                        $resultado->indice_verao = $indicesProva[$chaveVerao];
                        $resultado->diff_verao = $tempoCs - $indicesProva[$chaveVerao];
                    } elseif (isset($indicesProva[$categoria])) {
                        $resultado->indice_verao = $indicesProva[$categoria];
                        $resultado->diff_verao = $tempoCs - $indicesProva[$categoria];
                    }
                }

                return $resultado;
            });
        }

        return view('indices.index', [
            'atletas' => $atletas,
            'campeonatos' => $campeonatos,
            'filtros' => $filtros,
            'categorias' => self::CATEGORIAS,
            'resultados' => $resultados,
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

    public static function centesimosParaTempo(int $cs): string
    {
        $minutos = intdiv($cs, 6000);
        $resto = $cs % 6000;
        $segundos = intdiv($resto, 100);
        $centesimos = $resto % 100;

        return sprintf('%02d:%02d.%02d', $minutos, $segundos, $centesimos);
    }

    public static function formatarDiferenca(int $diff): string
    {
        $sinal = $diff <= 0 ? '-' : '+';
        $abs = abs($diff);

        return $sinal . self::centesimosParaTempo($abs);
    }
}
