<?php

namespace Database\Seeders;

use App\Models\Campeonato;
use App\Services\ImportacaoJsonService;
use Illuminate\Database\Seeder;

class CampeonatoSeeder extends Seeder
{
    public function run(): void
    {
        $campeonato = Campeonato::firstOrCreate(
            ['nome' => 'Torneio Festival de Águas 2024'],
            [
                'data_inicio' => '2024-11-01',
                'data_fim' => '2024-11-03',
                'piscina' => '25m',
            ]
        );

        $jsonPath = base_path('../torneio_fda.json');
        if (!file_exists($jsonPath)) {
            $jsonPath = storage_path('app/torneio_fda.json');
        }

        if (file_exists($jsonPath)) {
            $service = new ImportacaoJsonService();
            $result = $service->importar(file_get_contents($jsonPath), $campeonato->id, '25m');
            $this->command->info("Importação: {$result['importados']} registros importados, {$result['ignorados']} ignorados.");
        } else {
            $this->command->warn('Arquivo torneio_fda.json não encontrado.');
        }
    }
}
