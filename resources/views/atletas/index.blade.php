@extends('layouts.app')

@section('title', 'Atletas')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Atletas</h1>
    </div>

    <form action="{{ route('atletas.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 mb-6">
        @csrf
        <h2 class="font-semibold text-lg mb-4">Novo Atleta</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Nome</label>
                <input type="text" name="nome" value="{{ old('nome') }}" placeholder="Nome completo" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Data de Nascimento</label>
                <input type="date" name="data_nascimento" value="{{ old('data_nascimento') }}" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Registro</label>
                <input type="text" name="codigo_federacao" value="{{ old('codigo_federacao') }}" placeholder="Registro" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Sexo</label>
                <select name="sexo" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    <option value="">Selecione</option>
                    <option value="feminino" {{ old('sexo') == 'feminino' ? 'selected' : '' }}>Feminino</option>
                    <option value="masculino" {{ old('sexo') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 font-semibold transition">Cadastrar</button>
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

    <div class="bg-white rounded-lg shadow overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nascimento</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registro</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sexo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscrições</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resultados</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($atletas as $atleta)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap font-medium">
                            <a href="{{ route('atletas.edit', $atleta) }}" class="text-blue-700 hover:underline">{{ $atleta->nome }}</a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $atleta->data_nascimento ? $atleta->data_nascimento->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $atleta->codigo_federacao ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($atleta->sexo)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $atleta->sexo == 'feminino' ? 'bg-pink-100 text-pink-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($atleta->sexo) }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        @php
                            $cat = $atleta->categoria();
                            $catCor = match($cat) {
                                'MINIMIRIM' => 'bg-yellow-100 text-yellow-800',
                                'PREMIRIM'  => 'bg-amber-100 text-amber-800',
                                'MIR1'      => 'bg-lime-100 text-lime-800',
                                'MIR2'      => 'bg-green-100 text-green-800',
                                'PET1'      => 'bg-teal-100 text-teal-800',
                                'PET2'      => 'bg-cyan-100 text-cyan-800',
                                'INF1'      => 'bg-sky-100 text-sky-800',
                                'INF2'      => 'bg-blue-100 text-blue-800',
                                'JUV1'      => 'bg-violet-100 text-violet-800',
                                'JUV2'      => 'bg-purple-100 text-purple-800',
                                'JR1'       => 'bg-pink-100 text-pink-800',
                                'JR2'       => 'bg-rose-100 text-rose-800',
                                'Senior'    => 'bg-orange-100 text-orange-800',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            @if($atleta->data_nascimento)
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $catCor }}"
                                      title="{{ $cat }}">
                                    {{ \App\Services\CategoriaEsportiva::rotulo($cat) }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $atleta->inscricoes_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $atleta->resultados_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('atletas.edit', $atleta) }}" class="text-gray-600 hover:underline">Editar</a>
                            <form action="{{ route('atletas.destroy', $atleta) }}" method="POST" class="inline" onsubmit="return confirm('Excluir atleta e todos os dados relacionados?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">Nenhum atleta cadastrado</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
