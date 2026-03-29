<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SESC Swim – Plataforma de Natação</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SESC Swim">
    <link rel="apple-touch-icon" href="/icons/icon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0f172a; }
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .card-free {
            background: #1e293b;
            border: 1px solid #334155;
            transition: border-color .2s, transform .2s;
        }
        .card-free:hover { border-color: #60a5fa; transform: translateY(-2px); }

        .plan-familia {
            background: linear-gradient(160deg, #0c1445 0%, #1e293b 100%);
            border: 1px solid #3b82f6;
        }
        .plan-clube {
            background: linear-gradient(160deg, #1a0c45 0%, #1e293b 100%);
            border: 1px solid #7c3aed;
        }
        .check-familia { color: #60a5fa; }
        .check-clube   { color: #a78bfa; }

        .badge-free    { background: #064e3b; color: #6ee7b7; }
        .badge-familia { background: #1e3a8a; color: #93c5fd; }
        .badge-clube   { background: #2e1065; color: #c4b5fd; }
    </style>
</head>
<body class="min-h-screen text-white">

    {{-- NAV --}}
    <nav class="px-4 py-4 flex items-center justify-between max-w-6xl mx-auto">
        <div class="flex items-center gap-2">
            <img src="/icons/icon.svg" class="w-8 h-8 rounded-lg" alt="Logo">
            <span class="font-bold text-lg tracking-tight">SESC Swim</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="#planos" class="text-sm text-slate-400 hover:text-white transition hidden sm:block">Planos</a>
            @auth
                <a href="{{ route('painel.index') }}"
                   class="text-sm bg-blue-700 hover:bg-blue-600 px-4 py-2 rounded-lg font-medium transition">
                    Painel
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm text-slate-400 hover:text-white px-3 py-2 transition">
                    Entrar
                </a>
            @endauth
        </div>
    </nav>

    {{-- HERO --}}
    <section class="px-4 pt-10 pb-16 text-center max-w-3xl mx-auto">
        <div class="inline-flex items-center gap-2 bg-blue-900/40 border border-blue-800 rounded-full px-4 py-1.5 text-xs text-blue-300 mb-6">
            🏊 Plataforma de Natação Competitiva
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
            Tudo sobre seus<br>
            <span class="gradient-text">atletas em um só lugar</span>
        </h1>
        <p class="text-slate-400 text-lg max-w-xl mx-auto mb-8">
            Acompanhe resultados ao vivo, evolução de tempos e muito mais.
            Para pais, técnicos e clubes.
        </p>
        <a href="#planos"
           class="inline-block bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-3 rounded-xl transition text-sm">
            Ver planos →
        </a>
    </section>

    {{-- FREE SERVICES --}}
    <section class="px-4 pb-16 max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-xs font-bold px-3 py-1 rounded-full badge-free uppercase tracking-wide">Gratuito</span>
            <span class="text-slate-400 text-sm">Disponível para todos, sem cadastro</span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

            <a href="{{ route('placar.index') }}" class="card-free rounded-2xl p-5 block">
                <div class="text-3xl mb-3">📡</div>
                <h3 class="font-bold text-sm sm:text-base mb-1">Placar ao Vivo</h3>
                <p class="text-slate-400 text-xs sm:text-sm">Resultados e colocações em tempo real durante as competições.</p>
                <div class="mt-4 text-blue-400 text-xs sm:text-sm font-medium">Acessar →</div>
            </a>

            <a href="{{ route('evolucao.index') }}" class="card-free rounded-2xl p-5 block">
                <div class="text-3xl mb-3">📈</div>
                <h3 class="font-bold text-sm sm:text-base mb-1">Evolução</h3>
                <p class="text-slate-400 text-xs sm:text-sm">Tempos de cada atleta ao longo dos campeonatos com gráficos.</p>
                <div class="mt-4 text-blue-400 text-xs sm:text-sm font-medium">Acessar →</div>
            </a>

            <a href="{{ route('resultados.index') }}" class="card-free rounded-2xl p-5 block">
                <div class="text-3xl mb-3">🏆</div>
                <h3 class="font-bold text-sm sm:text-base mb-1">Resultados</h3>
                <p class="text-slate-400 text-xs sm:text-sm">Histórico completo por campeonato, prova e atleta.</p>
                <div class="mt-4 text-blue-400 text-xs sm:text-sm font-medium">Acessar →</div>
            </a>

            <a href="{{ route('indices.index') }}" class="card-free rounded-2xl p-5 block">
                <div class="text-3xl mb-3">🎯</div>
                <h3 class="font-bold text-sm sm:text-base mb-1">Índices</h3>
                <p class="text-slate-400 text-xs sm:text-sm">Tabela de índices por categoria, prova e temporada.</p>
                <div class="mt-4 text-blue-400 text-xs sm:text-sm font-medium">Acessar →</div>
            </a>

            <a href="{{ route('calculadora') }}" class="card-free rounded-2xl p-5 block">
                <div class="text-3xl mb-3">🧮</div>
                <h3 class="font-bold text-sm sm:text-base mb-1">Calculadora</h3>
                <p class="text-slate-400 text-xs sm:text-sm">Calcule seus pontos World Aquatics a partir do seu tempo.</p>
                <div class="mt-4 text-blue-400 text-xs sm:text-sm font-medium">Calcular →</div>
            </a>

        </div>
    </section>

    {{-- DIVIDER --}}
    <div class="max-w-6xl mx-auto px-4 mb-16">
        <div class="border-t border-slate-800"></div>
    </div>

    {{-- PLANS --}}
    <section id="planos" class="px-4 pb-24 max-w-6xl mx-auto">

        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 bg-violet-900/40 border border-violet-800 rounded-full px-4 py-1.5 text-xs text-violet-300 mb-4">
                ✨ Recursos Premium
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold mb-3">Escolha o plano ideal</h2>
            <p class="text-slate-400 text-sm max-w-md mx-auto">
                Ferramentas avançadas para acompanhar e desenvolver atletas com mais profundidade.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">

            {{-- PLANO FAMÍLIA --}}
            <div class="plan-familia rounded-2xl p-6 flex flex-col">
                <div class="flex items-start justify-between mb-1">
                    <div class="text-4xl">👨‍👩‍👧</div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full badge-familia uppercase tracking-wide">Em breve</span>
                </div>
                <h3 class="text-xl font-extrabold mt-3 mb-1">Plano Família</h3>
                <p class="text-slate-400 text-sm mb-6">
                    Para pais que querem acompanhar de perto cada passo do seu filho dentro da natação.
                </p>

                <ul class="space-y-3 mb-8 flex-1">
                    @php $familiaItens = [
                        ['Histórico completo e personalizado do atleta', 'Veja todos os resultados do seu filho em um só lugar, com linha do tempo visual.'],
                        ['Notificação de resultados', 'Receba uma mensagem assim que o resultado for lançado — tempo, colocação e medalha.'],
                        ['Comparação com os índices', 'Saiba exatamente quanto falta para atingir o índice da próxima competição ou categoria.'],
                        ['Card de conquista compartilhável', 'Gere uma imagem bonita de um resultado ou recorde pessoal para postar no Instagram.'],
                        ['Alertas de recorde pessoal', 'Notificação automática sempre que seu filho bater o próprio melhor tempo.'],
                        ['Planejamento de metas', 'Defina metas de tempo por prova junto com o técnico e acompanhe o progresso do seu filho a cada competição.'],
                    ]; @endphp
                    @foreach($familiaItens as [$titulo, $desc])
                        <li class="flex items-start gap-3">
                            <svg class="w-4 h-4 check-familia flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $titulo }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $desc }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <button onclick="abrirModal('familia')"
                        class="w-full py-3 rounded-xl bg-blue-700/30 border border-blue-600 text-blue-300 text-sm font-semibold hover:bg-blue-700/50 transition">
                    Garantir meu desconto →
                </button>
            </div>

            {{-- PLANO CLUBE --}}
            <div class="plan-clube rounded-2xl p-6 flex flex-col">
                <div class="flex items-start justify-between mb-1">
                    <div class="text-4xl">🏊</div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full badge-clube uppercase tracking-wide">Em breve</span>
                </div>
                <h3 class="text-xl font-extrabold mt-3 mb-1">Plano Clube</h3>
                <p class="text-slate-400 text-sm mb-6">
                    Para técnicos e clubes que precisam de uma visão completa e ferramentas de gestão da equipe.
                </p>

                <ul class="space-y-3 mb-8 flex-1">
                    @php $clubeItens = [
                        ['Importação automática do Starlist', 'Assim que o Starlist de uma competição for liberado, os atletas são importados automaticamente.'],
                        ['Painel do técnico', 'Visão consolidada de todos os atletas: quem está evoluindo, quem está estagnado, quem está perto de um índice.'],
                        ['Relatório pré-campeonato', 'Lista dos atletas inscritos com melhores tempos, índices e expectativa de colocação.'],
                        ['Comparativo entre temporadas', 'Análise da evolução coletiva do clube de um ano para o outro.'],
                        ['Gestão de metas por atleta', 'O técnico define a meta de tempo por prova e o sistema mostra o progresso.'],
                        ['Exportação em PDF', 'Relatórios prontos para apresentar para a diretoria ou para os pais.'],
                    ]; @endphp
                    @foreach($clubeItens as [$titulo, $desc])
                        <li class="flex items-start gap-3">
                            <svg class="w-4 h-4 check-clube flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $titulo }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $desc }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <button onclick="abrirModal('clube')"
                        class="w-full py-3 rounded-xl bg-violet-700/30 border border-violet-600 text-violet-300 text-sm font-semibold hover:bg-violet-700/50 transition">
                    Garantir meu desconto →
                </button>
            </div>

        </div>

        {{-- NOTA --}}
        <p class="text-center text-slate-600 text-xs mt-8">
            Todos os recursos gratuitos continuam disponíveis em qualquer plano.
        </p>
    </section>

    {{-- FOOTER --}}
    <footer class="border-t border-slate-800 px-4 py-8 text-center text-slate-600 text-xs">
        <div class="flex items-center justify-center gap-2 mb-2">
            <img src="/icons/icon.svg" class="w-5 h-5 rounded opacity-60" alt="">
            <span class="font-semibold text-slate-500">SESC Swim</span>
        </div>
        <p>Plataforma de Natação Competitiva</p>
    </footer>

    {{-- MODAL LISTA DE ESPERA --}}
    <div id="modal-espera" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 hidden">
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="fecharModal()"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-md bg-slate-900 border border-slate-700 rounded-2xl p-6 shadow-2xl">

            {{-- Close --}}
            <button onclick="fecharModal()" class="absolute top-4 right-4 text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Ícone + título --}}
            <div id="modal-icon" class="text-4xl mb-2"></div>
            <h3 id="modal-titulo" class="text-xl font-extrabold mb-1"></h3>

            {{-- Destaque de desconto --}}
            <div class="bg-amber-900/30 border border-amber-700/50 rounded-xl px-4 py-3 mb-5">
                <p class="text-amber-300 text-sm font-semibold">🎁 Desconto especial para a lista de espera</p>
                <p class="text-amber-200/70 text-xs mt-1">
                    Quem se cadastrar agora garante um desconto exclusivo no lançamento.
                    Você será o primeiro a saber quando o módulo estiver disponível.
                </p>
            </div>

            {{-- Formulário --}}
            <div id="modal-form-area">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Nome completo</label>
                        <input id="modal-nome" type="text" placeholder="Seu nome"
                               class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">E-mail</label>
                        <input id="modal-email" type="email" placeholder="seu@email.com"
                               class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <p id="modal-erro" class="text-red-400 text-xs mt-2 hidden"></p>
                <button id="modal-btn" onclick="enviarListaEspera()"
                        class="w-full mt-4 py-3 rounded-xl text-sm font-bold transition">
                    Quero meu desconto →
                </button>
            </div>

            {{-- Sucesso --}}
            <div id="modal-sucesso" class="hidden text-center py-4">
                <div class="text-5xl mb-3">🎉</div>
                <p class="font-bold text-lg mb-1">Você está na lista!</p>
                <p class="text-slate-400 text-sm">Avisaremos por e-mail assim que o módulo estiver disponível — com seu desconto garantido.</p>
            </div>

        </div>
    </div>

    <script>
        let planoAtual = '';

        const planos = {
            familia: {
                icon: '👨‍👩‍👧',
                titulo: 'Lista de espera – Plano Família',
                btnClass: 'bg-blue-600 hover:bg-blue-500 text-white',
            },
            clube: {
                icon: '🏊',
                titulo: 'Lista de espera – Plano Clube',
                btnClass: 'bg-violet-600 hover:bg-violet-500 text-white',
            },
        };

        function abrirModal(plano) {
            planoAtual = plano;
            const p = planos[plano];
            document.getElementById('modal-icon').textContent    = p.icon;
            document.getElementById('modal-titulo').textContent  = p.titulo;
            document.getElementById('modal-btn').className       = 'w-full mt-4 py-3 rounded-xl text-sm font-bold transition ' + p.btnClass;
            document.getElementById('modal-nome').value          = '';
            document.getElementById('modal-email').value         = '';
            document.getElementById('modal-erro').classList.add('hidden');
            document.getElementById('modal-form-area').classList.remove('hidden');
            document.getElementById('modal-sucesso').classList.add('hidden');
            document.getElementById('modal-espera').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('modal-nome').focus(), 100);
        }

        function fecharModal() {
            document.getElementById('modal-espera').classList.add('hidden');
            document.body.style.overflow = '';
        }

        async function enviarListaEspera() {
            const nome  = document.getElementById('modal-nome').value.trim();
            const email = document.getElementById('modal-email').value.trim();
            const erro  = document.getElementById('modal-erro');

            if (!nome || !email) {
                erro.textContent = 'Preencha nome e e-mail para continuar.';
                erro.classList.remove('hidden');
                return;
            }

            const btn = document.getElementById('modal-btn');
            btn.textContent = 'Enviando...';
            btn.disabled = true;

            try {
                const res = await fetch('{{ route('lista-espera.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ nome, email, plano: planoAtual }),
                });

                const data = await res.json();

                if (data.status === 'ok') {
                    document.getElementById('modal-form-area').classList.add('hidden');
                    document.getElementById('modal-sucesso').classList.remove('hidden');
                } else if (data.status === 'already') {
                    erro.textContent = 'Este e-mail já está cadastrado neste plano.';
                    erro.classList.remove('hidden');
                    btn.textContent = 'Quero meu desconto →';
                    btn.disabled = false;
                } else {
                    throw new Error();
                }
            } catch {
                erro.textContent = 'Erro ao enviar. Tente novamente.';
                erro.classList.remove('hidden');
                btn.textContent = 'Quero meu desconto →';
                btn.disabled = false;
            }
        }

        // Fechar com ESC
        document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModal(); });
    </script>

    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</body>
</html>
