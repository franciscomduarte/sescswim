<?php

namespace App\Services;

use Carbon\Carbon;

class CategoriaEsportiva
{
    /**
     * Retorna o código da categoria esportiva baseado na data de nascimento.
     * Idade esportiva = ano atual − ano de nascimento.
     * Idades < 6 ou > 99 são tratadas como Senior.
     */
    public static function calcular(?Carbon $dataNascimento): string
    {
        if (!$dataNascimento) {
            return 'Senior';
        }

        $idade = now()->year - $dataNascimento->year;

        return match (true) {
            $idade === 6               => 'MINIMIRIM',
            $idade >= 7  && $idade <= 8  => 'PREMIRIM',
            $idade === 9               => 'MIR1',
            $idade === 10              => 'MIR2',
            $idade === 11              => 'PET1',
            $idade === 12              => 'PET2',
            $idade === 13              => 'INF1',
            $idade === 14              => 'INF2',
            $idade === 15              => 'JUV1',
            $idade === 16              => 'JUV2',
            $idade === 17              => 'JR1',
            $idade >= 18 && $idade <= 19 => 'JR2',
            $idade >= 20 && $idade <= 99 => 'Senior',
            default                    => 'Senior', // <6 ou >99
        };
    }

    /**
     * Retorna o rótulo legível da categoria.
     */
    public static function rotulo(string $categoria): string
    {
        return match ($categoria) {
            'MINIMIRIM' => 'Mini-mirim',
            'PREMIRIM'  => 'Pré-mirim',
            'MIR1'      => 'Mirim 1',
            'MIR2'      => 'Mirim 2',
            'PET1'      => 'Petiz 1',
            'PET2'      => 'Petiz 2',
            'INF1'      => 'Infantil 1',
            'INF2'      => 'Infantil 2',
            'JUV1'      => 'Juvenil 1',
            'JUV2'      => 'Juvenil 2',
            'JR1'       => 'Júnior 1',
            'JR2'       => 'Júnior 2',
            'Senior'    => 'Sênior',
            default     => $categoria,
        };
    }

    /**
     * Indica se a categoria possui índices diferenciados por temporada (Verão/Inverno).
     * Apenas INF1, INF2, JUV1 e JUV2 têm índices sazonais.
     */
    public static function temSazonalidade(string $categoria): bool
    {
        return in_array($categoria, ['INF1', 'INF2', 'JUV1', 'JUV2']);
    }

    /**
     * Retorna todas as categorias em ordem crescente de idade.
     */
    public static function todas(): array
    {
        return ['MINIMIRIM', 'PREMIRIM', 'MIR1', 'MIR2', 'PET1', 'PET2',
                'INF1', 'INF2', 'JUV1', 'JUV2', 'JR1', 'JR2', 'Senior'];
    }
}
