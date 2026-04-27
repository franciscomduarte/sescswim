<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CAMPEONATO_ID = 7;

    public function up(): void
    {
        // Cria o campeonato se ainda não existir
        $existe = DB::table('campeonatos')->where('id', self::CAMPEONATO_ID)->exists();
        if (!$existe) {
            DB::statement('INSERT INTO campeonatos (id, nome, data_inicio, data_fim, piscina, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)', [
                self::CAMPEONATO_ID,
                'JOGOS ESCOLARES DF ( SELETIVA JEBS E JOGOS JUVENTUDE)',
                '2026-04-25',
                '2026-04-26',
                '50m',
                now(),
                now(),
            ]);
            // Reseta a sequence para não colidir com futuros inserts via ORM
            DB::statement("SELECT setval(pg_get_serial_sequence('campeonatos', 'id'), (SELECT MAX(id) FROM campeonatos))");
        }

        // Provas (estilos) já existentes: 1=Medley, 2=Livre, 3=Borboleta, 4=Costas, 5=Peito
        // Garante que as distâncias existam e recupera seus IDs
        $distancias = collect([
            '50M', '100M', '200M', '400M', '800M', '1500M', '4x50M',
        ])->mapWithKeys(function (string $metragem) {
            $id = DB::table('distancias')
                ->where('metragem', $metragem)
                ->value('id');

            if (!$id) {
                $id = DB::table('distancias')->insertGetId([
                    'metragem'   => $metragem,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [$metragem => $id];
        });

        $provas = DB::table('provas')
            ->whereIn('nome', ['Medley', 'Livre', 'Borboleta', 'Costas', 'Peito'])
            ->pluck('id', 'nome');

        // Todas as combinações prova+distância presentes no balizamento JEDF 2026
        $combinacoes = [
            // Revezamento 4x50M Medley (Etapa 1 provas 1, 2 + Etapa 3 provas 43-46)
            ['Medley',    '4x50M'],
            // Revezamento 4x50M Livre (Etapa 1 provas 15, 16 + Etapa 2 provas 17-20)
            ['Livre',     '4x50M'],
            // Individuais Livre
            ['Livre',     '50M'],
            ['Livre',     '100M'],
            ['Livre',     '200M'],
            ['Livre',     '400M'],
            ['Livre',     '800M'],
            ['Livre',     '1500M'],
            // Individuais Medley
            ['Medley',    '200M'],
            ['Medley',    '400M'],
            // Individuais Borboleta
            ['Borboleta', '50M'],
            ['Borboleta', '100M'],
            ['Borboleta', '200M'],
            // Individuais Costas
            ['Costas',    '50M'],
            ['Costas',    '100M'],
            ['Costas',    '200M'],
            // Individuais Peito
            ['Peito',     '50M'],
            ['Peito',     '100M'],
            ['Peito',     '200M'],
        ];

        foreach ($combinacoes as [$provaNome, $distanciaMet]) {
            DB::table('campeonato_provas')->insertOrIgnore([
                'campeonato_id' => self::CAMPEONATO_ID,
                'prova_id'      => $provas[$provaNome],
                'distancia_id'  => $distancias[$distanciaMet],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('campeonato_provas')
            ->where('campeonato_id', self::CAMPEONATO_ID)
            ->delete();

        DB::table('campeonatos')
            ->where('id', self::CAMPEONATO_ID)
            ->delete();
    }
};
