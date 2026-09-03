<?php

use Illuminate\Database\Seeder;
use App\Models\Seguradora;

class SeguradorasTableSeeder extends Seeder
{
    public function run()
    {
        $seguradoras = [
            ['nome' => 'Porto Seguro', 'codigo' => 'PORTO'],
            ['nome' => 'Bradesco Seguros', 'codigo' => 'BRADESCO'],
            ['nome' => 'SulAmérica', 'codigo' => 'SULAMERICA'],
            ['nome' => 'Allianz Seguros', 'codigo' => 'ALLIANZ'],
            ['nome' => 'Mapfre', 'codigo' => 'MAPFRE'],
            ['nome' => 'Tokio Marine', 'codigo' => 'TOKIO'],
            ['nome' => 'Liberty Seguros', 'codigo' => 'LIBERTY'],
            ['nome' => 'HDI Seguros', 'codigo' => 'HDI'],
            ['nome' => 'Zurich Seguros', 'codigo' => 'ZURICH'],
            ['nome' => 'Sompo Seguros', 'codigo' => 'SOMPO'],
            ['nome' => 'Itaú Seguros', 'codigo' => 'ITAU'],
            ['nome' => 'Caixa Seguradora', 'codigo' => 'CAIXA'],
            ['nome' => 'BB Seguros', 'codigo' => 'BB'],
            ['nome' => 'Azul Seguros', 'codigo' => 'AZUL'],
            ['nome' => 'Mitsui Sumitomo', 'codigo' => 'MITSUI'],
        ];

        foreach ($seguradoras as $seguradora) {
            Seguradora::updateOrCreate(
                ['codigo' => $seguradora['codigo']],
                ['nome' => $seguradora['nome'], 'ativa' => true]
            );
        }
    }
}
