<?php

namespace App\Livewire;

use App\Models\Atleta;
use App\Models\Campeonato;
use App\Models\CampeonatoProva;
use App\Models\Distancia;
use App\Models\Equipe;
use App\Models\Inscricao;
use App\Models\Prova;
use App\Models\Resultado;
use Livewire\Component;

class PainelLancamento extends Component
{
    public Campeonato $campeonato;
    public string $filtroProva = '';
    public string $filtroAtleta = '';
    public string $filtroStatus = '';

    // Provas individuais
    public array $tempos = [];
    public array $colocacoes = [];
    public array $rcos = [];
    public array $feedbacks = [];
    public array $editando = [];

    // Formulário de novo atleta — armazena a chave "provaId-distanciaId" do grupo aberto, ou ''
    public string $mostrarFormAtleta = '';
    public string $novoAtletaId      = '';
    public string $novaProvaId       = '';
    public string $novaDistanciaId   = '';
    public string $novoTempo         = '';
    public string $novaColocacao     = '';
    public string $erroNovoAtleta    = '';

    // Formulário de nova prova no campeonato
    public bool   $mostrarFormProva  = false;
    public string $novaProvaFormId   = '';
    public string $novaDistFormId    = '';
    public string $erroNovaProva     = '';

    // Revezamentos
    public array $temposEquipes = [];
    public array $colocacoesEquipes = [];
    public array $rcosEquipes = [];
    public array $feedbacksEquipes = [];
    public array $editandoEquipes = [];

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
                'atleta_id'     => $inscricao->atleta_id,
                'prova_id'      => $inscricao->prova_id,
                'distancia_id'  => $inscricao->distancia_id,
                'campeonato_id' => $this->campeonato->id,
            ])->first();

            $key = $inscricao->id;
            $this->tempos[$key]    = $resultado?->tempo ?? '';
            $this->colocacoes[$key] = (string) ($resultado?->colocacao ?? '');
            $this->rcos[$key]      = (bool) ($resultado?->rco ?? false);
        }

        $equipes = Equipe::where('campeonato_id', $this->campeonato->id)->get();

        foreach ($equipes as $equipe) {
            $id = $equipe->id;
            $this->temposEquipes[$id]    = $equipe->tempo ?? '';
            $this->colocacoesEquipes[$id] = (string) ($equipe->colocacao ?? '');
            $this->rcosEquipes[$id]      = (bool) ($equipe->rco ?? false);
        }
    }

    // ── Provas individuais ──────────────────────────────────────────

    public function habilitarEdicao(int $inscricaoId)
    {
        $this->editando[$inscricaoId] = true;
        $this->feedbacks[$inscricaoId] = '';
    }

    public function updated($property)
    {
        if (preg_match('/^(tempos|colocacoes|rcos)\.(\d+)$/', $property, $matches)) {
            $this->salvarResultado((int) $matches[2]);
        }

        if (preg_match('/^(temposEquipes|colocacoesEquipes|rcosEquipes)\.(\d+)$/', $property, $matches)) {
            $this->salvarEquipe((int) $matches[2]);
        }
    }

    public function salvarResultado(int $inscricaoId)
    {
        $inscricao = Inscricao::with(['atleta', 'prova', 'distancia'])->findOrFail($inscricaoId);

        if ($inscricao->status === 'Pendente') {
            // Verifica se outros atletas da mesma prova já foram iniciados
            $grupoIniciado = Inscricao::where([
                'campeonato_id' => $this->campeonato->id,
                'prova_id'      => $inscricao->prova_id,
                'distancia_id'  => $inscricao->distancia_id,
            ])->whereIn('status', ['Em andamento', 'Finalizada'])->exists();

            if ($grupoIniciado) {
                // Promove automaticamente a inscrição para acompanhar o grupo
                $inscricao->update(['status' => 'Em andamento']);
            } else {
                $this->feedbacks[$inscricaoId] = 'erro:Prova ainda não iniciada';
                return;
            }
        }

        $tempo    = $this->tempos[$inscricaoId] ?? '';
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
            'atleta_id'     => $inscricao->atleta_id,
            'prova_id'      => $inscricao->prova_id,
            'distancia_id'  => $inscricao->distancia_id,
            'campeonato_id' => $this->campeonato->id,
        ], [
            'piscina'           => $this->campeonato->piscina,
            'tempo'             => $tempo,
            'rco'               => (bool) ($this->rcos[$inscricaoId] ?? false),
            'colocacao'         => $colocacaoInt,
            'medalha'           => $medalha,
            'status_lancamento' => 'Lançado',
            'data_lancamento'   => now(),
        ]);

        if ($resultado->status_lancamento === 'Confirmado') {
            $this->feedbacks[$inscricaoId] = 'erro:Resultado já confirmado';
            return;
        }

        $this->feedbacks[$inscricaoId] = 'salvo';
        unset($this->editando[$inscricaoId]);

        $this->verificarProvaFinalizada($inscricao);
    }

    public function abrirFormAtleta(string $chaveProva)
    {
        [$provaId, $distanciaId] = explode('-', $chaveProva);

        // Fecha qualquer form já aberto e limpa campos
        $this->mostrarFormAtleta = $chaveProva;
        $this->novoAtletaId      = '';
        $this->novaProvaId       = $provaId;
        $this->novaDistanciaId   = $distanciaId;
        $this->novoTempo         = '';
        $this->novaColocacao     = '';
        $this->erroNovoAtleta    = '';
    }

    public function adicionarAtleta()
    {
        $this->erroNovoAtleta = '';

        if (!$this->novoAtletaId || !$this->novaProvaId || !$this->novaDistanciaId) {
            $this->erroNovoAtleta = 'Atleta, prova e distância são obrigatórios.';
            return;
        }

        if (!empty($this->novoTempo) && !preg_match('/^\d{2}:\d{2}\.\d{2}$/', $this->novoTempo)) {
            $this->erroNovoAtleta = 'Tempo inválido. Use o formato mm:ss.ms (ex: 00:28.47)';
            return;
        }

        if (!empty($this->novaColocacao) && (!is_numeric($this->novaColocacao) || $this->novaColocacao < 1)) {
            $this->erroNovoAtleta = 'Colocação deve ser um número positivo.';
            return;
        }

        $chave = [
            'campeonato_id' => $this->campeonato->id,
            'atleta_id'     => $this->novoAtletaId,
            'prova_id'      => $this->novaProvaId,
            'distancia_id'  => $this->novaDistanciaId,
        ];

        if (Inscricao::where($chave)->exists()) {
            $this->erroNovoAtleta = 'Este atleta já está inscrito nessa prova/distância.';
            return;
        }

        $temDados = !empty($this->novoTempo) || !empty($this->novaColocacao);

        // Herda o status do grupo da prova (pode já estar Em andamento ou Finalizada)
        $statusGrupo = Inscricao::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $this->novaProvaId,
            'distancia_id'  => $this->novaDistanciaId,
        ])->whereIn('status', ['Em andamento', 'Finalizada'])->value('status');

        $statusInscricao = $statusGrupo ?? ($temDados ? 'Em andamento' : 'Pendente');
        $ordemMax        = Inscricao::where('campeonato_id', $this->campeonato->id)->max('ordem_execucao') ?? 0;

        $inscricao = Inscricao::create(array_merge($chave, [
            'ordem_execucao' => $ordemMax + 1,
            'status'         => $statusInscricao,
        ]));

        $colocacaoInt = (int) $this->novaColocacao;
        $medalha      = match ($colocacaoInt) {
            1 => 'Ouro', 2 => 'Prata', 3 => 'Bronze', default => 'Nenhuma',
        };

        Resultado::create(array_merge($chave, [
            'piscina'           => $this->campeonato->piscina,
            'tempo'             => $this->novoTempo ?: null,
            'colocacao'         => $colocacaoInt ?: null,
            'medalha'           => $colocacaoInt ? $medalha : 'Nenhuma',
            'rco'               => false,
            'status_lancamento' => $temDados ? 'Lançado' : 'Pendente',
            'data_lancamento'   => $temDados ? now() : null,
        ]));

        // Registra no estado reativo do componente
        $key                    = $inscricao->id;
        $this->tempos[$key]     = $this->novoTempo;
        $this->colocacoes[$key] = $this->novaColocacao;
        $this->rcos[$key]       = false;
        $this->feedbacks[$key]  = $temDados ? 'salvo' : '';

        // Fecha o formulário
        $this->novoAtletaId      = '';
        $this->novaProvaId       = '';
        $this->novaDistanciaId   = '';
        $this->novoTempo         = '';
        $this->novaColocacao     = '';
        $this->mostrarFormAtleta = '';
    }

    public function adicionarProva()
    {
        $this->erroNovaProva = '';

        if (!$this->novaProvaFormId || !$this->novaDistFormId) {
            $this->erroNovaProva = 'Selecione a prova e a distância.';
            return;
        }

        $jaExiste = CampeonatoProva::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $this->novaProvaFormId,
            'distancia_id'  => $this->novaDistFormId,
        ])->exists();

        if ($jaExiste) {
            $this->erroNovaProva = 'Esta prova/distância já foi adicionada ao campeonato.';
            return;
        }

        CampeonatoProva::create([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $this->novaProvaFormId,
            'distancia_id'  => $this->novaDistFormId,
        ]);

        $this->novaProvaFormId  = '';
        $this->novaDistFormId   = '';
        $this->mostrarFormProva = false;
    }

    public function iniciarProva(string $chaveProva)
    {
        [$provaId, $distanciaId] = explode('-', $chaveProva);

        Inscricao::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $provaId,
            'distancia_id'  => $distanciaId,
        ])->where('status', 'Pendente')
          ->update(['status' => 'Em andamento']);
    }

    public function confirmarProva(string $chaveProva)
    {
        [$provaId, $distanciaId] = explode('-', $chaveProva);

        Resultado::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $provaId,
            'distancia_id'  => $distanciaId,
        ])->where('status_lancamento', 'Lançado')
          ->update(['status_lancamento' => 'Confirmado']);

        Inscricao::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $provaId,
            'distancia_id'  => $distanciaId,
        ])->update(['status' => 'Finalizada']);
    }

    private function verificarProvaFinalizada(Inscricao $inscricao)
    {
        $totalInscritos = Inscricao::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $inscricao->prova_id,
            'distancia_id'  => $inscricao->distancia_id,
        ])->count();

        $totalLancados = Resultado::where([
            'campeonato_id' => $this->campeonato->id,
            'prova_id'      => $inscricao->prova_id,
            'distancia_id'  => $inscricao->distancia_id,
        ])->count();

        if ($totalLancados >= $totalInscritos) {
            Inscricao::where([
                'campeonato_id' => $this->campeonato->id,
                'prova_id'      => $inscricao->prova_id,
                'distancia_id'  => $inscricao->distancia_id,
            ])->update(['status' => 'Finalizada']);
        }
    }

    // ── Revezamentos ────────────────────────────────────────────────

    public function habilitarEdicaoEquipe(int $equipeId)
    {
        $this->editandoEquipes[$equipeId] = true;
        $this->feedbacksEquipes[$equipeId] = '';
    }

    public function salvarEquipe(int $equipeId)
    {
        $equipe = Equipe::findOrFail($equipeId);

        if ($equipe->status === 'Pendente') {
            $this->feedbacksEquipes[$equipeId] = 'erro:Revezamento ainda não iniciado';
            return;
        }

        if ($equipe->status_lancamento === 'Confirmado') {
            $this->feedbacksEquipes[$equipeId] = 'erro:Resultado já confirmado';
            return;
        }

        $tempo    = $this->temposEquipes[$equipeId] ?? '';
        $colocacao = $this->colocacoesEquipes[$equipeId] ?? '';

        if (empty($tempo) && empty($colocacao)) {
            return;
        }

        if (!empty($tempo) && !preg_match('/^\d{2}:\d{2}\.\d{2}$/', $tempo)) {
            $this->feedbacksEquipes[$equipeId] = 'erro:Formato de tempo inválido (mm:ss.ms)';
            return;
        }

        if (!empty($colocacao) && (!is_numeric($colocacao) || $colocacao < 1)) {
            $this->feedbacksEquipes[$equipeId] = 'erro:Colocação deve ser um número positivo';
            return;
        }

        $colocacaoInt = (int) $colocacao;
        $medalha = match ($colocacaoInt) {
            1 => 'Ouro',
            2 => 'Prata',
            3 => 'Bronze',
            default => 'Nenhuma',
        };

        $equipe->update([
            'tempo'             => $tempo,
            'rco'               => (bool) ($this->rcosEquipes[$equipeId] ?? false),
            'colocacao'         => $colocacaoInt,
            'medalha'           => $medalha,
            'status_lancamento' => 'Lançado',
            'data_lancamento'   => now(),
        ]);

        $this->feedbacksEquipes[$equipeId] = 'salvo';
        unset($this->editandoEquipes[$equipeId]);
    }

    public function iniciarEquipe(int $equipeId)
    {
        Equipe::where('id', $equipeId)->where('status', 'Pendente')
            ->update(['status' => 'Em andamento']);
    }

    public function confirmarEquipe(int $equipeId)
    {
        Equipe::where('id', $equipeId)->where('status_lancamento', 'Lançado')
            ->update(['status_lancamento' => 'Confirmado', 'status' => 'Finalizada']);
    }

    // ── Queries ─────────────────────────────────────────────────────

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

        $agrupadas = $inscricoes->groupBy(fn($i) => $i->prova_id . '-' . $i->distancia_id);

        // Inclui provas do campeonato que ainda não têm atletas inscritos
        if (!$this->filtroStatus && !$this->filtroAtleta) {
            $cprovs = CampeonatoProva::with(['prova', 'distancia'])
                ->where('campeonato_id', $this->campeonato->id)
                ->get();

            foreach ($cprovs as $cp) {
                $chave = $cp->prova_id . '-' . $cp->distancia_id;
                if (!$agrupadas->has($chave)) {
                    $agrupadas[$chave] = collect();
                }
            }
        }

        return $agrupadas;
    }

    public function getEquipes()
    {
        $query = Equipe::with(['distancia', 'membros.atleta'])
            ->where('campeonato_id', $this->campeonato->id)
            ->orderBy('ordem_execucao');

        if ($this->filtroStatus) {
            $query->where('status', $this->filtroStatus);
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.painel-lancamento', [
            'provasAgrupadas'  => $this->getProvasAgrupadas(),
            'equipes'          => $this->getEquipes(),
            'atletas'          => Atleta::orderBy('nome')->get(),
            'provas'           => Prova::orderBy('nome')->get(),
            'distancias'       => Distancia::orderBy('metragem')->get(),
            'campeonatoProvas' => CampeonatoProva::with(['prova', 'distancia'])
                                    ->where('campeonato_id', $this->campeonato->id)
                                    ->get(),
        ]);
    }
}
