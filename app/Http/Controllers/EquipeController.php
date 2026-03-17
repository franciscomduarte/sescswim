<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Campeonato;
use App\Models\Distancia;
use App\Models\Equipe;
use App\Models\EquipeAtleta;
use Illuminate\Http\Request;

class EquipeController extends Controller
{
    public function create(Campeonato $campeonato)
    {
        $atletas   = Atleta::orderBy('nome')->get();
        $distancias = Distancia::orderBy('metragem')->get();

        return view('equipes.create', compact('campeonato', 'atletas', 'distancias'));
    }

    public function store(Request $request, Campeonato $campeonato)
    {
        $validated = $request->validate([
            'nome'             => 'required|string|max:255',
            'modalidade'       => 'required|in:Masculino,Feminino,Misto',
            'tipo'             => 'required|in:Livre,Medley',
            'distancia_id'     => 'required|exists:distancias,id',
            'ordem_execucao'   => 'nullable|integer|min:1',
            'atleta_1'         => 'required|exists:atletas,id',
            'atleta_2'         => 'required|exists:atletas,id',
            'atleta_3'         => 'required|exists:atletas,id',
            'atleta_4'         => 'required|exists:atletas,id',
        ]);

        $atletaIds = [
            $validated['atleta_1'],
            $validated['atleta_2'],
            $validated['atleta_3'],
            $validated['atleta_4'],
        ];

        if (count(array_unique($atletaIds)) < 4) {
            return back()->withErrors(['atleta_1' => 'Os 4 atletas devem ser diferentes.'])->withInput();
        }

        $equipe = Equipe::create([
            'campeonato_id'  => $campeonato->id,
            'distancia_id'   => $validated['distancia_id'],
            'nome'           => $validated['nome'],
            'modalidade'     => $validated['modalidade'],
            'tipo'           => $validated['tipo'],
            'ordem_execucao' => $validated['ordem_execucao'],
        ]);

        foreach ([1, 2, 3, 4] as $pos) {
            EquipeAtleta::create([
                'equipe_id'  => $equipe->id,
                'atleta_id'  => $validated["atleta_{$pos}"],
                'posicao'    => $pos,
            ]);
        }

        return redirect()->route('campeonatos.edit', $campeonato)
            ->with('success', 'Equipe cadastrada com sucesso.');
    }

    public function edit(Campeonato $campeonato, Equipe $equipe)
    {
        $atletas    = Atleta::orderBy('nome')->get();
        $distancias = Distancia::orderBy('metragem')->get();
        $membros    = $equipe->membros()->with('atleta')->get()->keyBy('posicao');

        return view('equipes.edit', compact('campeonato', 'equipe', 'atletas', 'distancias', 'membros'));
    }

    public function update(Request $request, Campeonato $campeonato, Equipe $equipe)
    {
        $validated = $request->validate([
            'nome'           => 'required|string|max:255',
            'modalidade'     => 'required|in:Masculino,Feminino,Misto',
            'tipo'           => 'required|in:Livre,Medley',
            'distancia_id'   => 'required|exists:distancias,id',
            'ordem_execucao' => 'nullable|integer|min:1',
            'atleta_1'       => 'required|exists:atletas,id',
            'atleta_2'       => 'required|exists:atletas,id',
            'atleta_3'       => 'required|exists:atletas,id',
            'atleta_4'       => 'required|exists:atletas,id',
        ]);

        $atletaIds = [
            $validated['atleta_1'],
            $validated['atleta_2'],
            $validated['atleta_3'],
            $validated['atleta_4'],
        ];

        if (count(array_unique($atletaIds)) < 4) {
            return back()->withErrors(['atleta_1' => 'Os 4 atletas devem ser diferentes.'])->withInput();
        }

        $equipe->update([
            'distancia_id'   => $validated['distancia_id'],
            'nome'           => $validated['nome'],
            'modalidade'     => $validated['modalidade'],
            'tipo'           => $validated['tipo'],
            'ordem_execucao' => $validated['ordem_execucao'],
        ]);

        $equipe->membros()->delete();

        foreach ([1, 2, 3, 4] as $pos) {
            EquipeAtleta::create([
                'equipe_id' => $equipe->id,
                'atleta_id' => $validated["atleta_{$pos}"],
                'posicao'   => $pos,
            ]);
        }

        return redirect()->route('campeonatos.edit', $campeonato)
            ->with('success', 'Equipe atualizada com sucesso.');
    }

    public function destroy(Campeonato $campeonato, Equipe $equipe)
    {
        $equipe->delete();

        return redirect()->route('campeonatos.edit', $campeonato)
            ->with('success', 'Equipe excluída.');
    }
}
