<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cria o clube SESC inicial
        $clubeId = DB::table('clubes')->insertGetId([
            'nome'       => 'SESC',
            'slug'       => 'sesc',
            'ativo'      => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Adiciona clube_id e is_super_admin em users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clube_id')->nullable()->constrained('clubes')->nullOnDelete();
            $table->boolean('is_super_admin')->default(false);
        });

        // Adiciona clube_id em atletas
        Schema::table('atletas', function (Blueprint $table) {
            $table->foreignId('clube_id')->nullable()->constrained('clubes')->nullOnDelete();
        });

        // Adiciona clube_id em campeonatos
        Schema::table('campeonatos', function (Blueprint $table) {
            $table->foreignId('clube_id')->nullable()->constrained('clubes')->nullOnDelete();
        });

        // Associa todos os dados existentes ao clube SESC
        DB::table('atletas')->update(['clube_id' => $clubeId]);
        DB::table('campeonatos')->update(['clube_id' => $clubeId]);

        // O admin existente fica associado ao SESC e vira super_admin
        DB::table('users')->update([
            'clube_id'      => $clubeId,
            'is_super_admin' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('campeonatos', fn (Blueprint $t) => $t->dropColumn('clube_id'));
        Schema::table('atletas', fn (Blueprint $t) => $t->dropColumn('clube_id'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['clube_id', 'is_super_admin']));
    }
};
