<?php

namespace App\Http\Controllers;

use App\Mail\ListaEsperaNotificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ListaEsperaController extends Controller
{
    const NOTIFICAR_EMAIL = 'francisco.m.duarte@gmail.com';

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'  => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'plano' => 'required|in:familia,clube',
        ]);

        $jaExiste = DB::table('lista_espera')
            ->where('email', $data['email'])
            ->where('plano', $data['plano'])
            ->exists();

        if ($jaExiste) {
            return response()->json([
                'status'   => 'already',
                'mensagem' => 'Este e-mail já está na lista de espera para este plano.',
            ]);
        }

        DB::table('lista_espera')->insert([
            'nome'       => $data['nome'],
            'email'      => $data['email'],
            'plano'      => $data['plano'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::to(self::NOTIFICAR_EMAIL)->send(
            new ListaEsperaNotificacao($data['nome'], $data['email'], $data['plano'])
        );

        return response()->json([
            'status'   => 'ok',
            'mensagem' => 'Cadastro realizado com sucesso!',
        ]);
    }
}
