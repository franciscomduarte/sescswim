<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora World Aquatics Points</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0f172a; }
        .card { background: #1e293b; border: 1px solid #334155; }
        .input-base {
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            border-radius: 0.5rem;
            padding: 0.625rem 0.75rem;
            width: 100%;
            font-size: 0.875rem;
            outline: none;
            transition: border-color .15s;
        }
        .input-base:focus { border-color: #3b82f6; }
        .tab-active   { background: #1d4ed8; color: white; }
        .tab-inactive { background: #1e293b; color: #94a3b8; }
        .tab-inactive:hover { background: #334155; color: white; }

        /* Gauge */
        .gauge-ring { transform-origin: center; transition: stroke-dashoffset .6s cubic-bezier(.4,0,.2,1); }

        /* Points color zones */
        .zone-world  { color: #fbbf24; }
        .zone-elite  { color: #a78bfa; }
        .zone-high   { color: #60a5fa; }
        .zone-good   { color: #34d399; }
        .zone-avg    { color: #94a3b8; }
        .zone-low    { color: #f87171; }
    </style>
</head>
<body class="min-h-screen text-white">

    {{-- NAV --}}
    <nav class="px-4 py-4 flex items-center justify-between max-w-3xl mx-auto">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="/icons/icon.svg" class="w-7 h-7 rounded" alt="">
            <span class="font-bold text-base tracking-tight">SESC Swim</span>
        </a>
        <a href="{{ route('home') }}" class="text-slate-400 hover:text-white text-sm transition">← Início</a>
    </nav>

    <div class="max-w-3xl mx-auto px-4 pb-16">

        {{-- HEADER --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 bg-blue-900/40 border border-blue-800 rounded-full px-4 py-1.5 text-xs text-blue-300 mb-4">
                🌊 World Aquatics Points
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold mb-2">Calculadora de Pontuação</h1>
            <p class="text-slate-400 text-sm max-w-md mx-auto">
                Calcule seus pontos World Aquatics a partir do seu tempo. A fórmula é
                <span class="font-mono text-blue-300">P = ⌊1000 × (B ÷ T)³⌋</span>,
                onde B é o tempo base e T é o seu tempo.
            </p>
        </div>

        {{-- FORM CARD --}}
        <div class="card rounded-2xl p-5 sm:p-7 mb-6">

            {{-- Piscina --}}
            <div class="mb-5">
                <label class="block text-xs text-slate-400 uppercase tracking-wide mb-2">Piscina</label>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="setPiscina('scm')" id="btn-scm"
                            class="tab-active py-2.5 rounded-xl text-sm font-semibold transition">
                        25m (Curta)
                    </button>
                    <button onclick="setPiscina('lcm')" id="btn-lcm"
                            class="tab-inactive py-2.5 rounded-xl text-sm font-semibold transition">
                        50m (Longa)
                    </button>
                </div>
            </div>

            {{-- Sexo --}}
            <div class="mb-5">
                <label class="block text-xs text-slate-400 uppercase tracking-wide mb-2">Sexo</label>
                <div class="grid grid-cols-3 gap-2">
                    <button onclick="setSexo('M')" id="btn-M"
                            class="tab-active py-2.5 rounded-xl text-sm font-semibold transition">
                        Masculino
                    </button>
                    <button onclick="setSexo('F')" id="btn-F"
                            class="tab-inactive py-2.5 rounded-xl text-sm font-semibold transition">
                        Feminino
                    </button>
                    <button onclick="setSexo('X')" id="btn-X"
                            class="tab-inactive py-2.5 rounded-xl text-sm font-semibold transition">
                        Misto
                    </button>
                </div>
            </div>

            {{-- Prova --}}
            <div class="mb-5">
                <label class="block text-xs text-slate-400 uppercase tracking-wide mb-2">Prova</label>
                <select id="prova-select" onchange="onProvaChange()" class="input-base appearance-none">
                    <option value="">— Selecione a prova —</option>
                </select>
            </div>

            {{-- Tempo --}}
            <div class="mb-6">
                <label class="block text-xs text-slate-400 uppercase tracking-wide mb-2">
                    Seu tempo
                    <span class="normal-case text-slate-500 ml-1">(mm:ss.cc  ou  ss.cc)</span>
                </label>
                <input id="tempo-input" type="text" placeholder="0:00.00"
                       inputmode="numeric" maxlength="8" autocomplete="off"
                       class="input-base text-lg font-mono tracking-widest">
                <p id="tempo-erro" class="text-red-400 text-xs mt-1 hidden">Formato inválido. Use mm:ss.cc ou ss.cc</p>
            </div>

            {{-- RESULTADO --}}
            <div id="resultado" class="hidden">
                <div class="border-t border-slate-700 pt-6">
                    <div class="flex flex-col sm:flex-row items-center gap-6">

                        {{-- Gauge SVG --}}
                        <div class="flex-shrink-0 relative w-36 h-36">
                            <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                                {{-- Track --}}
                                <circle cx="60" cy="60" r="50"
                                        fill="none" stroke="#1e293b" stroke-width="12"
                                        stroke-dasharray="314.16" stroke-dashoffset="0"
                                        stroke-linecap="round"/>
                                {{-- Progress --}}
                                <circle id="gauge-arc" cx="60" cy="60" r="50"
                                        fill="none" stroke="#3b82f6" stroke-width="12"
                                        stroke-dasharray="314.16" stroke-dashoffset="314.16"
                                        stroke-linecap="round"
                                        class="gauge-ring"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center rotate-0">
                                <span id="pts-value" class="text-3xl font-extrabold leading-none">0</span>
                                <span class="text-xs text-slate-400 mt-0.5">pontos</span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 w-full">
                            <div id="zona-badge" class="inline-block text-xs font-bold px-3 py-1 rounded-full mb-3"></div>

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="bg-slate-900 rounded-xl p-3">
                                    <p class="text-xs text-slate-500 mb-0.5">Tempo base (B)</p>
                                    <p id="base-fmt" class="font-mono font-bold text-slate-200">—</p>
                                </div>
                                <div class="bg-slate-900 rounded-xl p-3">
                                    <p class="text-xs text-slate-500 mb-0.5">Seu tempo (T)</p>
                                    <p id="tempo-fmt" class="font-mono font-bold text-slate-200">—</p>
                                </div>
                                <div class="bg-slate-900 rounded-xl p-3">
                                    <p class="text-xs text-slate-500 mb-0.5">Diferença</p>
                                    <p id="dif-fmt" class="font-mono font-bold">—</p>
                                </div>
                                <div class="bg-slate-900 rounded-xl p-3">
                                    <p class="text-xs text-slate-500 mb-0.5">% do recorde</p>
                                    <p id="pct-fmt" class="font-mono font-bold text-slate-200">—</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- REFERÊNCIA DE ZONAS --}}
        <div class="card rounded-2xl p-5">
            <h2 class="text-sm font-bold text-slate-300 mb-4 uppercase tracking-wide">Referência de zonas</h2>
            <div class="space-y-2">
                @php $zonas = [
                    ['≥ 1000', 'Recorde Mundial', '#fbbf24', '#451a03'],
                    ['900 – 999', 'Elite Mundial', '#a78bfa', '#2e1065'],
                    ['800 – 899', 'Alto nível', '#60a5fa', '#1e3a8a'],
                    ['700 – 799', 'Muito bom', '#34d399', '#064e3b'],
                    ['500 – 699', 'Intermediário', '#94a3b8', '#1e293b'],
                    ['< 500',    'Desenvolvimento', '#f87171', '#450a0a'],
                ]; @endphp
                @foreach($zonas as [$faixa, $label, $cor, $bg])
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-20 font-mono text-xs font-bold text-right flex-shrink-0"
                              style="color: {{ $cor }}">{{ $faixa }}</span>
                        <div class="flex-1 rounded-lg px-3 py-1.5"
                             style="background: {{ $bg }}20; border: 1px solid {{ $cor }}30">
                            <span style="color: {{ $cor }}">{{ $label }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- LINK TEMPOS BASE --}}
        <div class="text-center mt-6 space-y-1">
            <button onclick="abrirTemposBase()"
                    class="text-blue-400 hover:text-blue-300 text-sm underline underline-offset-2 transition">
                Consultar tabela completa de tempos base →
            </button>
            <p class="text-slate-600 text-xs">
                World Aquatics 2025/2026 · SCM válido até 31/08/2026 · LCM válido até 31/12/2026
            </p>
        </div>
    </div>

    {{-- MODAL TEMPOS BASE --}}
    <div id="modal-tempos" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="fecharTemposBase()"></div>
        <div class="relative w-full max-w-2xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

            {{-- Header fixo --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700 flex-shrink-0">
                <div>
                    <h3 class="font-bold text-base">Tempos Base – World Aquatics</h3>
                    <p class="text-xs text-slate-400 mt-0.5">P = ⌊1000 × (B ÷ T)³⌋ · 1000 pts = tempo base</p>
                </div>
                <button onclick="fecharTemposBase()" class="text-slate-500 hover:text-white transition ml-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Filtros --}}
            <div class="px-5 py-3 border-b border-slate-800 flex flex-wrap gap-2 flex-shrink-0">
                <div class="flex rounded-lg overflow-hidden border border-slate-700 text-xs">
                    <button onclick="setModalPiscina('scm')" id="mpb-scm"
                            class="px-3 py-1.5 font-semibold tab-active transition">25m</button>
                    <button onclick="setModalPiscina('lcm')" id="mpb-lcm"
                            class="px-3 py-1.5 font-semibold tab-inactive transition">50m</button>
                </div>
                <div class="flex rounded-lg overflow-hidden border border-slate-700 text-xs">
                    <button onclick="setModalSexo('M')" id="msb-M"
                            class="px-3 py-1.5 font-semibold tab-active transition">Masculino</button>
                    <button onclick="setModalSexo('F')" id="msb-F"
                            class="px-3 py-1.5 font-semibold tab-inactive transition">Feminino</button>
                    <button onclick="setModalSexo('X')" id="msb-X"
                            class="px-3 py-1.5 font-semibold tab-inactive transition">Misto</button>
                </div>
            </div>

            {{-- Tabela scrollável --}}
            <div class="overflow-y-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-900 border-b border-slate-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs text-slate-400 font-medium uppercase tracking-wide">Prova</th>
                            <th class="px-5 py-3 text-right text-xs text-slate-400 font-medium uppercase tracking-wide">Tempo base</th>
                            <th class="px-5 py-3 text-right text-xs text-slate-400 font-medium uppercase tracking-wide">Em segundos</th>
                        </tr>
                    </thead>
                    <tbody id="modal-tbody">
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-800 text-xs text-slate-600 flex-shrink-0">
                Fonte: World Aquatics Points – Base Times SCM 2025 / LCM 2026
            </div>
        </div>
    </div>

    <script>
    // ─── Base times ──────────────────────────────────────────────────────────────
    // Estrutura: BASES[piscina][sexo][prova] = segundos
    const BASES = {
        scm: {
            M: {
                '50m Livre':              19.90,
                '100m Livre':             44.84,
                '200m Livre':             98.61,
                '400m Livre':            212.25,
                '800m Livre':            440.46,
                '1500m Livre':           846.88,
                '50m Costas':             22.11,
                '100m Costas':            48.33,
                '200m Costas':           105.63,
                '50m Peito':              24.95,
                '100m Peito':             55.28,
                '200m Peito':            120.16,
                '50m Borboleta':          21.32,
                '100m Borboleta':         47.71,
                '200m Borboleta':        106.85,
                '100m Medley':            49.28,
                '200m Medley':           108.88,
                '400m Medley':           234.81,
                '4×50m Livre (Revez.)':   81.80,
                '4×100m Livre (Revez.)': 181.66,
                '4×200m Livre (Revez.)': 400.51,
                '4×50m Medley (Revez.)':  89.72,
                '4×100m Medley (Revez.)':198.68,
            },
            F: {
                '50m Livre':              22.83,
                '100m Livre':             50.25,
                '200m Livre':            110.31,
                '400m Livre':            230.25,
                '800m Livre':            477.42,
                '1500m Livre':           908.24,
                '50m Costas':             25.23,
                '100m Costas':            54.02,
                '200m Costas':           118.04,
                '50m Peito':              28.37,
                '100m Peito':             62.36,
                '200m Peito':            132.50,
                '50m Borboleta':          23.94,
                '100m Borboleta':         52.71,
                '200m Borboleta':        119.32,
                '100m Medley':            55.11,
                '200m Medley':           121.63,
                '400m Medley':           255.48,
                '4×50m Livre (Revez.)':   92.50,
                '4×100m Livre (Revez.)': 205.01,
                '4×200m Livre (Revez.)': 450.13,
                '4×50m Medley (Revez.)': 102.35,
                '4×100m Medley (Revez.)':220.41,
            },
            X: {
                '4×50m Livre (Revez. Misto)':   87.33,
                '4×50m Medley (Revez. Misto)':  95.15,
            },
        },
        lcm: {
            M: {
                '50m Livre':              20.91,
                '100m Livre':             46.40,
                '200m Livre':            102.00,
                '400m Livre':            219.96,
                '800m Livre':            452.12,
                '1500m Livre':           870.67,
                '50m Costas':             23.55,
                '100m Costas':            51.60,
                '200m Costas':           111.92,
                '50m Peito':              25.95,
                '100m Peito':             56.88,
                '200m Peito':            125.48,
                '50m Borboleta':          22.27,
                '100m Borboleta':         49.45,
                '200m Borboleta':        110.34,
                '200m Medley':           112.69,
                '400m Medley':           242.50,
                '4×100m Livre (Revez.)': 188.24,
                '4×200m Livre (Revez.)': 418.55,
                '4×100m Medley (Revez.)':206.78,
            },
            F: {
                '50m Livre':              23.61,
                '100m Livre':             51.71,
                '200m Livre':            112.23,
                '400m Livre':            234.18,
                '800m Livre':            484.12,
                '1500m Livre':           920.48,
                '50m Costas':             26.86,
                '100m Costas':            57.13,
                '200m Costas':           123.14,
                '50m Peito':              29.16,
                '100m Peito':             64.13,
                '200m Peito':            137.55,
                '50m Borboleta':          24.43,
                '100m Borboleta':         54.60,
                '200m Borboleta':        121.81,
                '200m Medley':           125.70,
                '400m Medley':           263.65,
                '4×100m Livre (Revez.)': 207.96,
                '4×200m Livre (Revez.)': 457.50,
                '4×100m Medley (Revez.)':229.34,
            },
            X: {
                '4×100m Livre (Revez. Misto)':  198.48,
                '4×100m Medley (Revez. Misto)': 217.43,
            },
        },
    };

    // ─── Estado ──────────────────────────────────────────────────────────────────
    let piscina = 'scm';
    let sexo    = 'M';

    // ─── Helpers ─────────────────────────────────────────────────────────────────
    function parseTime(str) {
        str = str.trim().replace(',', '.');
        if (/^\d+(\.\d+)?$/.test(str)) return parseFloat(str);
        const m = str.match(/^(\d+):(\d{1,2}(?:\.\d+)?)$/);
        if (!m) return null;
        return parseInt(m[1], 10) * 60 + parseFloat(m[2]);
    }

    function formatTime(sec) {
        if (sec < 60) return sec.toFixed(2) + 's';
        const m = Math.floor(sec / 60);
        const s = (sec - m * 60).toFixed(2).padStart(5, '0');
        return m + ':' + s;
    }

    function zona(pts) {
        if (pts >= 1000) return { label: '🌍 Recorde Mundial', color: '#fbbf24', bg: '#451a0340', stroke: '#fbbf24' };
        if (pts >= 900)  return { label: '⭐ Elite Mundial',   color: '#a78bfa', bg: '#2e106540', stroke: '#a78bfa' };
        if (pts >= 800)  return { label: '🔵 Alto nível',      color: '#60a5fa', bg: '#1e3a8a40', stroke: '#60a5fa' };
        if (pts >= 700)  return { label: '🟢 Muito bom',       color: '#34d399', bg: '#064e3b40', stroke: '#34d399' };
        if (pts >= 500)  return { label: '⚪ Intermediário',   color: '#94a3b8', bg: '#1e293b80', stroke: '#64748b' };
        return               { label: '🔴 Desenvolvimento',   color: '#f87171', bg: '#450a0a40', stroke: '#f87171' };
    }

    // ─── UI helpers ──────────────────────────────────────────────────────────────
    function setTabGroup(ids, activeId, activeClass, inactiveClass) {
        ids.forEach(id => {
            const el = document.getElementById(id);
            el.className = el.className.replace(/tab-active|tab-inactive/g, '').trim();
            el.classList.add(id === activeId ? 'tab-active' : 'tab-inactive');
        });
    }

    function setPiscina(p) {
        piscina = p;
        setTabGroup(['btn-scm', 'btn-lcm'], 'btn-' + p);
        populateProvas();
        calcular();
    }

    function setSexo(s) {
        sexo = s;
        setTabGroup(['btn-M', 'btn-F', 'btn-X'], 'btn-' + s);
        populateProvas();
        calcular();
    }

    function populateProvas() {
        const sel   = document.getElementById('prova-select');
        const atual = sel.value;
        const provas = Object.keys(BASES[piscina][sexo] || {});
        sel.innerHTML = '<option value="">— Selecione a prova —</option>';
        provas.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p;
            opt.textContent = p;
            if (p === atual) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    function onProvaChange() {
        calcular();
    }

    function calcular() {
        const prova  = document.getElementById('prova-select').value;
        const tempoStr = document.getElementById('tempo-input').value;
        const erroEl   = document.getElementById('tempo-erro');
        const resEl    = document.getElementById('resultado');

        if (!prova || !tempoStr.trim()) {
            resEl.classList.add('hidden');
            return;
        }

        const T = parseTime(tempoStr);
        if (T === null || T <= 0) {
            erroEl.classList.remove('hidden');
            resEl.classList.add('hidden');
            return;
        }
        erroEl.classList.add('hidden');

        const B   = BASES[piscina][sexo][prova];
        const pts = Math.floor(1000 * Math.pow(B / T, 3));
        const z   = zona(pts);

        // Gauge: 0 pts = 314.16 offset; 1000 pts = 0 offset; cap at 1000
        const pctGauge    = Math.min(pts / 1000, 1);
        const dashOffset  = 314.16 * (1 - pctGauge);

        document.getElementById('gauge-arc').style.strokeDashoffset = dashOffset;
        document.getElementById('gauge-arc').setAttribute('stroke', z.stroke);
        document.getElementById('pts-value').textContent  = pts;
        document.getElementById('pts-value').style.color  = z.color;

        const badge = document.getElementById('zona-badge');
        badge.textContent   = z.label;
        badge.style.color   = z.color;
        badge.style.background = z.bg;
        badge.style.border  = '1px solid ' + z.color + '50';

        const dif = T - B;
        const difFmt = document.getElementById('dif-fmt');
        difFmt.textContent = (dif >= 0 ? '+' : '') + dif.toFixed(2) + 's';
        difFmt.style.color = dif <= 0 ? '#34d399' : '#f87171';

        document.getElementById('base-fmt').textContent  = formatTime(B);
        document.getElementById('tempo-fmt').textContent = formatTime(T);
        document.getElementById('pct-fmt').textContent   = (B / T * 100).toFixed(1) + '%';

        resEl.classList.remove('hidden');
    }

    // ─── Máscara de tempo (mm:ss.cc) ─────────────────────────────────────────────
    (function () {
        const input = document.getElementById('tempo-input');

        // Aplica a máscara: aceita até 6 dígitos e formata como m:ss.cc ou mm:ss.cc
        function applyMask(raw) {
            // Mantém apenas dígitos
            const digits = raw.replace(/\D/g, '').slice(0, 6);

            if (digits.length === 0) return '';
            if (digits.length <= 2)  return digits;                                      // cc
            if (digits.length === 3) return digits.slice(0, 1) + '.' + digits.slice(1); // s.cc
            if (digits.length === 4) return digits.slice(0, 2) + '.' + digits.slice(2); // ss.cc
            if (digits.length === 5) return digits.slice(0, 1) + ':' + digits.slice(1, 3) + '.' + digits.slice(3); // m:ss.cc
            return digits.slice(0, 2) + ':' + digits.slice(2, 4) + '.' + digits.slice(4); // mm:ss.cc
        }

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { calcular(); return; }

            // Permite: backspace, delete, tab, setas, home, end
            if (['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'].includes(e.key)) return;

            // Bloqueia qualquer tecla que não seja dígito
            if (!/^\d$/.test(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function () {
            const pos    = input.selectionStart;
            const before = input.value;
            const masked = applyMask(before);
            input.value  = masked;

            // Reposiciona cursor de forma inteligente
            const added = masked.length - before.length;
            input.setSelectionRange(pos + added, pos + added);

            calcular();
        });

        // Suporte a colar (paste)
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            input.value  = applyMask(pasted);
            calcular();
        });
    })();

    // ─── Modal tempos base ────────────────────────────────────────────────────────
    let modalPiscina = 'scm';
    let modalSexo    = 'M';

    function formatTime(sec) {
        if (sec < 60) return sec.toFixed(2) + 's';
        const m = Math.floor(sec / 60);
        const s = (sec - m * 60).toFixed(2).padStart(5, '0');
        return m + ':' + s;
    }

    function renderModalTabela() {
        const dados  = BASES[modalPiscina][modalSexo] || {};
        const tbody  = document.getElementById('modal-tbody');
        tbody.innerHTML = '';
        Object.entries(dados).forEach(([prova, baseSec], i) => {
            const tr = document.createElement('tr');
            tr.className = (i % 2 === 0 ? 'bg-slate-900' : 'bg-slate-800/40') + ' border-b border-slate-800 last:border-0';
            tr.innerHTML = `
                <td class="px-5 py-3 text-slate-200">${prova}</td>
                <td class="px-5 py-3 text-right font-mono text-blue-300 font-semibold">${formatTime(baseSec)}</td>
                <td class="px-5 py-3 text-right font-mono text-slate-400">${baseSec.toFixed(2)}s</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function setModalPiscina(p) {
        modalPiscina = p;
        ['scm','lcm'].forEach(v => {
            const el = document.getElementById('mpb-' + v);
            el.className = el.className.replace(/tab-active|tab-inactive/g, '').trim() + ' ' + (v === p ? 'tab-active' : 'tab-inactive');
        });
        renderModalTabela();
    }

    function setModalSexo(s) {
        modalSexo = s;
        ['M','F','X'].forEach(v => {
            const el = document.getElementById('msb-' + v);
            el.className = el.className.replace(/tab-active|tab-inactive/g, '').trim() + ' ' + (v === s ? 'tab-active' : 'tab-inactive');
        });
        renderModalTabela();
    }

    function abrirTemposBase() {
        // Sincroniza com seleção atual da calculadora
        modalPiscina = piscina;
        modalSexo    = sexo;
        setModalPiscina(modalPiscina);
        setModalSexo(modalSexo);
        document.getElementById('modal-tempos').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function fecharTemposBase() {
        document.getElementById('modal-tempos').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharTemposBase(); });

    // ─── Init ─────────────────────────────────────────────────────────────────────
    populateProvas();
    </script>

    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</body>
</html>
