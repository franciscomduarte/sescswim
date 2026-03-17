<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Campeonato;
use App\Models\Premiacao;
use Illuminate\Http\Request;

class PremiacaoController extends Controller
{
    public function store(Request $request, Campeonato $campeonato)
    {
        $validated = $request->validate([
            'escopo'      => 'required|in:individual,equipe',
            'atleta_id'   => 'required_if:escopo,individual|nullable|exists:atletas,id',
            'tipo'        => 'required|in:Eficiência Técnica,Índice Técnico',
            'observacao'  => 'nullable|string|max:255',
        ]);

        Premiacao::create([
            'campeonato_id' => $campeonato->id,
            'atleta_id'     => $validated['escopo'] === 'individual' ? $validated['atleta_id'] : null,
            'tipo'          => $validated['tipo'],
            'observacao'    => $validated['observacao'] ?? null,
        ]);

        return redirect()->route('campeonatos.edit', $campeonato)
            ->with('success', 'Premiação registrada com sucesso.');
    }

    public function destroy(Campeonato $campeonato, Premiacao $premiacao)
    {
        $premiacao->delete();

        return redirect()->route('campeonatos.edit', $campeonato)
            ->with('success', 'Premiação removida.');
    }

    public function relatorio()
    {
        $campeonatos = Campeonato::with([
                'premiacoes' => fn($q) => $q->with('atleta')->orderBy('tipo')->orderBy('atleta_id'),
            ])
            ->whereHas('premiacoes')
            ->orderByDesc('data_inicio')
            ->get();

        return view('premiacoes.relatorio', compact('campeonatos'));
    }
}
