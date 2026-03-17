@extends('layouts.app')

@section('title', 'Provas')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Provas</h1>
    </div>

    <form action="{{ route('provas.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 mb-6">
        @csrf
        <h2 class="font-semibold text-lg mb-4">Nova Prova</h2>
        <div class="flex gap-3">
            <input type="text" name="nome" value="{{ old('nome') }}" placeholder="Nome da prova" required
                   class="flex-1 border-gray-300 rounded-md shadow-sm p-2 border">
            <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 font-semibold transition">Cadastrar</button>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscrições</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campeonatos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($provas as $prova)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $prova->nome }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $prova->inscricoes_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @forelse($prova->campeonatos_uso as $camp)
                                <span class="inline-block bg-blue-50 text-blue-700 text-xs rounded px-2 py-0.5 mr-1 mb-1">{{ $camp->nome }}</span>
                            @empty
                                <span class="text-gray-400">—</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('provas.edit', $prova) }}" class="text-gray-600 hover:underline">Editar</a>
                            <form action="{{ route('provas.destroy', $prova) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Excluir a prova {{ $prova->nome }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Nenhuma prova cadastrada</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
