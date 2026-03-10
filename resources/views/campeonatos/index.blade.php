@extends('layouts.app')

@section('title', 'Campeonatos')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Campeonatos</h1>
    </div>

    <form action="{{ route('campeonatos.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 mb-6">
        @csrf
        <h2 class="font-semibold text-lg mb-4">Novo Campeonato</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <input type="text" name="nome" placeholder="Nome do campeonato" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Piscina</label>
                <select name="piscina" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    <option value="25m">25 metros</option>
                    <option value="50m">50 metros</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 font-semibold transition">Criar</button>
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

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Piscina</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscritos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resultados</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($campeonatos as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            <a href="{{ route('campeonatos.edit', $c) }}" class="text-blue-700 hover:underline">{{ $c->nome }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $c->data_inicio->format('d/m/Y') }} - {{ $c->data_fim->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $c->piscina }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $c->inscricoes_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $c->resultados_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('campeonatos.edit', $c) }}" class="text-gray-600 hover:underline">Editar</a>
                            <a href="{{ route('painel.show', $c) }}" class="text-blue-600 hover:underline">Painel</a>
                            <form action="{{ route('campeonatos.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Excluir campeonato e todos os dados relacionados?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Nenhum campeonato cadastrado</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
