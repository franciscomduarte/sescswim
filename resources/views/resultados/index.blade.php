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

    {{-- Cards de Medalhas --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow p-6 text-center border border-yellow-200">
            <div class="text-4xl font-bold text-yellow-600">{{ $medalhas['ouro'] }}</div>
            <div class="text-sm text-yellow-700 font-semibold mt-1">🥇 Ouro</div>
        </div>
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg shadow p-6 text-center border border-gray-200">
            <div class="text-4xl font-bold text-gray-500">{{ $medalhas['prata'] }}</div>
            <div class="text-sm text-gray-600 font-semibold mt-1">🥈 Prata</div>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow p-6 text-center border border-orange-200">
            <div class="text-4xl font-bold text-orange-600">{{ $medalhas['bronze'] }}</div>
            <div class="text-sm text-orange-700 font-semibold mt-1">🥉 Bronze</div>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($desempenhoAtletas as $d)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $d->atleta->nome }}</td>
                            <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $d->total_provas }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold text-yellow-600">{{ $d->ouro ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold text-gray-400">{{ $d->prata ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold text-orange-500">{{ $d->bronze ?: '-' }}</td>
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

    {{-- Comparação Piscina --}}
    @if($comparacaoPiscina->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Comparação 25m vs 50m</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            @foreach($comparacaoPiscina as $p)
                <div class="border rounded-lg p-4 text-center">
                    <div class="text-lg font-bold text-blue-700 mb-2">Piscina {{ $p->piscina }}</div>
                    <div class="text-sm text-gray-500 mb-3">{{ $p->total }} resultados</div>
                    <div class="flex justify-center gap-4">
                        <div>
                            <div class="text-xl font-bold text-yellow-600">{{ $p->ouro }}</div>
                            <div class="text-xs text-gray-500">Ouro</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-400">{{ $p->prata }}</div>
                            <div class="text-xs text-gray-500">Prata</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-orange-500">{{ $p->bronze }}</div>
                            <div class="text-xs text-gray-500">Bronze</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
