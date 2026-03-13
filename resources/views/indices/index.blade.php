@extends('layouts.app')

@section('title', 'Índices Técnicos')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Índices Técnicos - Campeonato Brasileiro</h1>
        <p class="text-sm text-gray-500 mt-1">Compare os tempos dos atletas com os índices técnicos de inverno (1º semestre) e verão (2º semestre)</p>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Atleta</label>
                <select name="atleta_id" class="w-full text-sm border rounded-md p-2" required>
                    <option value="">Selecione um atleta</option>
                    @foreach($atletas as $a)
                        <option value="{{ $a->id }}" {{ $filtros['atleta_id'] == $a->id ? 'selected' : '' }}>{{ $a->nome }}</option>
                    @endforeach
                </select>
            </div>
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
                <label class="block text-xs font-medium text-gray-500 mb-1">Sexo</label>
                <select name="sexo" class="w-full text-sm border rounded-md p-2">
                    <option value="feminino" {{ $filtros['sexo'] == 'feminino' ? 'selected' : '' }}>Feminino</option>
                    <option value="masculino" {{ $filtros['sexo'] == 'masculino' ? 'selected' : '' }}>Masculino</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Categoria</label>
                <select name="categoria" class="w-full text-sm border rounded-md p-2">
                    @foreach($categorias as $sigla => $nome)
                        <option value="{{ $sigla }}" {{ $filtros['categoria'] == $sigla ? 'selected' : '' }}>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-semibold transition">Consultar</button>
                <a href="{{ route('indices.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm transition">Limpar</a>
            </div>
        </div>
    </form>

    {{-- Legenda --}}
    @if($resultados->count() > 0)
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap gap-4 text-xs">
            <div class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded bg-green-100 border border-green-400"></span>
                <span class="text-gray-600">Tempo abaixo do índice (classificado)</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded bg-red-100 border border-red-400"></span>
                <span class="text-gray-600">Tempo acima do índice</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded bg-gray-100 border border-gray-300"></span>
                <span class="text-gray-600">Índice não disponível para esta prova/piscina</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabela de Resultados --}}
    @if(!empty($filtros['atleta_id']))
        @if($resultados->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">
                    {{ $resultados->first()->atleta->nome }}
                    <span class="text-sm font-normal text-gray-500">
                        - {{ ucfirst($filtros['sexo']) }} / {{ $categorias[$filtros['categoria']] }}
                    </span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Competição</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prova</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Piscina</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tempo</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-blue-600 uppercase" title="Índice de Inverno (1º Semestre)">Índ. Inverno</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-blue-600 uppercase">Diferença</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-orange-600 uppercase" title="Índice de Verão (2º Semestre)">Índ. Verão</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-orange-600 uppercase">Diferença</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($resultados as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $r->campeonato->nome }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $r->prova_chave }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $r->piscina_resultado }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono font-bold text-gray-800">{{ $r->tempo }}</td>

                            {{-- Índice Inverno --}}
                            @if(isset($r->indice_inverno))
                                <td class="px-4 py-3 text-sm text-center font-mono text-blue-700">
                                    {{ \App\Http\Controllers\IndicesController::centesimosParaTempo($r->indice_inverno) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-mono font-semibold {{ $r->diff_inverno <= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }}">
                                    {{ \App\Http\Controllers\IndicesController::formatarDiferenca($r->diff_inverno) }}
                                </td>
                            @else
                                <td class="px-4 py-3 text-sm text-center text-gray-400">-</td>
                                <td class="px-4 py-3 text-sm text-center text-gray-400">-</td>
                            @endif

                            {{-- Índice Verão --}}
                            @if(isset($r->indice_verao))
                                <td class="px-4 py-3 text-sm text-center font-mono text-orange-700">
                                    {{ \App\Http\Controllers\IndicesController::centesimosParaTempo($r->indice_verao) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-mono font-semibold {{ $r->diff_verao <= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }}">
                                    {{ \App\Http\Controllers\IndicesController::formatarDiferenca($r->diff_verao) }}
                                </td>
                            @else
                                <td class="px-4 py-3 text-sm text-center text-gray-400">-</td>
                                <td class="px-4 py-3 text-sm text-center text-gray-400">-</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Resumo --}}
        @php
            $comIndice = $resultados->filter(fn($r) => isset($r->indice_verao));
            $classificadosVerao = $comIndice->filter(fn($r) => isset($r->diff_verao) && $r->diff_verao <= 0)->count();
            $classificadosInverno = $comIndice->filter(fn($r) => isset($r->diff_inverno) && $r->diff_inverno <= 0)->count();
            $totalComIndice = $comIndice->count();
        @endphp
        @if($totalComIndice > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-6 text-center border border-blue-200">
                <div class="text-3xl font-bold text-blue-700">{{ $classificadosInverno }}/{{ $totalComIndice }}</div>
                <div class="text-sm text-blue-600 font-semibold mt-1">Índices de Inverno atingidos</div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow p-6 text-center border border-orange-200">
                <div class="text-3xl font-bold text-orange-700">{{ $classificadosVerao }}/{{ $totalComIndice }}</div>
                <div class="text-sm text-orange-600 font-semibold mt-1">Índices de Verão atingidos</div>
            </div>
        </div>
        @endif

        @else
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            Nenhum resultado com tempo encontrado para este atleta.
        </div>
        @endif
    @else
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        Selecione um atleta para consultar os índices técnicos.
    </div>
    @endif
</div>
@endsection
