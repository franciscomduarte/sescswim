<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campeonato_provas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campeonato_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prova_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distancia_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campeonato_id', 'prova_id', 'distancia_id']);
        });

        // Migra os grupos já existentes (derivados das inscrições)
        $grupos = DB::table('inscricoes')
            ->select('campeonato_id', 'prova_id', 'distancia_id')
            ->distinct()
            ->get();

        foreach ($grupos as $g) {
            DB::table('campeonato_provas')->insertOrIgnore([
                'campeonato_id' => $g->campeonato_id,
                'prova_id'      => $g->prova_id,
                'distancia_id'  => $g->distancia_id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campeonato_provas');
    }
};
