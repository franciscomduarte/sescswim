<?php

use App\Http\Controllers\CampeonatoController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\ResultadosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('resultados.index');
});

// Campeonatos
Route::get('/campeonatos', [CampeonatoController::class, 'index'])->name('campeonatos.index');
Route::post('/campeonatos', [CampeonatoController::class, 'store'])->name('campeonatos.store');
Route::get('/campeonatos/{campeonato}/editar', [CampeonatoController::class, 'edit'])->name('campeonatos.edit');
Route::put('/campeonatos/{campeonato}', [CampeonatoController::class, 'update'])->name('campeonatos.update');
Route::delete('/campeonatos/{campeonato}', [CampeonatoController::class, 'destroy'])->name('campeonatos.destroy');
Route::delete('/campeonatos/{campeonato}/inscricao/{inscricao}', [CampeonatoController::class, 'removerInscricao'])->name('campeonatos.remover-inscricao');
Route::delete('/campeonatos/{campeonato}/resultado/{resultado}', [CampeonatoController::class, 'removerResultado'])->name('campeonatos.remover-resultado');

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

// Resultados / Landing Page
Route::get('/resultados', [ResultadosController::class, 'index'])->name('resultados.index');
