<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Sistema de Natação')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-xl font-bold">Natação SESC</a>
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('painel.index') }}" class="hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium">Painel</a>
                        <a href="{{ route('importacao.index') }}" class="hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium">Importação</a>
                        <a href="{{ route('resultados.index') }}" class="hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium">Resultados</a>
                        <a href="{{ route('campeonatos.index') }}" class="hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium">Campeonatos</a>
                    </div>
                </div>
                <div class="md:hidden">
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden pb-3 px-4 space-y-1">
            <a href="{{ route('painel.index') }}" class="block hover:bg-blue-600 px-3 py-2 rounded-md text-sm">Painel</a>
            <a href="{{ route('importacao.index') }}" class="block hover:bg-blue-600 px-3 py-2 rounded-md text-sm">Importação</a>
            <a href="{{ route('resultados.index') }}" class="block hover:bg-blue-600 px-3 py-2 rounded-md text-sm">Resultados</a>
            <a href="{{ route('campeonatos.index') }}" class="block hover:bg-blue-600 px-3 py-2 rounded-md text-sm">Campeonatos</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
