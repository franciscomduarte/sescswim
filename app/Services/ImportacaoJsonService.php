<?php

namespace App\Services;

use App\Models\Atleta;
use App\Models\Campeonato;
use App\Models\Distancia;
use App\Models\Inscricao;
use App\Models\Prova;
use App\Models\Resultado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportacaoJsonService
{
    public function importar(string $jsonContent, int $campeonatoId, string $piscina): array
    {
        $dados = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'sucesso' => false,
                'erro' => 'JSON inválido: ' . json_last_error_msg(),
                'total' => 0,
                'importados' => 0,
                'ignorados' => 0,
                'erros' => [],
            ];
        }

        $campeonato = Campeonato::findOrFail($campeonatoId);

        $total = count($dados);
        $importados = 0;
        $ignorados = 0;
        $erros = [];

        DB::beginTransaction();

        try {
            $ordemExecucao = 1;
            $provasProcessadas = [];

            foreach ($dados as $index => $registro) {
                $linha = $index + 1;

                if (!$this->validarRegistro($registro, $linha, $erros)) {
                    $ignorados++;
                    continue;
                }

                $nomeAtleta = trim($registro['atleta']);
                $nomeProva = trim($registro['prova']);
                $metragem = trim($registro['distancia']);
                $tempo = isset($registro['tempo']) && $registro['tempo'] !== null ? trim((string) $registro['tempo']) : null;
                $colocacao = isset($registro['colocacao']) && $registro['colocacao'] !== null && $registro['colocacao'] !== '' ? (int) $registro['colocacao'] : null;
                $medalha = isset($registro['medalha']) && $registro['medalha'] !== null ? trim((string) $registro['medalha']) : 'Nenhuma';

                if (!in_array($medalha, ['Ouro', 'Prata', 'Bronze', 'Nenhuma'])) {
                    $medalha = $colocacao !== null ? $this->calcularMedalha($colocacao) : 'Nenhuma';
                }

                $atleta = Atleta::firstOrCreate(['nome' => $nomeAtleta]);
                $prova = Prova::firstOrCreate(['nome' => $nomeProva]);
                $distancia = Distancia::firstOrCreate(['metragem' => $metragem]);

                $chaveProva = "{$prova->id}-{$distancia->id}";
                if (!isset($provasProcessadas[$chaveProva])) {
                    $provasProcessadas[$chaveProva] = $ordemExecucao++;
                }

                $temResultado = $tempo !== null && $tempo !== '';

                Inscricao::firstOrCreate([
                    'campeonato_id' => $campeonato->id,
                    'atleta_id' => $atleta->id,
                    'prova_id' => $prova->id,
                    'distancia_id' => $distancia->id,
                ], [
                    'ordem_execucao' => $provasProcessadas[$chaveProva],
                    'status' => $temResultado ? 'Finalizada' : 'Pendente',
                ]);

                if ($temResultado) {
                    Resultado::updateOrCreate([
                        'atleta_id' => $atleta->id,
                        'prova_id' => $prova->id,
                        'distancia_id' => $distancia->id,
                        'campeonato_id' => $campeonato->id,
                    ], [
                        'piscina' => $piscina,
                        'tempo' => $tempo,
                        'colocacao' => $colocacao,
                        'medalha' => $medalha,
                        'status_lancamento' => 'Lançado',
                        'data_lancamento' => now(),
                    ]);
                }

                $importados++;
            }

            DB::commit();

            return [
                'sucesso' => true,
                'total' => $total,
                'importados' => $importados,
                'ignorados' => $ignorados,
                'erros' => $erros,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na importação JSON: ' . $e->getMessage());

            return [
                'sucesso' => false,
                'erro' => 'Erro ao processar importação: ' . $e->getMessage(),
                'total' => $total,
                'importados' => $importados,
                'ignorados' => $ignorados,
                'erros' => $erros,
            ];
        }
    }

    private function validarRegistro(array $registro, int $linha, array &$erros): bool
    {
        $valido = true;

        if (empty($registro['atleta'])) {
            $erros[] = "Linha {$linha}: Nome do atleta vazio";
            $valido = false;
        }

        if (empty($registro['prova'])) {
            $erros[] = "Linha {$linha}: Nome da prova vazio";
            $valido = false;
        }

        if (empty($registro['distancia'])) {
            $erros[] = "Linha {$linha}: Distância vazia";
            $valido = false;
        }

        if (!empty($registro['tempo']) && !preg_match('/^\d{2}:\d{2}\.\d{2}$/', $registro['tempo'])) {
            $erros[] = "Linha {$linha}: Formato de tempo inválido ({$registro['tempo']}). Esperado: mm:ss.ms";
            $valido = false;
        }

        if (isset($registro['colocacao']) && $registro['colocacao'] !== null && $registro['colocacao'] !== '' && !is_numeric($registro['colocacao'])) {
            $erros[] = "Linha {$linha}: Colocação inválida ({$registro['colocacao']})";
            $valido = false;
        }

        return $valido;
    }

    private function calcularMedalha(int $colocacao): string
    {
        return match ($colocacao) {
            1 => 'Ouro',
            2 => 'Prata',
            3 => 'Bronze',
            default => 'Nenhuma',
        };
    }
}
