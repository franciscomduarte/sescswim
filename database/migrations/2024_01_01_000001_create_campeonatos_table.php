<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campeonatos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->enum('piscina', ['25m', '50m']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campeonatos');
    }
};
