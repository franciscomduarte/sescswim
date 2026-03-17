@extends('layouts.app')

@section('title', 'Nova Equipe — ' . $campeonato->nome)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Nova Equipe de Revezamento</h1>
        <a href="{{ route('campeonatos.edit', $campeonato) }}" class="text-blue-600 hover:underline text-sm">Voltar</a>
    </div>

    <form action="{{ route('campeonatos.equipes.store', $campeonato) }}" method="POST"
          class="bg-white rounded-lg shadow p-6 space-y-4"
          x-data="{ tipo: '{{ old('tipo', 'Livre') }}' }">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Nome da Equipe</label>
                <input type="text" name="nome" value="{{ old('nome') }}" required placeholder="Ex: Equipe A"
                       class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Modalidade</label>
                <select name="modalidade" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    <option value="Masculino" {{ old('modalidade') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="Feminino"  {{ old('modalidade') == 'Feminino'  ? 'selected' : '' }}>Feminino</option>
                    <option value="Misto"     {{ old('modalidade') == 'Misto'     ? 'selected' : '' }}>Misto</option>
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Tipo</label>
                <select name="tipo" x-model="tipo" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    <option value="Livre"  {{ old('tipo', 'Livre') == 'Livre'  ? 'selected' : '' }}>Livre</option>
                    <option value="Medley" {{ old('tipo') == 'Medley' ? 'selected' : '' }}>Medley</option>
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Distância (por atleta)</label>
                <select name="distancia_id" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    @foreach($distancias as $d)
                        <option value="{{ $d->id }}" {{ old('distancia_id') == $d->id ? 'selected' : '' }}>{{ $d->metragem }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Ordem de execução</label>
                <input type="number" name="ordem_execucao" value="{{ old('ordem_execucao') }}" min="1"
                       class="w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="Nº na ordem">
            </div>
        </div>

        {{-- Atletas --}}
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-700 mb-3">Atletas (posição 1 → 4)</h3>
            <div class="space-y-3">
                @foreach([1,2,3,4] as $pos)
                    <div class="flex items-center gap-3">
                        <div class="w-24 text-sm text-gray-600 font-medium shrink-0">
                            Posição {{ $pos }}
                            <span x-show="tipo === 'Medley'" class="block text-xs text-blue-600 font-normal">
                                {{ [1=>'Borboleta',2=>'Costas',3=>'Peito',4=>'Livre'][$pos] }}
                            </span>
                        </div>
                        <select name="atleta_{{ $pos }}" required
                                class="flex-1 border-gray-300 rounded-md shadow-sm p-2 border text-sm">
                            <option value="">Selecione o atleta</option>
                            @foreach($atletas as $atleta)
                                <option value="{{ $atleta->id }}" {{ old("atleta_{$pos}") == $atleta->id ? 'selected' : '' }}>
                                    {{ $atleta->nome }}
                                    @if($atleta->sexo) ({{ ucfirst($atleta->sexo) }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        @if($errors->any())
            <div class="text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 font-semibold transition">
                Cadastrar Equipe
            </button>
        </div>
    </form>
</div>
@endsection
