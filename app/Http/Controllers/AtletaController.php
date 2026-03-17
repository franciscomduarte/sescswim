<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AtletaController extends Controller
{
    public function index()
    {
        $atletas = Atleta::orderBy('nome')
            ->withCount(['inscricoes', 'resultados'])
            ->get();

        return view('atletas.index', compact('atletas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:atletas,nome',
            'data_nascimento' => 'nullable|date',
            'codigo_federacao' => 'nullable|string|max:100',
            'sexo' => 'nullable|in:masculino,feminino',
        ]);

        Atleta::create($validated);

        return redirect()->route('atletas.index')->with('success', 'Atleta cadastrado com sucesso.');
    }

    public function edit(Atleta $atleta)
    {
        return view('atletas.edit', compact('atleta'));
    }

    public function update(Request $request, Atleta $atleta)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:atletas,nome,' . $atleta->id,
            'data_nascimento' => 'nullable|date',
            'codigo_federacao' => 'nullable|string|max:100',
            'sexo' => 'nullable|in:masculino,feminino',
        ]);

        $atleta->update($validated);

        return redirect()->route('atletas.index')->with('success', 'Atleta atualizado com sucesso.');
    }

    public function destroy(Atleta $atleta)
    {
        $atleta->delete();

        return redirect()->route('atletas.index')->with('success', 'Atleta excluído com sucesso.');
    }

    public function importarCbda(Atleta $atleta)
    {
        if (!$atleta->codigo_federacao) {
            return back()->with('error', 'Atleta não possui código de federação.');
        }

        $firebaseApiKey = env('CBDA_FIREBASE_API_KEY');
        $authUrl = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$firebaseApiKey}";

        $authResponse = Http::withoutVerifying()->post($authUrl, [
            'email'             => env('CBDA_EMAIL'),
            'password'          => env('CBDA_PASSWORD'),
            'returnSecureToken' => true,
        ]);

        if ($authResponse->failed()) {
            return back()->with('error', 'Falha na autenticação com a CBDA.');
        }

        $token = $authResponse->json('idToken');

        $apiResponse = Http::withoutVerifying()->withToken($token)
            ->get("https://www.cbda.org.br/api/atleta/{$atleta->codigo_federacao}");

        if ($apiResponse->failed()) {
            return back()->with('error', 'Atleta não encontrado na CBDA (código: ' . $atleta->codigo_federacao . ').');
        }

        $dados = $apiResponse->json();

        if (!$dados) {
            return back()->with('error', 'Resposta inesperada da CBDA: ' . substr($apiResponse->body(), 0, 300));
        }

        $sexoMap = [
            'm'         => 'masculino',
            'f'         => 'feminino',
            'masculino' => 'masculino',
            'feminino'  => 'feminino',
        ];
        $sexo = $sexoMap[strtolower($dados['sexo'] ?? '')] ?? null;

        $atleta->update([
            'data_nascimento' => $dados['data_nascimento'] ?? $atleta->data_nascimento,
            'sexo'            => $sexo ?? $atleta->sexo,
        ]);

        return back()->with('success', 'Dados importados da CBDA com sucesso.');
    }
}
