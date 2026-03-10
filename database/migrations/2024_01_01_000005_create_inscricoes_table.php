<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campeonato_id')->constrained('campeonatos')->cascadeOnDelete();
            $table->foreignId('atleta_id')->constrained('atletas')->cascadeOnDelete();
            $table->foreignId('prova_id')->constrained('provas')->cascadeOnDelete();
            $table->foreignId('distancia_id')->constrained('distancias')->cascadeOnDelete();
            $table->integer('ordem_execucao')->default(0);
            $table->enum('status', ['Pendente', 'Em andamento', 'Finalizada'])->default('Pendente');
            $table->timestamps();

            $table->unique(['campeonato_id', 'atleta_id', 'prova_id', 'distancia_id'], 'inscricoes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscricoes');
    }
};
