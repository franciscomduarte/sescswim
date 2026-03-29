<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolução do Atleta – {{ $clube->nome }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $clube->nome }}">
    <link rel="apple-touch-icon" href="/icons/icon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { background: #0f172a; }
        .card { background: #1e293b; border: 1px solid #334155; }
        .medal-ouro   { color: #fbbf24; }
        .medal-prata  { color: #94a3b8; }
        .medal-bronze { color: #b45309; }
    </style>
</head>
<body class="min-h-screen text-white">

    {{-- NAV --}}
    <nav class="bg-blue-900 border-b border-blue-800 px-4 py-3 flex items-center justify-between">
        <a href="/" class="text-lg font-bold tracking-tight">{{ $clube->nome }}</a>
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('placar.show', $clube->slug) }}" class="text-blue-300 hover:text-white transition">Placar</a>
            <span class="text-blue-700">|</span>
            <span class="text-white font-semibold">Evolução</span>
        </div>
    </nav>

    {{-- STICKY SELECTOR --}}
    <div class="sticky top-0 z-20 bg-slate-900/95 backdrop-blur border-b border-slate-700 px-4 py-3">
        <form method="GET" action="{{ route('evolucao.show', $clube->slug) }}">
            <label for="atleta_id" class="block text-xs text-slate-400 mb-1 font-medium uppercase tracking-wide">Selecionar atleta</label>
            <select id="atleta_id" name="atleta_id"
                    onchange="this.form.submit()"
                    class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                <option value="">— Escolha um atleta —</option>
                @foreach($atletas as $a)
                    <option value="{{ $a->id }}" {{ $atleta && $atleta->id == $a->id ? 'selected' : '' }}>
                        {{ $a->nome }}
                        @if($a->sexo) ({{ $a->sexo == 'feminino' ? 'F' : 'M' }}) @endif
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6">

        @if(!$atleta)
            {{-- EMPTY STATE --}}
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-full bg-blue-900/50 border-2 border-blue-700 flex items-center justify-center mb-5">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-200 mb-2">Selecione um atleta</h2>
                <p class="text-slate-400 text-sm max-w-xs">Escolha um atleta acima para visualizar a evolução dos tempos ao longo dos campeonatos.</p>
            </div>

        @else
            @php
                $cat       = $atleta->data_nascimento ? \App\Services\CategoriaEsportiva::calcular($atleta->data_nascimento) : null;
                $catRotulo = $cat ? \App\Services\CategoriaEsportiva::rotulo($cat) : null;
                $iniciais  = collect(explode(' ', $atleta->nome))->filter()->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->join('');
            @endphp

            {{-- PROFILE CARD --}}
            <div class="card rounded-2xl p-5 mb-6 flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-full bg-blue-700 flex items-center justify-center text-xl font-bold select-none">
                    {{ $iniciais }}
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-lg font-bold truncate">{{ $atleta->nome }}</h1>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @if($atleta->sexo)
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $atleta->sexo == 'feminino' ? 'bg-pink-900/60 text-pink-300' : 'bg-blue-900/60 text-blue-300' }}">
                                {{ ucfirst($atleta->sexo) }}
                            </span>
                        @endif
                        @if($catRotulo)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-900/60 text-amber-300">{{ $catRotulo }}</span>
                        @endif
                        @if($atleta->data_nascimento)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-700 text-slate-300">
                                Nasc. {{ $atleta->data_nascimento->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if($evolucao->isEmpty())
                <div class="text-center py-16 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="font-medium">Nenhum resultado encontrado</p>
                    <p class="text-sm mt-1">Este atleta ainda não possui tempos registrados.</p>
                </div>

            @else
                {{-- STATS ROW --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                    @php $stats = [
                        ['label' => 'Campeonatos', 'value' => $resumo['campeonatos'], 'icon' => '🏆'],
                        ['label' => 'Provas',       'value' => $resumo['provas'],       'icon' => '🏊'],
                        ['label' => 'Medalhas',     'value' => $resumo['medalhas'],     'icon' => '🥇'],
                        ['label' => 'Melhor colocação', 'value' => $resumo['melhor_coloc'] ? $resumo['melhor_coloc'].'º' : '—', 'icon' => '🎯'],
                    ]; @endphp
                    @foreach($stats as $s)
                        <div class="card rounded-xl p-4 text-center">
                            <div class="text-2xl mb-1">{{ $s['icon'] }}</div>
                            <div class="text-2xl font-bold text-white">{{ $s['value'] ?? '—' }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $s['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                @if($resumo['medalhas'] > 0)
                    <div class="card rounded-xl px-5 py-3 mb-6 flex flex-wrap gap-4 text-sm">
                        @if($resumo['ouros'] > 0)
                            <span class="medal-ouro font-semibold">🥇 {{ $resumo['ouros'] }} ouro{{ $resumo['ouros'] > 1 ? 's' : '' }}</span>
                        @endif
                        @if($resumo['pratas'] > 0)
                            <span class="medal-prata font-semibold">🥈 {{ $resumo['pratas'] }} prata{{ $resumo['pratas'] > 1 ? 's' : '' }}</span>
                        @endif
                        @if($resumo['bronzes'] > 0)
                            <span class="medal-bronze font-semibold">🥉 {{ $resumo['bronzes'] }} bronze{{ $resumo['bronzes'] > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                @endif

                {{-- PROVA CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($evolucao as $idx => $grupo)
                        @php
                            $melhorFormatado = $grupo['melhor']->tempo ?? '—';
                            $delta           = $grupo['delta'];
                            $count           = count($grupo['resultados']);
                        @endphp
                        <div class="card rounded-2xl overflow-hidden">
                            {{-- Card header --}}
                            <div class="px-4 pt-4 pb-2 flex items-start justify-between gap-2">
                                <h3 class="font-bold text-base leading-tight">{{ $grupo['label'] }}</h3>
                                @if($delta !== null)
                                    @if($delta > 0)
                                        <span class="flex-shrink-0 text-xs px-2 py-1 rounded-full bg-green-900/60 text-green-300 font-semibold whitespace-nowrap">
                                            ▼ {{ number_format(abs($delta), 2, ',', '') }}s mais rápido
                                        </span>
                                    @elseif($delta < 0)
                                        <span class="flex-shrink-0 text-xs px-2 py-1 rounded-full bg-red-900/60 text-red-300 font-semibold whitespace-nowrap">
                                            ▲ {{ number_format(abs($delta), 2, ',', '') }}s mais lento
                                        </span>
                                    @else
                                        <span class="flex-shrink-0 text-xs px-2 py-1 rounded-full bg-slate-700 text-slate-300 font-semibold whitespace-nowrap">= sem alteração</span>
                                    @endif
                                @endif
                            </div>

                            {{-- Best time --}}
                            <div class="px-4 pb-3">
                                <p class="text-xs text-slate-400">Melhor tempo</p>
                                <p class="text-2xl font-mono font-bold text-blue-300">{{ $melhorFormatado }}</p>
                                @if($grupo['melhor'])
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $grupo['melhor']->campeonato->nome }}</p>
                                @endif
                            </div>

                            {{-- Chart --}}
                            @if($count > 1)
                                <div class="px-3 pb-2">
                                    <canvas id="chart-{{ $idx }}"
                                            data-labels="{{ $grupo['labels_json'] }}"
                                            data-times="{{ $grupo['times_json'] }}"
                                            height="160"></canvas>
                                </div>
                            @else
                                <div class="px-4 pb-3 text-xs text-slate-500 italic">Apenas 1 resultado — gráfico disponível com 2 ou mais participações.</div>
                            @endif

                            {{-- Results table --}}
                            <div class="border-t border-slate-700">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="text-slate-500 border-b border-slate-700">
                                            <th class="px-3 py-2 text-left font-medium">Campeonato</th>
                                            <th class="px-3 py-2 text-center font-medium">Tempo</th>
                                            <th class="px-3 py-2 text-center font-medium">Col.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($grupo['resultados'] as $r)
                                            @php
                                                $medalIcon = match($r->medalha ?? '') {
                                                    'Ouro'   => '🥇',
                                                    'Prata'  => '🥈',
                                                    'Bronze' => '🥉',
                                                    default  => '',
                                                };
                                                $isBest = $grupo['melhor'] && $r->id === $grupo['melhor']->id;
                                            @endphp
                                            <tr class="border-b border-slate-800 last:border-0 {{ $isBest ? 'bg-blue-900/20' : '' }}">
                                                <td class="px-3 py-2 text-slate-300">
                                                    <div class="truncate max-w-[140px]" title="{{ $r->campeonato->nome }}">{{ $r->campeonato->nome }}</div>
                                                    <div class="text-slate-500">{{ $r->campeonato->data_inicio->format('d/m/Y') }}</div>
                                                </td>
                                                <td class="px-3 py-2 text-center font-mono {{ $isBest ? 'text-blue-300 font-bold' : 'text-slate-300' }}">
                                                    {{ $r->tempo }}
                                                    @if($isBest) <span class="text-blue-400 text-xs">★</span> @endif
                                                </td>
                                                <td class="px-3 py-2 text-center text-slate-300">
                                                    {{ $r->colocacao ? $r->colocacao.'º' : '—' }}
                                                    {{ $medalIcon }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- Footer --}}
    <div class="text-center text-xs text-slate-600 py-8">
        {{ $clube->nome }} &mdash; Evolução de Atletas
    </div>

    <script>
    (function () {
        function secToTime(sec) {
            const s = Math.abs(sec);
            const m = Math.floor(s / 60);
            const rem = (s - m * 60).toFixed(2).padStart(5, '0');
            return m > 0 ? m + ':' + rem : rem + 's';
        }

        document.querySelectorAll('canvas[data-labels]').forEach(function (canvas) {
            const labels = JSON.parse(canvas.dataset.labels);
            const times  = JSON.parse(canvas.dataset.times);

            const minVal = Math.min(...times);
            const maxVal = Math.max(...times);
            const pad    = (maxVal - minVal) * 0.15 || 1;

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: times,
                        borderColor: '#60a5fa',
                        backgroundColor: 'rgba(96,165,250,0.12)',
                        pointBackgroundColor: times.map(function (t) {
                            return t === minVal ? '#34d399' : '#60a5fa';
                        }),
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.35,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ' ' + secToTime(ctx.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#64748b',
                                font: { size: 10 },
                                maxRotation: 30,
                                callback: function (val, idx) {
                                    const lbl = this.getLabelForValue(val);
                                    return lbl.length > 14 ? lbl.slice(0, 13) + '…' : lbl;
                                }
                            },
                            grid: { color: '#1e293b' }
                        },
                        y: {
                            reverse: true,
                            min: minVal - pad,
                            max: maxVal + pad,
                            ticks: {
                                color: '#64748b',
                                font: { size: 10 },
                                callback: function (val) { return secToTime(val); }
                            },
                            grid: { color: '#334155' }
                        }
                    }
                }
            });
        });
    })();
    </script>
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</body>
</html>
