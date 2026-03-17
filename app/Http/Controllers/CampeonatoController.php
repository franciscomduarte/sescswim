<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Inscricao;
use App\Models\Resultado;
use Illuminate\Http\Request;

class CampeonatoController extends Controller
{
    public function index()
    {
        $campeonatos = Campeonato::withCount(['resultados', 'inscricoes'])
            ->orderByDesc('data_inicio')
            ->get();

        return view('campeonatos.index', compact('campeonatos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'piscina' => 'required|in:25m,50m',
        ]);

        Campeonato::create($request->only(['nome', 'data_inicio', 'data_fim', 'piscina']));

        return redirect()->route('campeonatos.index')->with('success', 'Campeonato criado com sucesso!');
    }

    public function edit(Campeonato $campeonato)
    {
        $inscricoes = Inscricao::with(['atleta', 'prova', 'distancia'])
            ->where('campeonato_id', $campeonato->id)
            ->orderBy('ordem_execucao')
            ->get()
            ->groupBy(fn($i) => $i->prova->nome . ' - ' . $i->distancia->metragem);

        $resultados = Resultado::with(['atleta', 'prova', 'distancia'])
            ->where('campeonato_id', $campeonato->id)
            ->orderBy('prova_id')
            ->orderBy('colocacao')
            ->get()
            ->groupBy(fn($r) => $r->prova->nome . ' - ' . $r->distancia->metragem);

        $totais = [
            'inscricoes' => Inscricao::where('campeonato_id', $campeonato->id)->count(),
            'resultados' => Resultado::where('campeonato_id', $campeonato->id)->count(),
            'pendentes' => Inscricao::where('campeonato_id', $campeonato->id)->where('status', 'Pendente')->count(),
            'em_andamento' => Inscricao::where('campeonato_id', $campeonato->id)->where('status', 'Em andamento')->count(),
            'finalizadas' => Inscricao::where('campeonato_id', $campeonato->id)->where('status', 'Finalizada')->count(),
            'ouro' => Resultado::where('campeonato_id', $campeonato->id)->where('medalha', 'Ouro')->count(),
            'prata' => Resultado::where('campeonato_id', $campeonato->id)->where('medalha', 'Prata')->count(),
            'bronze' => Resultado::where('campeonato_id', $campeonato->id)->where('medalha', 'Bronze')->count(),
        ];

        return view('campeonatos.edit', compact('campeonato', 'inscricoes', 'resultados', 'totais'));
    }

    public function update(Request $request, Campeonato $campeonato)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'piscina' => 'required|in:25m,50m',
        ]);

        $campeonato->update($request->only(['nome', 'data_inicio', 'data_fim', 'piscina']));

        return redirect()->route('campeonatos.edit', $campeonato)->with('success', 'Campeonato atualizado com sucesso!');
    }

    public function destroy(Campeonato $campeonato)
    {
        $campeonato->delete();
        return redirect()->route('campeonatos.index')->with('success', 'Campeonato excluído!');
    }

    public function removerInscricao(Campeonato $campeonato, Inscricao $inscricao)
    {
        Resultado::where([
            'atleta_id' => $inscricao->atleta_id,
            'prova_id' => $inscricao->prova_id,
            'distancia_id' => $inscricao->distancia_id,
            'campeonato_id' => $campeonato->id,
        ])->delete();

        $inscricao->delete();

        return redirect()->route('campeonatos.edit', $campeonato)->with('success', 'Inscrição removida!');
    }

    public function atualizarResultado(Request $request, Campeonato $campeonato, Resultado $resultado)
    {
        $validated = $request->validate([
            'tempo'             => 'nullable|string|max:20',
            'colocacao'         => 'nullable|integer|min:1',
            'medalha'           => 'nullable|in:Ouro,Prata,Bronze',
            'rco'               => 'boolean',
            'status_lancamento' => 'required|in:Pendente,Lançado,Confirmado',
        ]);

        $resultado->update([
            'tempo'             => $validated['tempo'] ?: null,
            'colocacao'         => $validated['colocacao'] ?: null,
            'medalha'           => $validated['medalha'] ?: null,
            'rco'               => $request->boolean('rco'),
            'status_lancamento' => $validated['status_lancamento'],
        ]);

        $statusInscricao = match($validated['status_lancamento']) {
            'Confirmado' => 'Finalizada',
            'Lançado'    => 'Em andamento',
            default      => 'Pendente',
        };

        Inscricao::where([
            'campeonato_id' => $campeonato->id,
            'atleta_id'     => $resultado->atleta_id,
            'prova_id'      => $resultado->prova_id,
            'distancia_id'  => $resultado->distancia_id,
        ])->update(['status' => $statusInscricao]);

        return redirect()->route('campeonatos.edit', $campeonato)->with('success', 'Resultado atualizado!');
    }

    public function removerResultado(Campeonato $campeonato, Resultado $resultado)
    {
        $atletaId = $resultado->atleta_id;
        $provaId = $resultado->prova_id;
        $distanciaId = $resultado->distancia_id;

        $resultado->delete();

        Inscricao::where([
            'campeonato_id' => $campeonato->id,
            'atleta_id' => $atletaId,
            'prova_id' => $provaId,
            'distancia_id' => $distanciaId,
        ])->update(['status' => 'Pendente']);

        return redirect()->route('campeonatos.edit', $campeonato)->with('success', 'Resultado removido!');
    }
}
