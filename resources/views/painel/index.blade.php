@extends('layouts.app')

@section('title', 'Painel de Lançamento')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Painel de Lançamento</h1>
    <p class="text-gray-600 mb-4">Selecione um campeonato para abrir o painel operacional:</p>

    <div class="space-y-3">
        @forelse($campeonatos as $c)
            <a href="{{ route('painel.show', $c) }}" class="block bg-white rounded-lg shadow p-4 hover:bg-blue-50 transition border-l-4 border-blue-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-gray-800">{{ $c->nome }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ $c->data_inicio->format('d/m/Y') }} - {{ $c->data_fim->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ $c->piscina }}</span>
                </div>
            </a>
        @empty
            <div class="text-center py-12 text-gray-500">
                Nenhum campeonato cadastrado. <a href="{{ route('campeonatos.index') }}" class="text-blue-600 hover:underline">Criar campeonato</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
