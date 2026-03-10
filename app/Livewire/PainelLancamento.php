<?php

namespace App\Livewire;

use App\Models\Campeonato;
use App\Models\Inscricao;
use App\Models\Resultado;
use Livewire\Component;

class PainelLancamento extends Component
{
    public Campeonato $campeonato;
    public string $filtroProva = '';
    public string $filtroAtleta = '';
    public string $filtroStatus = '';

    public array $tempos = [];
    public array $colocacoes = [];
    public array $feedbacks = [];
    public array $editando = [];

    public function mount(Campeonato $campeonato)
    {
        $this->campeonato = $campeonato;
        $this->carregarDados();
    }

    public function carregarDados()
    {
        $inscricoes = Inscricao::where('campeonato_id', $this->campeonato->id)->get();

        foreach ($inscricoes as $inscricao) {
            $resultado = Resultado::where([
                'atleta_id' => $inscricao->atleta_id,
                'prova_id' => $inscricao->prova_id,
                'distancia_id' => $inscricao->distancia_id,
                'campeonato_id' => $this->campeonato->id,
            ])->first();

            $key = $inscricao->id;
            $this->tempos[$key] = $resultado?->tempo ?? '';
            $this->colocacoes[$key] = (string) ($resultado?->colocacao ?? '');
        }
    }

    public function habilitarEdicao(int $inscricaoId)
    {
        $this->editando[$inscricaoId] = true;
        $this->feedbacks[$inscricaoId] = '';
    }

    public function updated($property)
    {
        if (preg_match('/^(tempos|colocacoes)\.(\d+)$/', $property, $matches)) {
            $inscricaoId = (int) $matches[2];
            $this->salvarResultado($inscricaoId);
        }
    }

    public function salvarResultado(int $inscricaoId)
    {
        $inscricao = Inscricao::with(['atleta', 'prova', 'distancia'])->findOrFail($inscricaoId);

        if ($inscricao->status === 'Pendente') {
            $this->feedbacks[$inscricaoId] = 'erro:Prova ainda não iniciada';
            return;
        }

        $tempo = $this->tempos[$inscricaoId] ?? '';
        $colocacao = $this->colocacoes[$inscricaoId] ?? '';

        if (empty($tempo) && empty($colocacao)) {
            return;
        }

        if (!empty($tempo) && !preg_match('/^\d{2}:\d{2}\.\d{2}$/', $tempo)) {
            $this->feedbacks[$inscricaoId] = 'erro:Formato de tempo inválido (mm:ss.ms)';
            return;
        }

        if (!empty($colocacao) && (!is_numeric($colocacao) || $colocacao < 1)) {
            $this->feedbacks[$inscricaoId] = 'erro:Colocação deve ser um número positivo';
            return;
        }

        $colocacaoInt = (int) $colocacao;
        $medalha = match ($colocacaoInt) {
            1 => 'Ouro',
            2 => 'Prata',
            3 => 'Bronze',
            default => 'Nenhuma',
        };

        $resultado = Resultado::updateOrCreate([
            'atleta_id' => $inscricao->atleta_id,
            'prova_id' => $inscricao->prova_id,
            'distancia_id' => $inscricao->distancia_id,
            'campeonato_id' => $this->campeonato->id,
        ], [
            'piscina' => $this->campeonato->piscina,
            'tempo' => $tempo,
            'colocacao' => $colocacaoInt,
            'medalha' => $medalha,
            'status_lancamento' => 'Lançado',
            'data_lancamento' => now(),
        ]);

        if ($resultado->status_lancamento === 'Confirmado') {
            $this->feedbacks[$inscricaoId] = 'erro:Resultado já confirmado';
            return;
        }

        $this->feedbacks[$inscricaoId] = 'salvo';
        unset($this->editando[$inscricaoId]);

        $this->verificarProvaFinalizada($inscricao);
    }

    public function iniciarProva(string $chaveProva)
    {
        [$provaId, $distanciaId] = explode('-', $chaveProva);

        Inscricao::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id' => $provaId,
            'distancia_id' => $distanciaId,
        ])->where('status', 'Pendente')
          ->update(['status' => 'Em andamento']);
    }

    public function confirmarProva(string $chaveProva)
    {
        [$provaId, $distanciaId] = explode('-', $chaveProva);

        Resultado::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id' => $provaId,
            'distancia_id' => $distanciaId,
        ])->where('status_lancamento', 'Lançado')
          ->update(['status_lancamento' => 'Confirmado']);

        Inscricao::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id' => $provaId,
            'distancia_id' => $distanciaId,
        ])->update(['status' => 'Finalizada']);
    }

    private function verificarProvaFinalizada(Inscricao $inscricao)
    {
        $totalInscritos = Inscricao::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id' => $inscricao->prova_id,
            'distancia_id' => $inscricao->distancia_id,
        ])->count();

        $totalLancados = Resultado::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id' => $inscricao->prova_id,
            'distancia_id' => $inscricao->distancia_id,
        ])->count();

        if ($totalLancados >= $totalInscritos) {
            Inscricao::where([
                'campeonato_id' => $this->campeonato->id,
                'prova_id' => $inscricao->prova_id,
                'distancia_id' => $inscricao->distancia_id,
            ])->update(['status' => 'Finalizada']);
        }
    }

    public function getProvasAgrupadas()
    {
        $query = Inscricao::with(['atleta', 'prova', 'distancia'])
            ->where('campeonato_id', $this->campeonato->id)
            ->orderBy('ordem_execucao');

        if ($this->filtroStatus) {
            $query->where('status', $this->filtroStatus);
        }

        $inscricoes = $query->get();

        if ($this->filtroProva) {
            $inscricoes = $inscricoes->filter(fn($i) =>
                str_contains(mb_strtolower($i->prova->nome), mb_strtolower($this->filtroProva))
            );
        }

        if ($this->filtroAtleta) {
            $inscricoes = $inscricoes->filter(fn($i) =>
                str_contains(mb_strtolower($i->atleta->nome), mb_strtolower($this->filtroAtleta))
            );
        }

        return $inscricoes->groupBy(fn($i) => $i->prova_id . '-' . $i->distancia_id);
    }

    public function render()
    {
        $provasAgrupadas = $this->getProvasAgrupadas();

        return view('livewire.painel-lancamento', [
            'provasAgrupadas' => $provasAgrupadas,
        ]);
    }
}
