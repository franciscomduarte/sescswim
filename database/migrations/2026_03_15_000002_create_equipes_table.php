<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campeonato_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distancia_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->enum('modalidade', ['Masculino', 'Feminino', 'Misto']);
            $table->enum('tipo', ['Livre', 'Medley']);
            $table->integer('ordem_execucao')->nullable();
            $table->enum('status', ['Pendente', 'Em andamento', 'Finalizada'])->default('Pendente');
            $table->string('tempo')->nullable();
            $table->integer('colocacao')->nullable();
            $table->enum('medalha', ['Ouro', 'Prata', 'Bronze', 'Nenhuma'])->default('Nenhuma');
            $table->boolean('rco')->default(false);
            $table->enum('status_lancamento', ['Pendente', 'Lançado', 'Confirmado'])->default('Pendente');
            $table->timestamp('data_lancamento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipes');
    }
};
