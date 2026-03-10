@extends('layouts.app')

@section('title', 'Importação de Resultados')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Importação de Resultados</h1>

    @if(session('resultado_importacao'))
        @php $res = session('resultado_importacao'); @endphp
        <div class="mb-6 p-4 rounded-lg {{ $res['sucesso'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <h3 class="font-bold text-lg mb-2">
                {{ $res['sucesso'] ? 'Importação concluída' : 'Erro na importação' }}
            </h3>
            @if(isset($res['erro']))
                <p class="text-red-600">{{ $res['erro'] }}</p>
            @endif
            <div class="grid grid-cols-3 gap-4 mt-3">
                <div class="text-center p-3 bg-white rounded shadow-sm">
                    <div class="text-2xl font-bold text-gray-700">{{ $res['total'] }}</div>
                    <div class="text-sm text-gray-500">Total</div>
                </div>
                <div class="text-center p-3 bg-white rounded shadow-sm">
                    <div class="text-2xl font-bold text-green-600">{{ $res['importados'] }}</div>
                    <div class="text-sm text-gray-500">Importados</div>
                </div>
                <div class="text-center p-3 bg-white rounded shadow-sm">
                    <div class="text-2xl font-bold text-yellow-600">{{ $res['ignorados'] }}</div>
                    <div class="text-sm text-gray-500">Ignorados</div>
                </div>
            </div>
            @if(!empty($res['erros']))
                <div class="mt-4">
                    <h4 class="font-semibold text-red-600 mb-1">Erros:</h4>
                    <ul class="text-sm text-red-500 list-disc list-inside">
                        @foreach($res['erros'] as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <form action="{{ route('importacao.importar') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Campeonato existente</label>
            <select name="campeonato_id" id="campeonato_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                <option value="">-- Criar novo campeonato --</option>
                @foreach($campeonatos as $c)
                    <option value="{{ $c->id }}">{{ $c->nome }} ({{ $c->piscina }})</option>
                @endforeach
            </select>
        </div>

        <div id="novo-campeonato-fields" class="space-y-4 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-semibold text-blue-700">Novo Campeonato</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input type="text" name="novo_campeonato" class="w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="Ex: Torneio Festival de Águas 2024">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                    <input type="date" name="data_inicio" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                    <input type="date" name="data_fim" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Piscina</label>
            <select name="piscina" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                <option value="25m">25 metros</option>
                <option value="50m">50 metros</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo JSON</label>
            <input type="file" name="arquivo" accept=".json,.txt" required class="w-full border border-gray-300 rounded-md shadow-sm p-2 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-500 mt-1">Formatos aceitos: .json, .txt</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded p-3">
                <ul class="text-sm text-red-600 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 font-semibold transition">
            Importar Resultados
        </button>
    </form>
</div>

@push('scripts')
<script>
    const select = document.getElementById('campeonato_id');
    const novoFields = document.getElementById('novo-campeonato-fields');

    function toggleNovoCampeonato() {
        novoFields.style.display = select.value ? 'none' : 'block';
    }

    select.addEventListener('change', toggleNovoCampeonato);
    toggleNovoCampeonato();
</script>
@endpush
@endsection
