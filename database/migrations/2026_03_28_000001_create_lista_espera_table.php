<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lista_espera', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->enum('plano', ['familia', 'clube']);
            $table->timestamps();

            $table->unique(['email', 'plano']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_espera');
    }
};
