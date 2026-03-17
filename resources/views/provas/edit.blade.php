@extends('layouts.app')

@section('title', 'Editar Prova')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Prova</h1>
        <a href="{{ route('provas.index') }}" class="text-blue-600 hover:underline text-sm">Voltar</a>
    </div>

    <form action="{{ route('provas.update', $prova) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1">Nome</label>
            <input type="text" name="nome" value="{{ old('nome', $prova->nome) }}" required
                   class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
        </div>
        @if($errors->any())
            <div class="mb-3 text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 font-semibold transition">Salvar</button>
    </form>
</div>
@endsection
