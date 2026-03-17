<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipe_atletas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipe_id')->constrained('equipes')->cascadeOnDelete();
            $table->foreignId('atleta_id')->constrained('atletas')->cascadeOnDelete();
            $table->unsignedTinyInteger('posicao'); // 1–4
            $table->timestamps();

            $table->unique(['equipe_id', 'posicao']);
            $table->unique(['equipe_id', 'atleta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipe_atletas');
    }
};
