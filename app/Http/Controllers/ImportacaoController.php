<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Services\ImportacaoJsonService;
use Illuminate\Http\Request;

class ImportacaoController extends Controller
{
    public function index()
    {
        $campeonatos = Campeonato::orderBy('nome')->get();
        return view('importacao.index', compact('campeonatos'));
    }

    public function importar(Request $request, ImportacaoJsonService $service)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:json,txt',
            'piscina' => 'required|in:25m,50m',
        ]);

        if ($request->filled('novo_campeonato')) {
            $request->validate([
                'novo_campeonato' => 'required|string|max:255',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
            ]);

            $campeonato = Campeonato::create([
                'nome' => $request->novo_campeonato,
                'data_inicio' => $request->data_inicio,
                'data_fim' => $request->data_fim,
                'piscina' => $request->piscina,
            ]);
            $campeonatoId = $campeonato->id;
        } else {
            $request->validate(['campeonato_id' => 'required|exists:campeonatos,id']);
            $campeonatoId = $request->campeonato_id;
        }

        $jsonContent = file_get_contents($request->file('arquivo')->getRealPath());
        $resultado = $service->importar($jsonContent, $campeonatoId, $request->piscina);

        return redirect()->route('importacao.index')
            ->with('resultado_importacao', $resultado);
    }
}
