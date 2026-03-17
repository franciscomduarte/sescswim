<?php

namespace App\Http\Controllers;

use App\Models\Prova;
use Illuminate\Http\Request;

class ProvaController extends Controller
{
    public function index()
    {
        $provas = Prova::withCount('inscricoes')
            ->with(['inscricoes.campeonato'])
            ->orderBy('nome')
            ->get()
            ->map(function ($prova) {
                $prova->campeonatos_uso = $prova->inscricoes
                    ->pluck('campeonato')
                    ->filter()
                    ->unique('id')
                    ->sortByDesc('data_inicio')
                    ->values();
                return $prova;
            });

        return view('provas.index', compact('provas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:provas,nome',
        ]);

        Prova::create($request->only('nome'));

        return redirect()->route('provas.index')->with('success', 'Prova cadastrada com sucesso.');
    }

    public function edit(Prova $prova)
    {
        return view('provas.edit', compact('prova'));
    }

    public function update(Request $request, Prova $prova)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:provas,nome,' . $prova->id,
        ]);

        $prova->update($request->only('nome'));

        return redirect()->route('provas.index')->with('success', 'Prova atualizada com sucesso.');
    }

    public function destroy(Prova $prova)
    {
        if ($prova->inscricoes()->exists()) {
            return redirect()->route('provas.index')->with('error', 'Não é possível excluir: prova possui inscrições vinculadas.');
        }

        $prova->delete();

        return redirect()->route('provas.index')->with('success', 'Prova excluída com sucesso.');
    }
}
