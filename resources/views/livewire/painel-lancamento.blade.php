<div wire:poll.5s>
    {{-- Cabeçalho --}}
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $campeonato->nome }}</h1>
                <p class="text-sm text-gray-500">
                    {{ $campeonato->data_inicio->format('d/m/Y') }} - {{ $campeonato->data_fim->format('d/m/Y') }}
                    <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $campeonato->piscina }}</span>
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" wire:model.live.debounce.300ms="filtroProva" placeholder="Filtrar prova..." class="text-sm border rounded-md px-3 py-2 w-full sm:w-40">
                <input type="text" wire:model.live.debounce.300ms="filtroAtleta" placeholder="Filtrar atleta..." class="text-sm border rounded-md px-3 py-2 w-full sm:w-40">
                <select wire:model.live="filtroStatus" class="text-sm border rounded-md px-3 py-2 w-full sm:w-40">
                    <option value="">Todos os status</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Em andamento">Em andamento</option>
                    <option value="Finalizada">Finalizada</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Botão + formulário nova prova --}}
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="flex items-center justify-between mb-0">
            <span class="text-sm font-semibold text-gray-700">Provas do campeonato</span>
            <button wire:click="$set('mostrarFormProva', true)"
                    class="text-xs bg-green-600 text-white px-3 py-1.5 rounded hover:bg-green-700 font-semibold transition flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Nova Prova
            </button>
        </div>

        @if($mostrarFormProva)
            <div class="mt-3 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Adicionar prova ao campeonato</p>

                @if($erroNovaProva)
                    <div class="mb-2 bg-red-50 border border-red-200 text-red-700 text-xs rounded px-3 py-2">
                        {{ $erroNovaProva }}
                    </div>
                @endif

                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs text-gray-500 mb-1">Prova <span class="text-red-500">*</span></label>
                        <select wire:model="novaProvaFormId"
                                class="w-full border-gray-300 rounded text-sm p-1.5 border bg-white focus:ring-2 focus:ring-green-500">
                            <option value="">Selecione…</option>
                            @foreach($provas as $p)
                                <option value="{{ $p->id }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-28">
                        <label class="block text-xs text-gray-500 mb-1">Distância <span class="text-red-500">*</span></label>
                        <select wire:model="novaDistFormId"
                                class="w-full border-gray-300 rounded text-sm p-1.5 border bg-white focus:ring-2 focus:ring-green-500">
                            <option value="">Selecione…</option>
                            @foreach($distancias as $d)
                                <option value="{{ $d->id }}">{{ $d->metragem }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="adicionarProva"
                                class="bg-green-600 text-white px-4 py-1.5 rounded text-sm font-semibold hover:bg-green-700 transition">
                            Confirmar
                        </button>
                        <button wire:click="$set('mostrarFormProva', false)"
                                class="bg-gray-200 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-300 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Lista de Provas --}}
    @forelse($provasAgrupadas as $chaveProva => $inscricoes)
        @php
            $primeiraInscricao = $inscricoes->first();
            $semAtletas        = $primeiraInscricao === null;

            // Para grupos vazios, busca prova/distância pelo CampeonatoProva
            if ($semAtletas) {
                [$cpProvaId, $cpDistId] = explode('-', $chaveProva);
                $cpRecord = $campeonatoProvas->firstWhere(fn($cp) => $cp->prova_id == $cpProvaId && $cp->distancia_id == $cpDistId);
            }

            $status      = $semAtletas ? 'Pendente' : $primeiraInscricao->status;
            $statusColor = match($status) {
                'Pendente'     => 'bg-gray-100 border-gray-300 text-gray-600',
                'Em andamento' => 'bg-blue-50 border-blue-400 text-blue-700',
                'Finalizada'   => 'bg-green-50 border-green-400 text-green-700',
                default        => 'bg-gray-100 border-gray-300',
            };
            $statusBadge = match($status) {
                'Pendente'     => 'bg-gray-200 text-gray-700',
                'Em andamento' => 'bg-blue-200 text-blue-800',
                'Finalizada'   => 'bg-green-200 text-green-800',
                default        => 'bg-gray-200 text-gray-700',
            };
            $ordem       = $semAtletas ? '—' : str_pad($primeiraInscricao->ordem_execucao, 2, '0', STR_PAD_LEFT);
            $nomeProva   = $semAtletas
                ? ($cpRecord->distancia->metragem . ' ' . $cpRecord->prova->nome)
                : ($primeiraInscricao->distancia->metragem . ' ' . $primeiraInscricao->prova->nome);
        @endphp

        <div class="mb-3 rounded-lg shadow border-l-4 {{ $statusColor }}">
            {{-- Header da prova --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 py-3 bg-white rounded-t-lg">
                <div class="flex items-center gap-2">
                    @if(!$semAtletas)
                        <span class="text-sm font-mono text-gray-400">[{{ $ordem }}]</span>
                    @endif
                    <span class="font-bold text-gray-800">{{ $nomeProva }}</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusBadge }}">{{ $status }}</span>
                    @if($semAtletas)
                        <span class="text-xs text-gray-400 italic">Sem atletas</span>
                    @endif
                </div>
                <div class="flex gap-2 mt-2 sm:mt-0">
                    @if(!$semAtletas && $status === 'Pendente')
                        <button wire:click="iniciarProva('{{ $chaveProva }}')" class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                            Iniciar Prova
                        </button>
                    @elseif(!$semAtletas && $status === 'Em andamento')
                        <button wire:click="confirmarProva('{{ $chaveProva }}')" class="text-xs bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition" onclick="return confirm('Confirmar todos os resultados desta prova?')">
                            Confirmar Prova
                        </button>
                    @endif
                    <button wire:click="abrirFormAtleta('{{ $chaveProva }}')"
                            class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Atleta
                    </button>
                </div>
            </div>

            {{-- Formulário de adicionar atleta nesta prova --}}
            @if($mostrarFormAtleta === $chaveProva)
                <div class="px-4 py-3 bg-emerald-50 border-t border-emerald-200">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wide">
                            Adicionar atleta — {{ $nomeProva }}
                        </p>
                        <button wire:click="$set('mostrarFormAtleta', '')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    @if($erroNovoAtleta)
                        <div class="mb-2 bg-red-50 border border-red-200 text-red-700 text-xs rounded px-3 py-2">
                            {{ $erroNovoAtleta }}
                        </div>
                    @endif

                    <div class="flex flex-wrap items-end gap-3">
                        <div class="flex-1 min-w-48">
                            <label class="block text-xs text-gray-500 mb-1">Atleta <span class="text-red-500">*</span></label>
                            <select wire:model="novoAtletaId"
                                    class="w-full border-gray-300 rounded text-sm p-1.5 border bg-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">Selecione o atleta…</option>
                                @foreach($atletas as $a)
                                    <option value="{{ $a->id }}">{{ $a->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tempo</label>
                            <input type="tel"
                                   wire:model="novoTempo"
                                   oninput="mascaraTempoInput(this)"
                                   placeholder="00:00.00"
                                   maxlength="8"
                                   class="w-28 border-gray-300 rounded text-sm p-1.5 border font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Colocação</label>
                            <input type="tel"
                                   wire:model="novaColocacao"
                                   oninput="this.value=this.value.replace(/\D/g,'').substring(0,2)"
                                   placeholder="#"
                                   maxlength="2"
                                   class="w-16 border-gray-300 rounded text-sm p-1.5 border text-center focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="flex gap-2">
                            <button wire:click="adicionarAtleta"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-1.5 rounded transition">
                                Confirmar
                            </button>
                            <button wire:click="$set('mostrarFormAtleta', '')"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-3 py-1.5 rounded transition">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Atletas da prova --}}
            <div class="divide-y divide-gray-100">
                @foreach($inscricoes->sortBy(fn($i) => $this->colocacoes[$i->id] ?? 999) as $inscricao)
                    @php
                        $key = $inscricao->id;
                        $feedback = $this->feedbacks[$key] ?? null;
                        $isConfirmado = false;

                        $resultado = \App\Models\Resultado::where([
                            'atleta_id' => $inscricao->atleta_id,
                            'prova_id' => $inscricao->prova_id,
                            'distancia_id' => $inscricao->distancia_id,
                            'campeonato_id' => $campeonato->id,
                        ])->first();

                        if ($resultado && $resultado->status_lancamento === 'Confirmado') {
                            $isConfirmado = true;
                        }

                        $medalhaIcon = match($resultado?->medalha ?? '') {
                            'Ouro' => '🥇',
                            'Prata' => '🥈',
                            'Bronze' => '🥉',
                            default => '',
                        };
                    @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 px-4 py-2 bg-white {{ $isConfirmado ? 'opacity-70' : '' }}">
                        {{-- Nome do atleta --}}
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-800 truncate block">
                                {{ $medalhaIcon }} {{ $inscricao->atleta->nome }}
                            </span>
                        </div>

                        {{-- Inputs --}}
                        @php
                            $tempoPreenchido = !empty($this->tempos[$key]);
                            $colocPreenchida = !empty($this->colocacoes[$key]);
                            $jaLancado = $tempoPreenchido && $colocPreenchida;
                            $emEdicao = isset($this->editando[$key]);
                            $bloqueado = $isConfirmado || $status === 'Pendente';
                            $mostrarInputs = !$jaLancado || $emEdicao || $bloqueado;
                        @endphp

                        <div class="flex items-center gap-2">
                            @if($jaLancado && !$emEdicao && !$bloqueado)
                                {{-- Modo leitura: valores fixos + botão editar --}}
                                <span class="w-24 text-sm text-center font-mono text-gray-700 bg-gray-50 border rounded px-2 py-1">{{ $this->tempos[$key] }}</span>
                                <span class="w-16 text-sm text-center text-gray-700 bg-gray-50 border rounded px-2 py-1">{{ $this->colocacoes[$key] }}º</span>
                                @if($this->rcos[$key] ?? false)
                                    <span class="text-xs font-bold text-orange-600 bg-orange-100 border border-orange-300 rounded px-1.5 py-0.5">RCO</span>
                                @endif
                                <button
                                    wire:click="habilitarEdicao({{ $key }})"
                                    class="text-xs text-blue-600 hover:text-blue-800 hover:underline px-1"
                                    title="Editar resultado"
                                >Editar</button>
                            @else
                                {{-- Modo edição / primeiro lançamento --}}
                                <input
                                    type="tel"
                                    id="tempo-{{ $key }}"
                                    data-next="coloc-{{ $key }}"
                                    wire:model.blur="tempos.{{ $key }}"
                                    oninput="mascaraTempoInput(this)"
                                    placeholder="__:__.__"
                                    maxlength="8"
                                    pattern="[0-9]*"
                                    class="w-24 text-sm text-center border rounded px-2 py-1 font-mono {{ $bloqueado ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-blue-500 focus:border-blue-500' }}"
                                    {{ $bloqueado ? 'disabled' : '' }}
                                >
                                <input
                                    type="tel"
                                    id="coloc-{{ $key }}"
                                    wire:model.blur="colocacoes.{{ $key }}"
                                    oninput="mascaraColocacao(this)"
                                    placeholder="#"
                                    maxlength="2"
                                    pattern="[0-9]*"
                                    class="campo-coloc w-16 text-sm text-center border rounded px-2 py-1 {{ $bloqueado ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-blue-500 focus:border-blue-500' }}"
                                    {{ $bloqueado ? 'disabled' : '' }}
                                >
                                <label class="flex items-center gap-1 text-xs text-gray-600 cursor-pointer select-none {{ $bloqueado ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    <input
                                        type="checkbox"
                                        wire:model.live="rcos.{{ $key }}"
                                        class="rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                                        {{ $bloqueado ? 'disabled' : '' }}
                                    >
                                    RCO
                                </label>
                            @endif

                            {{-- Feedback --}}
                            <span class="w-6 text-center">
                                @if($feedback === 'salvo')
                                    <span class="text-green-500" title="Salvo">&#10004;</span>
                                @elseif($feedback && str_starts_with($feedback, 'erro:'))
                                    <span class="text-red-500 cursor-help" title="{{ substr($feedback, 5) }}">&#9888;</span>
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-12 text-gray-500">
            @if($filtroProva || $filtroAtleta || $filtroStatus)
                Nenhuma prova encontrada com os filtros aplicados.
            @else
                Nenhuma prova cadastrada para este campeonato. <a href="{{ route('importacao.index') }}" class="text-blue-600 hover:underline">Importar resultados</a>
            @endif
        </div>
    @endforelse

    {{-- Revezamentos --}}
    @if($equipes->count() > 0)
        <div class="mt-4 mb-2">
            <h2 class="text-base font-bold text-gray-700 px-1">Revezamentos</h2>
        </div>

        @foreach($equipes as $equipe)
            @php
                $statusColor = match($equipe->status) {
                    'Pendente'     => 'bg-gray-100 border-gray-300 text-gray-600',
                    'Em andamento' => 'bg-blue-50 border-blue-400 text-blue-700',
                    'Finalizada'   => 'bg-green-50 border-green-400 text-green-700',
                    default        => 'bg-gray-100 border-gray-300',
                };
                $statusBadge = match($equipe->status) {
                    'Pendente'     => 'bg-gray-200 text-gray-700',
                    'Em andamento' => 'bg-blue-200 text-blue-800',
                    'Finalizada'   => 'bg-green-200 text-green-800',
                    default        => 'bg-gray-200 text-gray-700',
                };
                $modalidadeBadge = match($equipe->modalidade) {
                    'Misto'    => 'bg-purple-100 text-purple-700',
                    'Feminino' => 'bg-pink-100 text-pink-700',
                    default    => 'bg-blue-100 text-blue-700',
                };
                $ordem = $equipe->ordem_execucao ? str_pad($equipe->ordem_execucao, 2, '0', STR_PAD_LEFT) : '--';
                $eqFeedback = $this->feedbacksEquipes[$equipe->id] ?? null;
                $eqConfirmado = $equipe->status_lancamento === 'Confirmado';
                $eqBloqueado  = $eqConfirmado || $equipe->status === 'Pendente';
                $eqLancado    = !empty($this->temposEquipes[$equipe->id]) && !empty($this->colocacoesEquipes[$equipe->id]);
                $eqEditando   = isset($this->editandoEquipes[$equipe->id]);
                $medalhaIcon  = match($equipe->medalha ?? '') {
                    'Ouro'   => '🥇',
                    'Prata'  => '🥈',
                    'Bronze' => '🥉',
                    default  => '',
                };
            @endphp

            <div class="mb-3 rounded-lg shadow border-l-4 {{ $statusColor }}">
                {{-- Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 py-3 bg-white rounded-t-lg">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-mono text-gray-400">[{{ $ordem }}]</span>
                        <span class="font-bold text-gray-800">{{ $medalhaIcon }} {{ $equipe->nome }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $modalidadeBadge }}">{{ $equipe->modalidade }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $equipe->tipo }} · 4×{{ $equipe->distancia->metragem }}</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusBadge }}">{{ $equipe->status }}</span>
                    </div>
                    <div class="flex gap-2 mt-2 sm:mt-0">
                        @if($equipe->status === 'Pendente')
                            <button wire:click="iniciarEquipe({{ $equipe->id }})"
                                    class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                                Iniciar
                            </button>
                        @elseif($equipe->status === 'Em andamento' && $equipe->status_lancamento === 'Lançado')
                            <button wire:click="confirmarEquipe({{ $equipe->id }})"
                                    class="text-xs bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition"
                                    onclick="return confirm('Confirmar resultado desta equipe?')">
                                Confirmar
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Atletas --}}
                <div class="px-4 py-2 bg-white border-t border-gray-100">
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 mb-2">
                        @foreach($equipe->membros as $membro)
                            <span>
                                <span class="text-gray-400">{{ $membro->posicao }}.</span>
                                {{ $membro->atleta->nome }}
                                @if($equipe->tipo === 'Medley')
                                    <span class="text-xs text-blue-500">({{ \App\Models\Equipe::MEDLEY_ESTILOS[$membro->posicao] }})</span>
                                @endif
                            </span>
                        @endforeach
                    </div>

                    {{-- Inputs de resultado --}}
                    <div class="flex items-center gap-2 {{ $eqConfirmado ? 'opacity-70' : '' }}">
                        @if($eqLancado && !$eqEditando && !$eqBloqueado)
                            <span class="w-24 text-sm text-center font-mono text-gray-700 bg-gray-50 border rounded px-2 py-1">{{ $this->temposEquipes[$equipe->id] }}</span>
                            <span class="w-16 text-sm text-center text-gray-700 bg-gray-50 border rounded px-2 py-1">{{ $this->colocacoesEquipes[$equipe->id] }}º</span>
                            @if($this->rcosEquipes[$equipe->id] ?? false)
                                <span class="text-xs font-bold text-orange-600 bg-orange-100 border border-orange-300 rounded px-1.5 py-0.5">RCO</span>
                            @endif
                            <button wire:click="habilitarEdicaoEquipe({{ $equipe->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-800 hover:underline px-1">Editar</button>
                        @else
                            <input type="tel"
                                   wire:model.blur="temposEquipes.{{ $equipe->id }}"
                                   oninput="mascaraTempoInput(this)"
                                   placeholder="__:__.__" maxlength="8" pattern="[0-9]*"
                                   class="w-24 text-sm text-center border rounded px-2 py-1 font-mono {{ $eqBloqueado ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-blue-500 focus:border-blue-500' }}"
                                   {{ $eqBloqueado ? 'disabled' : '' }}>
                            <input type="tel"
                                   wire:model.blur="colocacoesEquipes.{{ $equipe->id }}"
                                   oninput="this.value=this.value.replace(/\D/g,'').substring(0,2)"
                                   placeholder="#" maxlength="2" pattern="[0-9]*"
                                   class="w-16 text-sm text-center border rounded px-2 py-1 {{ $eqBloqueado ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-blue-500 focus:border-blue-500' }}"
                                   {{ $eqBloqueado ? 'disabled' : '' }}>
                            <label class="flex items-center gap-1 text-xs text-gray-600 cursor-pointer select-none {{ $eqBloqueado ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <input type="checkbox"
                                       wire:model.live="rcosEquipes.{{ $equipe->id }}"
                                       class="rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                                       {{ $eqBloqueado ? 'disabled' : '' }}>
                                RCO
                            </label>
                        @endif

                        <span class="w-6 text-center">
                            @if($eqFeedback === 'salvo')
                                <span class="text-green-500" title="Salvo">&#10004;</span>
                            @elseif($eqFeedback && str_starts_with($eqFeedback, 'erro:'))
                                <span class="text-red-500 cursor-help" title="{{ substr($eqFeedback, 5) }}">&#9888;</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<script>
    function mascaraTempo(valor) {
        var nums = valor.replace(/\D/g, '').substring(0, 6);
        if (nums.length <= 2) return nums;
        if (nums.length <= 4) return nums.substring(0, 2) + ':' + nums.substring(2);
        return nums.substring(0, 2) + ':' + nums.substring(2, 4) + '.' + nums.substring(4);
    }

    function mascaraTempoInput(el) {
        el.value = mascaraTempo(el.value);
        if (el.value.length === 8) {
            var nextId = el.getAttribute('data-next');
            if (nextId) {
                var next = document.getElementById(nextId);
                if (next && !next.disabled) {
                    next.focus();
                    next.select();
                }
            }
        }
    }

    function mascaraColocacao(el) {
        el.value = el.value.replace(/\D/g, '').substring(0, 2);
        if (el.value.length === 0) return;
        var allTempos = Array.from(document.querySelectorAll('input[id^="tempo-"]:not([disabled])'));
        var currentNum = el.id.replace('coloc-', '');
        var currentTempoIdx = allTempos.findIndex(function(t) { return t.id === 'tempo-' + currentNum; });
        if (currentTempoIdx >= 0 && currentTempoIdx + 1 < allTempos.length) {
            var nextTempo = allTempos[currentTempoIdx + 1];
            nextTempo.focus();
            nextTempo.select();
        }
    }
</script>
