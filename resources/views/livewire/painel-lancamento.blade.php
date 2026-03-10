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

    {{-- Lista de Provas --}}
    @forelse($provasAgrupadas as $chaveProva => $inscricoes)
        @php
            $primeiraInscricao = $inscricoes->first();
            $status = $primeiraInscricao->status;
            $statusColor = match($status) {
                'Pendente' => 'bg-gray-100 border-gray-300 text-gray-600',
                'Em andamento' => 'bg-blue-50 border-blue-400 text-blue-700',
                'Finalizada' => 'bg-green-50 border-green-400 text-green-700',
                default => 'bg-gray-100 border-gray-300',
            };
            $statusBadge = match($status) {
                'Pendente' => 'bg-gray-200 text-gray-700',
                'Em andamento' => 'bg-blue-200 text-blue-800',
                'Finalizada' => 'bg-green-200 text-green-800',
                default => 'bg-gray-200 text-gray-700',
            };
            $ordem = str_pad($primeiraInscricao->ordem_execucao, 2, '0', STR_PAD_LEFT);
        @endphp

        <div class="mb-3 rounded-lg shadow border-l-4 {{ $statusColor }}">
            {{-- Header da prova --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 py-3 bg-white rounded-t-lg">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-mono text-gray-400">[{{ $ordem }}]</span>
                    <span class="font-bold text-gray-800">
                        {{ $primeiraInscricao->distancia->metragem }} {{ $primeiraInscricao->prova->nome }}
                    </span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusBadge }}">{{ $status }}</span>
                </div>
                <div class="flex gap-2 mt-2 sm:mt-0">
                    @if($status === 'Pendente')
                        <button wire:click="iniciarProva('{{ $chaveProva }}')" class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                            Iniciar Prova
                        </button>
                    @elseif($status === 'Em andamento')
                        <button wire:click="confirmarProva('{{ $chaveProva }}')" class="text-xs bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition" onclick="return confirm('Confirmar todos os resultados desta prova?')">
                            Confirmar Prova
                        </button>
                    @endif
                </div>
            </div>

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
