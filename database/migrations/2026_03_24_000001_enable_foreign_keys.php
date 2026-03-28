<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // No SQLite, chaves estrangeiras são ativadas por conexão via PRAGMA.
            // A configuração 'foreign_key_constraints' => true em config/database.php
            // já garante isso automaticamente em cada conexão.
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        // Para MySQL/MariaDB/PostgreSQL: adiciona as constraints explicitamente
        // caso as tabelas tenham sido criadas sem elas.

        $this->addForeignIfMissing('inscricoes', 'campeonato_id', 'campeonatos', 'cascade');
        $this->addForeignIfMissing('inscricoes', 'atleta_id', 'atletas', 'cascade');
        $this->addForeignIfMissing('inscricoes', 'prova_id', 'provas', 'cascade');
        $this->addForeignIfMissing('inscricoes', 'distancia_id', 'distancias', 'cascade');

        $this->addForeignIfMissing('resultados', 'atleta_id', 'atletas', 'cascade');
        $this->addForeignIfMissing('resultados', 'prova_id', 'provas', 'cascade');
        $this->addForeignIfMissing('resultados', 'distancia_id', 'distancias', 'cascade');
        $this->addForeignIfMissing('resultados', 'campeonato_id', 'campeonatos', 'cascade');

        $this->addForeignIfMissing('equipe_atletas', 'equipe_id', 'equipes', 'cascade');
        $this->addForeignIfMissing('equipe_atletas', 'atleta_id', 'atletas', 'cascade');

        $this->addForeignIfMissing('premiacoes', 'campeonato_id', 'campeonatos', 'cascade');
        $this->addForeignIfMissing('premiacoes', 'atleta_id', 'atletas', 'set null');
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            return;
        }

        $this->dropForeignIfExists('premiacoes', 'atleta_id');
        $this->dropForeignIfExists('premiacoes', 'campeonato_id');

        $this->dropForeignIfExists('equipe_atletas', 'atleta_id');
        $this->dropForeignIfExists('equipe_atletas', 'equipe_id');

        $this->dropForeignIfExists('resultados', 'campeonato_id');
        $this->dropForeignIfExists('resultados', 'distancia_id');
        $this->dropForeignIfExists('resultados', 'prova_id');
        $this->dropForeignIfExists('resultados', 'atleta_id');

        $this->dropForeignIfExists('inscricoes', 'distancia_id');
        $this->dropForeignIfExists('inscricoes', 'prova_id');
        $this->dropForeignIfExists('inscricoes', 'atleta_id');
        $this->dropForeignIfExists('inscricoes', 'campeonato_id');
    }

    private function hasForeign(string $table, string $column): bool
    {
        $existing = DB::getSchemaBuilder()->getForeignKeys($table);

        foreach ($existing as $fk) {
            if (in_array($column, $fk['columns'])) {
                return true;
            }
        }

        return false;
    }

    private function addForeignIfMissing(string $table, string $column, string $references, string $onDelete): void
    {
        if ($this->hasForeign($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $references, $onDelete) {
            $blueprint->foreign($column)->references('id')->on($references)->onDelete($onDelete);
        });
    }

    private function dropForeignIfExists(string $table, string $column): void
    {
        if (!$this->hasForeign($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->dropForeign([$column]);
        });
    }
};
