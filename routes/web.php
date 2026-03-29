<?php

use App\Http\Controllers\AtletaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampeonatoController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\PremiacaoController;
use App\Http\Controllers\IndicesController;
use App\Http\Controllers\ProvaController;
use App\Http\Controllers\RelatorioBrasileiroController;
use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\ListaEsperaController;
use App\Http\Controllers\PainelPublicoController;
use App\Http\Controllers\ResultadosController;
use App\Models\Clube;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas públicas
Route::get('/placar', function () {
    if (auth()->check() && auth()->user()->clube) {
        return redirect()->route('placar.show', auth()->user()->clube->slug);
    }
    $clube = Clube::where('ativo', true)->first();
    return $clube
        ? redirect()->route('placar.show', $clube->slug)
        : redirect()->route('home');
})->name('placar.index');

Route::get('/placar/{slug}', [PainelPublicoController::class, 'index'])->name('placar.show');

Route::get('/evolucao', function () {
    if (auth()->check() && auth()->user()->clube) {
        return redirect()->route('evolucao.show', auth()->user()->clube->slug);
    }
    $clube = Clube::where('ativo', true)->first();
    return $clube
        ? redirect()->route('evolucao.show', $clube->slug)
        : redirect()->route('home');
})->name('evolucao.index');

Route::get('/evolucao/{slug}', [EvolutionController::class, 'index'])->name('evolucao.show');

Route::get('/calculadora', fn () => view('calculadora'))->name('calculadora');
Route::get('/resultados', [ResultadosController::class, 'index'])->name('resultados.index');
Route::get('/indices', [IndicesController::class, 'index'])->name('indices.index');
Route::get('/classificados-brasileiro', [RelatorioBrasileiroController::class, 'index'])->name('brasileiro.index');
Route::get('/premiacoes', [PremiacaoController::class, 'relatorio'])->name('premiacoes.relatorio');
Route::post('/lista-espera', [ListaEsperaController::class, 'store'])->name('lista-espera.store');

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    // Atletas
    Route::get('/atletas', [AtletaController::class, 'index'])->name('atletas.index');
    Route::post('/atletas', [AtletaController::class, 'store'])->name('atletas.store');
    Route::get('/atletas/{atleta}/editar', [AtletaController::class, 'edit'])->name('atletas.edit');
    Route::put('/atletas/{atleta}', [AtletaController::class, 'update'])->name('atletas.update');
    Route::delete('/atletas/{atleta}', [AtletaController::class, 'destroy'])->name('atletas.destroy');
    Route::post('/atletas/{atleta}/importar-cbda', [AtletaController::class, 'importarCbda'])->name('atletas.importar-cbda');

    // Provas
    Route::get('/provas', [ProvaController::class, 'index'])->name('provas.index');
    Route::post('/provas', [ProvaController::class, 'store'])->name('provas.store');
    Route::get('/provas/{prova}/editar', [ProvaController::class, 'edit'])->name('provas.edit');
    Route::put('/provas/{prova}', [ProvaController::class, 'update'])->name('provas.update');
    Route::delete('/provas/{prova}', [ProvaController::class, 'destroy'])->name('provas.destroy');

    // Campeonatos
    Route::get('/campeonatos', [CampeonatoController::class, 'index'])->name('campeonatos.index');
    Route::post('/campeonatos', [CampeonatoController::class, 'store'])->name('campeonatos.store');
    Route::get('/campeonatos/{campeonato}/editar', [CampeonatoController::class, 'edit'])->name('campeonatos.edit');
    Route::put('/campeonatos/{campeonato}', [CampeonatoController::class, 'update'])->name('campeonatos.update');
    Route::delete('/campeonatos/{campeonato}', [CampeonatoController::class, 'destroy'])->name('campeonatos.destroy');
    Route::post('/campeonatos/{campeonato}/provas', [CampeonatoController::class, 'adicionarProva'])->name('campeonatos.adicionar-prova');
    Route::delete('/campeonatos/{campeonato}/provas/{campeonatoProva}', [CampeonatoController::class, 'removerProva'])->name('campeonatos.remover-prova');
    Route::post('/campeonatos/{campeonato}/inscricoes', [CampeonatoController::class, 'adicionarInscricao'])->name('campeonatos.adicionar-inscricao');
    Route::delete('/campeonatos/{campeonato}/inscricao/{inscricao}', [CampeonatoController::class, 'removerInscricao'])->name('campeonatos.remover-inscricao');
    Route::delete('/campeonatos/{campeonato}/resultado/{resultado}', [CampeonatoController::class, 'removerResultado'])->name('campeonatos.remover-resultado');
    Route::put('/campeonatos/{campeonato}/resultado/{resultado}', [CampeonatoController::class, 'atualizarResultado'])->name('campeonatos.atualizar-resultado');

    // Premiações (aninhadas ao campeonato)
    Route::post('/campeonatos/{campeonato}/premiacoes', [PremiacaoController::class, 'store'])->name('campeonatos.premiacoes.store');
    Route::delete('/campeonatos/{campeonato}/premiacoes/{premiacao}', [PremiacaoController::class, 'destroy'])->name('campeonatos.premiacoes.destroy');

    // Equipes de Revezamento (aninhadas ao campeonato)
    Route::get('/campeonatos/{campeonato}/equipes/criar', [EquipeController::class, 'create'])->name('campeonatos.equipes.create');
    Route::post('/campeonatos/{campeonato}/equipes', [EquipeController::class, 'store'])->name('campeonatos.equipes.store');
    Route::get('/campeonatos/{campeonato}/equipes/{equipe}/editar', [EquipeController::class, 'edit'])->name('campeonatos.equipes.edit');
    Route::put('/campeonatos/{campeonato}/equipes/{equipe}', [EquipeController::class, 'update'])->name('campeonatos.equipes.update');
    Route::delete('/campeonatos/{campeonato}/equipes/{equipe}', [EquipeController::class, 'destroy'])->name('campeonatos.equipes.destroy');

    // Importação
    Route::get('/importacao', [ImportacaoController::class, 'index'])->name('importacao.index');
    Route::post('/importacao', [ImportacaoController::class, 'importar'])->name('importacao.importar');

    // Painel de Lançamento
    Route::get('/painel', function () {
        $campeonatos = \App\Models\Campeonato::orderByDesc('data_inicio')->get();
        return view('painel.index', compact('campeonatos'));
    })->name('painel.index');

    Route::get('/painel/{campeonato}', function (\App\Models\Campeonato $campeonato) {
        return view('painel.show', compact('campeonato'));
    })->name('painel.show');
});
