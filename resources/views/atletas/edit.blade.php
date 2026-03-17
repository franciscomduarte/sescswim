@extends('layouts.app')

@section('title', 'Editar Atleta')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Atleta</h1>
        <div class="flex items-center gap-3">
            @if($atleta->codigo_federacao)
                <form action="{{ route('atletas.importar-cbda', $atleta) }}" method="POST"
                      onsubmit="return confirm('Importar dados da CBDA para este atleta? Os campos data de nascimento e sexo serão sobrescritos.')">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white py-1.5 px-4 rounded-md hover:bg-green-700 text-sm font-semibold transition">
                        Importar da CBDA
                    </button>
                </form>
            @endif
            <a href="{{ route('atletas.index') }}" class="text-blue-600 hover:underline text-sm">Voltar</a>
        </div>
    </div>

    <form action="{{ route('atletas.update', $atleta) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Nome</label>
                <input type="text" name="nome" value="{{ old('nome', $atleta->nome) }}" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Data de Nascimento</label>
                <input type="date" name="data_nascimento" value="{{ old('data_nascimento', $atleta->data_nascimento?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Código Federação</label>
                <input type="text" name="codigo_federacao" value="{{ old('codigo_federacao', $atleta->codigo_federacao) }}" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Sexo</label>
                <select name="sexo" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    <option value="">Selecione</option>
                    <option value="feminino" {{ old('sexo', $atleta->sexo) == 'feminino' ? 'selected' : '' }}>Feminino</option>
                    <option value="masculino" {{ old('sexo', $atleta->sexo) == 'masculino' ? 'selected' : '' }}>Masculino</option>
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
</div>
@endsection
