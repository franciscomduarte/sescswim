<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placar ao Vivo – {{ $clube->nome }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $clube->nome }}">
    <link rel="apple-touch-icon" href="/icons/icon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="30">
    <style>
        .medal-ouro   { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .medal-prata  { background: linear-gradient(135deg, #e2e8f0, #94a3b8); }
        .medal-bronze { background: linear-gradient(135deg, #fb923c, #c2410c); }
        @keyframes pulse-soft { 0%,100%{opacity:1} 50%{opacity:.6} }
        .live-dot { animation: pulse-soft 1.8s infinite; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen">

{{-- ──────────── CABEÇALHO ──────────── --}}
<header class="bg-gradient-to-r from-blue-900 to-blue-700 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div class="flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 10c0-1 .5-2 1.5-2.5S7 7 8 8s2.5.5 4 0 3-.5 4 .5 1 2 1 2v6H3v-6z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16h18"/>
            </svg>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight leading-none">{{ $clube->nome }}</h1>
                <p class="text-blue-300 text-xs">Resultados ao Vivo</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm text-blue-200">
            <span class="live-dot w-2 h-2 rounded-full bg-green-400 inline-block"></span>
            <span>Atualiza a cada 30s</span>
            <span class="text-blue-400">·</span>
            <a href="{{ route('resultados.index') }}" class="underline hover:text-white text-xs">Ver estatísticas</a>
        </div>
    </div>
</header>

{{-- ──────────── FILTROS ──────────── --}}
<div class="bg-slate-800 border-b border-slate-700 sticky top-[61px] z-40 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-3">
        <form method="GET" action="{{ route('placar.show', $clube->slug) }}"
              class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">

            {{-- Campeonato --}}
            <div class="flex-1 min-w-0">
                <label class="block text-xs text-slate-400 mb-1 font-medium uppercase tracking-wide">Campeonato</label>
                <select name="campeonato_id" onchange="this.form.submit()"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
                    @foreach($campeonatos as $c)
                        <option value="{{ $c->id }}" @selected($campeonato?->id == $c->id)>
                            {{ $c->nome }}
                            @if($c->data_inicio)({{ $c->data_inicio->format('d/m/Y') }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Sexo --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium uppercase tracking-wide">Sexo</label>
                <div class="flex rounded-lg overflow-hidden border border-slate-600">
                    @foreach([''=>'Todos','masculino'=>'Masculino','feminino'=>'Feminino'] as $val => $label)
                        <a href="{{ route('placar.show', array_merge(['slug' => $clube->slug], request()->only('campeonato_id','busca'), ['sexo'=>$val])) }}"
                           class="px-3 py-2 text-sm font-medium transition
                                  {{ $sexo === $val ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Busca --}}
            <div class="flex-1 min-w-0 max-w-xs">
                <label class="block text-xs text-slate-400 mb-1 font-medium uppercase tracking-wide">Buscar atleta</label>
                <div class="relative">
                    <input type="text" name="busca" value="{{ $busca }}" placeholder="Nome do atleta…"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-slate-400">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </div>
            </div>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-500 text-white rounded-lg px-4 py-2 text-sm font-semibold transition self-end">
                Buscar
            </button>
        </form>
    </div>
</div>

{{-- ──────────── CONTEÚDO ──────────── --}}
<main class="max-w-7xl mx-auto px-4 py-6">

    @if(!$campeonato)
        <div class="text-center py-20 text-slate-400">
            <p class="text-lg">Nenhum campeonato encontrado.</p>
        </div>
    @else

        {{-- Título do campeonato --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white">{{ $campeonato->nome }}</h2>
            @if($campeonato->data_inicio)
                <p class="text-slate-400 text-sm mt-0.5">
                    {{ $campeonato->data_inicio->format('d/m/Y') }}
                    @if($campeonato->data_fim && $campeonato->data_fim != $campeonato->data_inicio)
                        – {{ $campeonato->data_fim->format('d/m/Y') }}
                    @endif
                    <span class="ml-2 bg-slate-700 text-slate-300 text-xs px-2 py-0.5 rounded-full">
                        Piscina {{ $campeonato->piscina }}
                    </span>
                </p>
            @endif
        </div>

        @if($grupos->isEmpty() && $gruposEquipes->isEmpty())
            <div class="text-center py-16 text-slate-400">
                <p class="text-lg font-medium">Nenhum resultado disponível ainda.</p>
                <p class="text-sm mt-1">A página atualiza automaticamente a cada 30 segundos.</p>
            </div>
        @else

            {{-- ──── PROVAS INDIVIDUAIS ──── --}}
            @if($grupos->isNotEmpty())
                @foreach($grupos as $sexoLabel => $porCategoria)
                    @php $isFemininoSexo = $sexoLabel === 'Feminino'; @endphp

                    {{-- Divisor de Sexo --}}
                    <div class="flex items-center gap-3 mb-5 {{ $loop->first ? '' : 'mt-10' }}">
                        <span class="text-lg">{{ $isFemininoSexo ? '👧' : '👦' }}</span>
                        <h3 class="text-lg font-extrabold tracking-tight
                                   {{ $isFemininoSexo ? 'text-pink-400' : 'text-cyan-400' }}">
                            {{ $sexoLabel }}
                        </h3>
                        <div class="flex-1 h-px {{ $isFemininoSexo ? 'bg-pink-800' : 'bg-cyan-800' }}"></div>
                    </div>

                    @foreach($porCategoria as $categoria => $porProva)
                        @php
                            $rotuloCat = \App\Services\CategoriaEsportiva::rotulo($categoria);
                        @endphp

                        {{-- Divisor de Categoria --}}
                        <div class="flex items-center gap-2 mb-3 {{ $loop->first ? '' : 'mt-7' }}">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full
                                         {{ $isFemininoSexo ? 'bg-pink-900/60 text-pink-300 border border-pink-700' : 'bg-cyan-900/60 text-cyan-300 border border-cyan-700' }}">
                                {{ $categoria }}
                            </span>
                            <span class="text-sm font-semibold text-slate-300">{{ $rotuloCat }}</span>
                            <div class="flex-1 h-px bg-slate-700"></div>
                        </div>

                        {{-- Cards das provas desta categoria --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-2">
                            @foreach($porProva as $nomeProva => $resultados)
                                <div class="bg-slate-800 rounded-2xl overflow-hidden shadow-lg border border-slate-700 flex flex-col">

                                    {{-- Cabeçalho do card --}}
                                    <div class="px-4 py-2.5 flex items-center justify-between
                                                {{ $isFemininoSexo ? 'bg-gradient-to-r from-pink-900/60 to-purple-900/60' : 'bg-gradient-to-r from-blue-900/60 to-cyan-900/60' }}">
                                        <div>
                                            <p class="font-extrabold text-base leading-tight">{{ $nomeProva }}</p>
                                            <p class="text-xs mt-0.5 {{ $isFemininoSexo ? 'text-pink-300' : 'text-cyan-300' }}">
                                                {{ $rotuloCat }} · {{ $sexoLabel }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Tabela de resultados --}}
                                    <div class="overflow-x-auto flex-1">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-slate-700 text-xs text-slate-400 uppercase">
                                                    <th class="px-3 py-2 text-center w-10">#</th>
                                                    <th class="px-3 py-2 text-left">Atleta</th>
                                                    <th class="px-3 py-2 text-right font-mono">Tempo</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-700/50">
                                                @foreach($resultados->sortBy('colocacao') as $r)
                                                    <tr class="hover:bg-slate-700/40 transition
                                                               {{ $r->colocacao == 1 ? 'bg-yellow-900/20' : '' }}
                                                               {{ $r->colocacao == 2 ? 'bg-slate-700/20' : '' }}
                                                               {{ $r->colocacao == 3 ? 'bg-orange-900/20' : '' }}">
                                                        <td class="px-3 py-3 text-center">
                                                            @if($r->colocacao == 1)
                                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full medal-ouro text-white font-black text-xs shadow">1°</span>
                                                            @elseif($r->colocacao == 2)
                                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full medal-prata text-slate-800 font-black text-xs shadow">2°</span>
                                                            @elseif($r->colocacao == 3)
                                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full medal-bronze text-white font-black text-xs shadow">3°</span>
                                                            @else
                                                                <span class="text-slate-400 font-semibold text-xs">{{ $r->colocacao }}°</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-3">
                                                            <span class="font-semibold text-white leading-tight">{{ $r->atleta->nome }}</span>
                                                            @if($r->rco)
                                                                <span class="ml-1.5 text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wide">RCO</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-3 text-right">
                                                            <span class="font-mono font-bold text-base
                                                                         {{ $r->colocacao == 1 ? 'text-yellow-400' : 'text-white' }}">
                                                                {{ $r->tempo }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @endforeach
                @endforeach
            @endif

            {{-- ──── REVEZAMENTOS ──── --}}
            @if($gruposEquipes->isNotEmpty())
                <div class="mt-12">
                    <div class="flex items-center gap-3 mb-6">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0"/>
                            </svg>
                            Revezamentos
                        </h3>
                        <div class="flex-1 h-px bg-slate-700"></div>
                    </div>

                    @foreach($gruposEquipes as $modalidade => $porProva)
                        @php
                            $isFemininoEq = $modalidade === 'Feminino';
                            $isMisto      = $modalidade === 'Misto';
                        @endphp

                        <div class="flex items-center gap-3 mb-4 {{ $loop->first ? '' : 'mt-8' }}">
                            <span class="text-lg">{{ $isMisto ? '🤝' : ($isFemininoEq ? '👧' : '👦') }}</span>
                            <span class="text-base font-extrabold {{ $isMisto ? 'text-purple-400' : ($isFemininoEq ? 'text-pink-400' : 'text-cyan-400') }}">
                                {{ $modalidade }}
                            </span>
                            <div class="flex-1 h-px {{ $isMisto ? 'bg-purple-800' : ($isFemininoEq ? 'bg-pink-800' : 'bg-cyan-800') }}"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-2">
                            @foreach($porProva as $nomeProva => $equipes)
                                <div class="bg-slate-800 rounded-2xl overflow-hidden shadow-lg border border-slate-700 flex flex-col">
                                    <div class="px-4 py-2.5 flex items-center justify-between
                                                {{ $isMisto ? 'bg-gradient-to-r from-purple-900/60 to-indigo-900/60'
                                                   : ($isFemininoEq ? 'bg-gradient-to-r from-pink-900/60 to-purple-900/60'
                                                                    : 'bg-gradient-to-r from-blue-900/60 to-cyan-900/60') }}">
                                        <div>
                                            <p class="font-extrabold text-base leading-tight">{{ $nomeProva }}</p>
                                            <p class="text-xs mt-0.5 {{ $isMisto ? 'text-purple-300' : ($isFemininoEq ? 'text-pink-300' : 'text-cyan-300') }}">
                                                {{ $modalidade }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto flex-1">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-slate-700 text-xs text-slate-400 uppercase">
                                                    <th class="px-3 py-2 text-center w-10">#</th>
                                                    <th class="px-3 py-2 text-left">Equipe</th>
                                                    <th class="px-3 py-2 text-right font-mono">Tempo</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-700/50">
                                                @foreach($equipes->sortBy('colocacao') as $equipe)
                                                    <tr class="hover:bg-slate-700/40 transition
                                                               {{ $equipe->colocacao == 1 ? 'bg-yellow-900/20' : '' }}
                                                               {{ $equipe->colocacao == 2 ? 'bg-slate-700/20' : '' }}
                                                               {{ $equipe->colocacao == 3 ? 'bg-orange-900/20' : '' }}">
                                                        <td class="px-3 py-3 text-center">
                                                            @if($equipe->colocacao == 1)
                                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full medal-ouro text-white font-black text-xs shadow">1°</span>
                                                            @elseif($equipe->colocacao == 2)
                                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full medal-prata text-slate-800 font-black text-xs shadow">2°</span>
                                                            @elseif($equipe->colocacao == 3)
                                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full medal-bronze text-white font-black text-xs shadow">3°</span>
                                                            @else
                                                                <span class="text-slate-400 font-semibold text-xs">{{ $equipe->colocacao }}°</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-3">
                                                            <p class="font-semibold text-white">{{ $equipe->nome }}</p>
                                                            @if($equipe->membros->isNotEmpty())
                                                                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                                                    {{ $equipe->membros->map(fn($m) => $m->atleta->nome)->join(' · ') }}
                                                                </p>
                                                            @endif
                                                            @if($equipe->rco)
                                                                <span class="text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wide">RCO</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-3 text-right">
                                                            <span class="font-mono font-bold text-base
                                                                         {{ $equipe->colocacao == 1 ? 'text-yellow-400' : 'text-white' }}">
                                                                {{ $equipe->tempo }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

        @endif
    @endif
</main>

<footer class="mt-10 py-5 border-t border-slate-700 text-center text-slate-500 text-xs">
    <p>{{ $clube->nome }} · Resultados ao Vivo</p>
    <p class="mt-1">Página atualiza automaticamente a cada 30 segundos</p>
</footer>

<script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</body>
</html>
