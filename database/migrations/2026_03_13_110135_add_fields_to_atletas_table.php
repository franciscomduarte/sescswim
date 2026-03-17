<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('atletas', function (Blueprint $table) {
            $table->date('data_nascimento')->nullable()->after('nome');
            $table->string('codigo_federacao')->nullable()->after('data_nascimento');
            $table->enum('sexo', ['masculino', 'feminino'])->nullable()->after('codigo_federacao');
        });
    }

    public function down(): void
    {
        Schema::table('atletas', function (Blueprint $table) {
            $table->dropColumn(['data_nascimento', 'codigo_federacao', 'sexo']);
        });
    }
};
