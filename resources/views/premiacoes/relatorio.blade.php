@extends('layouts.app')

@section('title', 'Premiações')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Premiações Especiais</h1>
    </div>

    @forelse($campeonatos as $campeonato)
        <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
            {{-- Cabeçalho do campeonato --}}
            <div class="px-6 py-4 bg-blue-700 text-white">
                <h2 class="text-lg font-bold">{{ $campeonato->nome }}</h2>
                <p class="text-sm text-blue-200">
                    {{ $campeonato->data_inicio->format('d/m/Y') }}
                    @if($campeonato->data_inicio != $campeonato->data_fim)
                        — {{ $campeonato->data_fim->format('d/m/Y') }}
                    @endif
                </p>
            </div>

            @php
                $individuais = $campeonato->premiacoes->filter(fn($p) => !$p->isEquipe())->groupBy('tipo');
                $equipe      = $campeonato->premiacoes->filter(fn($p) => $p->isEquipe())->groupBy('tipo');
            @endphp

            {{-- Premiações individuais --}}
            @if($individuais->count())
                <div class="px-6 py-3 border-b bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Individuais</h3>
                </div>
                @foreach($individuais as $tipo => $itens)
                    <div class="px-6 py-3 border-b last:border-b-0">
                        <div class="text-xs font-semibold text-yellow-700 uppercase mb-2">🏆 {{ $tipo }}</div>
                        <div class="space-y-1">
                            @foreach($itens as $p)
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="font-medium">{{ $p->atleta->nome }}</span>
                                    @if($p->observacao)
                                        <span class="text-gray-400 text-xs">— {{ $p->observacao }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Premiações de equipe --}}
            @if($equipe->count())
                <div class="px-6 py-3 border-b bg-gray-50 {{ $individuais->count() ? 'border-t' : '' }}">
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Equipe</h3>
                </div>
                @foreach($equipe as $tipo => $itens)
                    <div class="px-6 py-3 border-b last:border-b-0">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-lg">🏆</span>
                            <span class="font-semibold text-purple-700">{{ $tipo }}</span>
                            @if($itens->first()->observacao)
                                <span class="text-gray-400 text-xs">— {{ $itens->first()->observacao }}</span>
                            @endif
                            <span class="text-xs text-gray-500">· toda a equipe</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
            Nenhuma premiação registrada ainda.
        </div>
    @endforelse
</div>
@endsection
