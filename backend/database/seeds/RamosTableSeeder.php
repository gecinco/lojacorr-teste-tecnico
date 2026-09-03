<?php

use Illuminate\Database\Seeder;
use App\Models\Ramo;

class RamosTableSeeder extends Seeder
{
    public function run()
    {
        $ramos = [
            ['nome' => 'Automóvel', 'codigo' => 'AUTO'],
            ['nome' => 'Residencial', 'codigo' => 'RES'],
            ['nome' => 'Vida Individual', 'codigo' => 'VIDA'],
            ['nome' => 'Vida em Grupo', 'codigo' => 'VIDAG'],
            ['nome' => 'Empresarial', 'codigo' => 'EMP'],
            ['nome' => 'Condomínio', 'codigo' => 'COND'],
            ['nome' => 'Responsabilidade Civil', 'codigo' => 'RC'],
            ['nome' => 'Transporte', 'codigo' => 'TRANS'],
            ['nome' => 'Garantia', 'codigo' => 'GAR'],
            ['nome' => 'Saúde', 'codigo' => 'SAUDE'],
            ['nome' => 'Odontológico', 'codigo' => 'ODONTO'],
            ['nome' => 'Viagem', 'codigo' => 'VIAGEM'],
            ['nome' => 'Rural', 'codigo' => 'RURAL'],
            ['nome' => 'Engenharia', 'codigo' => 'ENG'],
            ['nome' => 'Fiança Locatícia', 'codigo' => 'FIANCA'],
        ];

        foreach ($ramos as $ramo) {
            Ramo::updateOrCreate(
                ['codigo' => $ramo['codigo']],
                ['nome' => $ramo['nome'], 'ativo' => true]
            );
        }
    }
}
