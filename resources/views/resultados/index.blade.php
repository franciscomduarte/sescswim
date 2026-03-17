@extends('layouts.app')

@section('title', 'Resultados Consolidados')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Resultados Consolidados</h1>

    {{-- Filtros --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Competição</label>
                <select name="campeonato_id" class="w-full text-sm border rounded-md p-2">
                    <option value="">Todas</option>
                    @foreach($campeonatos as $c)
                        <option value="{{ $c->id }}" {{ $filtros['campeonato_id'] == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Atleta</label>
                <select name="atleta_id" class="w-full text-sm border rounded-md p-2">
                    <option value="">Todos</option>
                    @foreach($atletas as $a)
                        <option value="{{ $a->id }}" {{ $filtros['atleta_id'] == $a->id ? 'selected' : '' }}>{{ $a->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Período Início</label>
                <input type="date" name="periodo_inicio" value="{{ $filtros['periodo_inicio'] }}" class="w-full text-sm border rounded-md p-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Período Fim</label>
                <input type="date" name="periodo_fim" value="{{ $filtros['periodo_fim'] }}" class="w-full text-sm border rounded-md p-2">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-semibold transition">Filtrar</button>
                <a href="{{ route('resultados.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm transition">Limpar</a>
            </div>
        </div>
    </form>

    {{-- Cards de Medalhas + RCO --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow p-6 text-center border border-yellow-200">
            <div class="text-4xl font-bold text-yellow-600">{{ $medalhas['ouro'] }}</div>
            <div class="text-sm text-yellow-700 font-semibold mt-1">🥇 Ouro</div>
            @if($medalhasRevezamento['ouro'] > 0)
                <div class="text-xs text-yellow-600 mt-1">+{{ $medalhasRevezamento['ouro'] }} revezamento</div>
            @endif
        </div>
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg shadow p-6 text-center border border-gray-200">
            <div class="text-4xl font-bold text-gray-500">{{ $medalhas['prata'] }}</div>
            <div class="text-sm text-gray-600 font-semibold mt-1">🥈 Prata</div>
            @if($medalhasRevezamento['prata'] > 0)
                <div class="text-xs text-gray-500 mt-1">+{{ $medalhasRevezamento['prata'] }} revezamento</div>
            @endif
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow p-6 text-center border border-orange-200">
            <div class="text-4xl font-bold text-orange-600">{{ $medalhas['bronze'] }}</div>
            <div class="text-sm text-orange-700 font-semibold mt-1">🥉 Bronze</div>
            @if($medalhasRevezamento['bronze'] > 0)
                <div class="text-xs text-orange-500 mt-1">+{{ $medalhasRevezamento['bronze'] }} revezamento</div>
            @endif
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow p-6 text-center border border-purple-200">
            <div class="text-4xl font-bold text-purple-600">{{ $totalRcos }}</div>
            <div class="text-sm text-purple-700 font-semibold mt-1">⭐ RCO</div>
        </div>
    </div>

    {{-- Desempenho por Atleta --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Desempenho por Atleta</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atleta</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Provas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-yellow-600 uppercase">🥇</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase">🥈</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-orange-500 uppercase">🥉</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">4º</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">5º</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">6º</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">7º</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">8º</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-purple-600 uppercase">RCO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($desempenhoAtletas as $d)
                        @php $rev = $relayMedalsPorAtleta[$d->atleta_id] ?? null; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $d->atleta->nome }}</td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $d->total_provas }}</td>
                            <td class="px-6 py-3 text-sm text-center">
                                @if($d->ouro || ($rev && $rev['ouro'] > 0))
                                    <span class="font-bold text-yellow-600">{{ $d->ouro ?: 0 }}</span>
                                    @if($rev && $rev['ouro'] > 0)
                                        <div class="text-xs text-yellow-500 leading-tight">+{{ $rev['ouro'] }} rev</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-center">
                                @if($d->prata || ($rev && $rev['prata'] > 0))
                                    <span class="font-bold text-gray-400">{{ $d->prata ?: 0 }}</span>
                                    @if($rev && $rev['prata'] > 0)
                                        <div class="text-xs text-gray-400 leading-tight">+{{ $rev['prata'] }} rev</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-center">
                                @if($d->bronze || ($rev && $rev['bronze'] > 0))
                                    <span class="font-bold text-orange-500">{{ $d->bronze ?: 0 }}</span>
                                    @if($rev && $rev['bronze'] > 0)
                                        <div class="text-xs text-orange-400 leading-tight">+{{ $rev['bronze'] }} rev</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $d->quarto ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $d->quinto ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $d->sexto ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $d->setimo ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $d->oitavo ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center">
                                @if($d->rcos > 0)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-semibold">{{ $d->rcos }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-8 text-center text-gray-500">Nenhum resultado encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Revezamentos --}}
    @if($revezamentos->isNotEmpty())
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Revezamentos (até 8º lugar)</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($revezamentos as $campNome => $equipes)
            <div class="p-4">
                <h3 class="text-sm font-bold text-blue-700 mb-3">{{ $campNome }}</h3>
                <div class="space-y-3">
                    @foreach($equipes as $equipe)
                    <div class="border rounded-lg p-3 {{ $equipe->rco ? 'border-purple-200 bg-purple-50' : 'border-gray-100' }}">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div>
                                <span class="font-bold
                                    {{ $equipe->colocacao == 1 ? 'text-yellow-500' : ($equipe->colocacao == 2 ? 'text-gray-400' : ($equipe->colocacao == 3 ? 'text-orange-500' : 'text-gray-600')) }}">
                                    {{ $equipe->colocacao }}º
                                </span>
                                <span class="font-medium text-gray-800 ml-2">{{ $equipe->nome }}</span>
                                <span class="ml-2 text-xs text-gray-500">{{ $equipe->modalidade }} · {{ $equipe->tipo }} · {{ $equipe->distancia?->metros ?? '' }}m×4</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($equipe->tempo)
                                    <span class="font-mono text-sm text-gray-700">{{ $equipe->tempo }}</span>
                                @endif
                                @if($equipe->medalha)
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                        {{ $equipe->medalha === 'Ouro' ? 'bg-yellow-100 text-yellow-700' : ($equipe->medalha === 'Prata' ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600') }}">
                                        {{ $equipe->medalha }}
                                    </span>
                                @endif
                                @if($equipe->rco)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-semibold">RCO</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($equipe->membros as $membro)
                                <span class="text-xs bg-gray-100 text-gray-700 rounded px-2 py-0.5">
                                    {{ $membro->posicao }}.
                                    @if($equipe->tipo === 'Medley')
                                        <span class="text-blue-600">{{ \App\Models\Equipe::MEDLEY_ESTILOS[$membro->posicao] }}</span> —
                                    @endif
                                    {{ $membro->atleta->nome }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recordes (RCO) --}}
    @if($recordesIndividuais->isNotEmpty() || $recordesRevezamento->isNotEmpty())
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-purple-50">
            <h2 class="text-lg font-bold text-purple-800">⭐ Recordes Obtidos (RCO)</h2>
        </div>
        <div class="p-4 space-y-6">
            {{-- Individuais --}}
            @if($recordesIndividuais->isNotEmpty())
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-3">Individuais</h3>
                <div class="space-y-4">
                    @foreach($recordesIndividuais as $campNome => $recordes)
                    <div>
                        <div class="text-xs font-bold text-blue-700 mb-2">{{ $campNome }}</div>
                        <div class="space-y-1">
                            @foreach($recordes as $r)
                            <div class="flex items-center gap-3 text-sm bg-purple-50 rounded px-3 py-2">
                                <span class="font-medium text-gray-800">{{ $r->atleta->nome }}</span>
                                <span class="text-gray-500">{{ $r->prova?->nome }}</span>
                                @if($r->distancia)
                                    <span class="text-gray-400">{{ $r->distancia->metros }}m</span>
                                @endif
                                @if($r->tempo)
                                    <span class="font-mono text-purple-700 font-semibold">{{ $r->tempo }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Revezamento --}}
            @if($recordesRevezamento->isNotEmpty())
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-3">Revezamento</h3>
                <div class="space-y-4">
                    @foreach($recordesRevezamento as $campNome => $equipes)
                    <div>
                        <div class="text-xs font-bold text-blue-700 mb-2">{{ $campNome }}</div>
                        <div class="space-y-1">
                            @foreach($equipes as $equipe)
                            <div class="flex items-center gap-3 text-sm bg-purple-50 rounded px-3 py-2 flex-wrap">
                                <span class="font-medium text-gray-800">{{ $equipe->nome }}</span>
                                <span class="text-gray-500">{{ $equipe->modalidade }} · {{ $equipe->tipo }}</span>
                                @if($equipe->distancia)
                                    <span class="text-gray-400">{{ $equipe->distancia->metros }}m×4</span>
                                @endif
                                @if($equipe->tempo)
                                    <span class="font-mono text-purple-700 font-semibold">{{ $equipe->tempo }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Premiações Especiais --}}
    @if($premiacoes->isNotEmpty())
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-amber-50">
            <h2 class="text-lg font-bold text-amber-800">🏆 Premiações Especiais</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($premiacoes as $campeonato)
            <div class="p-4">
                <h3 class="text-sm font-bold text-blue-700 mb-3">
                    {{ $campeonato->nome }}
                    <span class="text-gray-400 font-normal ml-1 text-xs">{{ $campeonato->data_inicio?->format('d/m/Y') }}</span>
                </h3>
                @php
                    $porTipo = $campeonato->premiacoes->groupBy('tipo');
                @endphp
                <div class="space-y-3">
                    @foreach($porTipo as $tipo => $items)
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ $tipo }}</div>
                        <div class="space-y-1">
                            @foreach($items as $premiacao)
                            <div class="flex items-center gap-2 text-sm">
                                @if($premiacao->isEquipe())
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-semibold">Equipe</span>
                                @else
                                    <span class="font-medium text-gray-800">{{ $premiacao->atleta?->nome ?? '—' }}</span>
                                @endif
                                @if($premiacao->observacao)
                                    <span class="text-gray-400 text-xs">— {{ $premiacao->observacao }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Resultados por Competição --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Resultados por Competição</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Competição</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-yellow-600 uppercase">🥇</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase">🥈</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-orange-500 uppercase">🥉</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($resultadosCompeticao as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $r->campeonato->nome }}</td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $r->total }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold text-yellow-600">{{ $r->ouro ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold text-gray-400">{{ $r->prata ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold text-orange-500">{{ $r->bronze ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum resultado encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Total Geral por Piscina --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Total Geral</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            @php
                $piscinaLookup = $comparacaoPiscina->keyBy('piscina');
            @endphp
            @foreach(['25m', '50m'] as $tamanho)
                @php
                    $p = $piscinaLookup[$tamanho] ?? null;
                    $revP = $relayMedalsPorPiscina[$tamanho] ?? ['ouro' => 0, 'prata' => 0, 'bronze' => 0];
                    $total = $p ? $p->total : 0;
                    $ouro   = ($p ? $p->ouro   : 0) + $revP['ouro'];
                    $prata  = ($p ? $p->prata  : 0) + $revP['prata'];
                    $bronze = ($p ? $p->bronze : 0) + $revP['bronze'];
                @endphp
                <div class="border rounded-lg p-4 text-center">
                    <div class="text-lg font-bold text-blue-700 mb-2">Piscina {{ $tamanho }}</div>
                    <div class="text-sm text-gray-500 mb-3">{{ $total }} resultados</div>
                    <div class="flex justify-center gap-4">
                        <div>
                            <div class="text-xl font-bold text-yellow-600">{{ $ouro }}</div>
                            <div class="text-xs text-gray-500">Ouro</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-400">{{ $prata }}</div>
                            <div class="text-xs text-gray-500">Prata</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-orange-500">{{ $bronze }}</div>
                            <div class="text-xs text-gray-500">Bronze</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
