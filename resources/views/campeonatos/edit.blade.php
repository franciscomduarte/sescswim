@extends('layouts.app')

@section('title', 'Editar - ' . $campeonato->nome)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="text-sm text-gray-500">
        <a href="{{ route('campeonatos.index') }}" class="hover:underline">Campeonatos</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">{{ $campeonato->nome }}</span>
    </div>

    {{-- Dados do Campeonato --}}
    <form action="{{ route('campeonatos.update', $campeonato) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')
        <h2 class="text-lg font-bold text-gray-800 mb-4">Dados do Campeonato</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Nome</label>
                <input type="text" name="nome" value="{{ old('nome', $campeonato->nome) }}" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="{{ old('data_inicio', $campeonato->data_inicio->format('Y-m-d')) }}" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="{{ old('data_fim', $campeonato->data_fim->format('Y-m-d')) }}" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Piscina</label>
                <select name="piscina" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    <option value="25m" {{ $campeonato->piscina === '25m' ? 'selected' : '' }}>25 metros</option>
                    <option value="50m" {{ $campeonato->piscina === '50m' ? 'selected' : '' }}>50 metros</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 font-semibold transition">Salvar</button>
            </div>
        </div>
        @if($errors->any())
            <div class="mt-3 text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </form>

    {{-- Resumo --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $totais['inscricoes'] }}</div>
            <div class="text-xs text-gray-500">Inscrições</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $totais['resultados'] }}</div>
            <div class="text-xs text-gray-500">Resultados</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-gray-500">{{ $totais['pendentes'] }}</div>
            <div class="text-xs text-gray-500">Pendentes</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $totais['finalizadas'] }}</div>
            <div class="text-xs text-gray-500">Finalizadas</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow p-3 text-center border border-yellow-200">
            <div class="text-xl font-bold text-yellow-600">{{ $totais['ouro'] }}</div>
            <div class="text-xs text-yellow-700">Ouro</div>
        </div>
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg shadow p-3 text-center border border-gray-200">
            <div class="text-xl font-bold text-gray-500">{{ $totais['prata'] }}</div>
            <div class="text-xs text-gray-600">Prata</div>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow p-3 text-center border border-orange-200">
            <div class="text-xl font-bold text-orange-600">{{ $totais['bronze'] }}</div>
            <div class="text-xs text-orange-700">Bronze</div>
        </div>
    </div>

    {{-- Links rápidos --}}
    <div class="flex gap-3">
        <a href="{{ route('painel.show', $campeonato) }}" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm font-semibold transition">Abrir Painel</a>
        <a href="{{ route('importacao.index') }}" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 text-sm transition">Importar Resultados</a>
    </div>

    {{-- Inscrições por Prova --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Inscrições por Prova</h2>
            <span class="text-sm text-gray-500">{{ $totais['inscricoes'] }} inscrições</span>
        </div>

        @forelse($inscricoes as $nomeProva => $atletasProva)
            <div class="border-b last:border-b-0">
                <div class="px-6 py-3 bg-gray-50 flex justify-between items-center">
                    <span class="font-semibold text-gray-700">{{ $nomeProva }}</span>
                    @php
                        $statusProva = $atletasProva->first()->status;
                        $badgeClass = match($statusProva) {
                            'Pendente' => 'bg-gray-200 text-gray-700',
                            'Em andamento' => 'bg-blue-200 text-blue-800',
                            'Finalizada' => 'bg-green-200 text-green-800',
                            default => 'bg-gray-200 text-gray-700',
                        };
                    @endphp
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeClass }}">
                        {{ $statusProva }} ({{ $atletasProva->count() }} atletas)
                    </span>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($atletasProva as $inscricao)
                        <div class="px-6 py-2 flex items-center justify-between hover:bg-gray-50">
                            <span class="text-sm text-gray-700">{{ $inscricao->atleta->nome }}</span>
                            <form action="{{ route('campeonatos.remover-inscricao', [$campeonato, $inscricao]) }}" method="POST" class="inline" onsubmit="return confirm('Remover inscrição de {{ $inscricao->atleta->nome }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:underline">Remover</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-500">Nenhuma inscrição</div>
        @endforelse
    </div>

    {{-- Resultados por Prova --}}
    @if($resultados->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Resultados por Prova</h2>
            <span class="text-sm text-gray-500">{{ $totais['resultados'] }} resultados</span>
        </div>

        @foreach($resultados as $nomeProva => $resultadosProva)
            <div class="border-b last:border-b-0">
                <div class="px-6 py-3 bg-gray-50">
                    <span class="font-semibold text-gray-700">{{ $nomeProva }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="px-6 py-2 text-left">#</th>
                                <th class="px-6 py-2 text-left">Atleta</th>
                                <th class="px-6 py-2 text-center">Tempo</th>
                                <th class="px-6 py-2 text-center">Medalha</th>
                                <th class="px-6 py-2 text-center">Status</th>
                                <th class="px-6 py-2 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($resultadosProva as $resultado)
                                @php
                                    $medalhaIcon = match($resultado->medalha) {
                                        'Ouro' => '🥇',
                                        'Prata' => '🥈',
                                        'Bronze' => '🥉',
                                        default => '-',
                                    };
                                    $statusClass = match($resultado->status_lancamento) {
                                        'Pendente' => 'bg-gray-200 text-gray-700',
                                        'Lançado' => 'bg-blue-200 text-blue-800',
                                        'Confirmado' => 'bg-green-200 text-green-800',
                                        default => 'bg-gray-200',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-2 text-sm text-gray-500">{{ $resultado->colocacao ?? '-' }}º</td>
                                    <td class="px-6 py-2 text-sm font-medium text-gray-700">{{ $resultado->atleta->nome }}</td>
                                    <td class="px-6 py-2 text-sm text-center font-mono">{{ $resultado->tempo ?? '-' }}</td>
                                    <td class="px-6 py-2 text-sm text-center">{{ $medalhaIcon }}</td>
                                    <td class="px-6 py-2 text-center">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusClass }}">{{ $resultado->status_lancamento }}</span>
                                    </td>
                                    <td class="px-6 py-2 text-right">
                                        <form action="{{ route('campeonatos.remover-resultado', [$campeonato, $resultado]) }}" method="POST" class="inline" onsubmit="return confirm('Remover resultado de {{ $resultado->atleta->nome }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:underline">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Zona de perigo --}}
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h2 class="text-lg font-bold text-red-700 mb-2">Zona de perigo</h2>
        <p class="text-sm text-red-600 mb-4">Excluir este campeonato removerá todas as inscrições e resultados associados.</p>
        <form action="{{ route('campeonatos.destroy', $campeonato) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o campeonato {{ $campeonato->nome }} e TODOS os dados?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 text-sm font-semibold transition">Excluir Campeonato</button>
        </form>
    </div>
</div>
@endsection
