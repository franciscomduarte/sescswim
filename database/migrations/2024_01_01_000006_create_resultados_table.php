<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atleta_id')->constrained('atletas')->cascadeOnDelete();
            $table->foreignId('prova_id')->constrained('provas')->cascadeOnDelete();
            $table->foreignId('distancia_id')->constrained('distancias')->cascadeOnDelete();
            $table->foreignId('campeonato_id')->constrained('campeonatos')->cascadeOnDelete();
            $table->enum('piscina', ['25m', '50m']);
            $table->string('tempo')->nullable();
            $table->integer('colocacao')->nullable();
            $table->enum('medalha', ['Ouro', 'Prata', 'Bronze', 'Nenhuma'])->default('Nenhuma');
            $table->enum('status_lancamento', ['Pendente', 'Lançado', 'Confirmado'])->default('Pendente');
            $table->timestamp('data_lancamento')->nullable();
            $table->timestamps();

            $table->unique(['atleta_id', 'prova_id', 'distancia_id', 'campeonato_id'], 'resultados_unique');
            $table->index(['campeonato_id', 'prova_id', 'distancia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados');
    }
};
