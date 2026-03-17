@extends('layouts.app')

@section('title', 'Classificados para Brasileiro')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Classificados para o Campeonato Brasileiro</h1>
        <p class="text-sm text-gray-500 mt-1">Atletas com melhor tempo igual ou abaixo do índice técnico</p>
    </div>

    {{-- Seletores de Sexo e Categoria --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sexo</label>
                <select name="sexo" class="w-full text-sm border rounded-md p-2">
                    <option value="feminino" {{ $sexo == 'feminino' ? 'selected' : '' }}>Feminino</option>
                    <option value="masculino" {{ $sexo == 'masculino' ? 'selected' : '' }}>Masculino</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Categoria</label>
                <select name="categoria" class="w-full text-sm border rounded-md p-2">
                    @foreach($categorias as $sigla => $nome)
                        <option value="{{ $sigla }}" {{ $categoria == $sigla ? 'selected' : '' }}>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-semibold transition">Consultar</button>
            </div>
        </div>
    </form>

    {{-- Resultado --}}
    @if($classificados->count() > 0)
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow p-6 text-center border border-green-200">
            <div class="text-3xl font-bold text-green-700">{{ $classificados->count() }}</div>
            <div class="text-sm text-green-600 font-semibold mt-1">
                {{ $classificados->count() === 1 ? 'índice atingido' : 'índices atingidos' }}
                ({{ $classificados->pluck('atleta_id')->unique()->count() }} {{ $classificados->pluck('atleta_id')->unique()->count() === 1 ? 'atleta' : 'atletas' }})
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atleta</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prova</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Piscina</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Melhor Tempo</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Índice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($classificados as $r)
                        <tr class="hover:bg-green-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $r->atleta->nome }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $r->prova_chave }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $r->piscina_resultado }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono font-bold text-green-700">{{ $r->tempo }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono text-gray-600">{{ $r->indice_formatado }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            Nenhum atleta com índice para o Campeonato Brasileiro nesta categoria.
        </div>
    @endif
</div>
@endsection
