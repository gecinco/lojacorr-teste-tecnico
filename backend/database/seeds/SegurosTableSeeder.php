<?php

use Illuminate\Database\Seeder;
use App\Models\Seguro;
use App\Models\User;
use App\Models\Seguradora;
use App\Models\Ramo;
use Carbon\Carbon;

class SegurosTableSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $seguradoras = Seguradora::all();
        $ramos = Ramo::all();

        if (!$user || $seguradoras->isEmpty() || $ramos->isEmpty()) {
            $this->command->warn('SegurosTableSeeder ignorado: execute Users/Seguradoras/Ramos seeders antes.');
            return;
        }

        $seguros = [
            [
                'documento_segurado' => '12345678909',
                'tipo_documento' => 'cpf',
                'nome_segurado' => 'João da Silva Santos',
                'valor_total' => 2400.00,
                'quantidade_parcelas' => 12,
                'valor_parcela' => 200.00,
                'inicio_vigencia' => Carbon::now()->subMonths(2),
                'fim_vigencia' => Carbon::now()->addMonths(10),
                'cep' => '01310100',
                'logradouro' => 'Avenida Paulista',
                'numero' => '1000',
                'complemento' => 'Sala 101',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ],
            [
                'documento_segurado' => '98765432100',
                'tipo_documento' => 'cpf',
                'nome_segurado' => 'Maria Oliveira Costa',
                'valor_total' => 3600.00,
                'quantidade_parcelas' => 6,
                'valor_parcela' => 600.00,
                'inicio_vigencia' => Carbon::now()->subMonths(6),
                'fim_vigencia' => Carbon::now()->addMonths(6),
                'cep' => '22041080',
                'logradouro' => 'Rua Barata Ribeiro',
                'numero' => '500',
                'complemento' => 'Apto 302',
                'bairro' => 'Copacabana',
                'cidade' => 'Rio de Janeiro',
                'uf' => 'RJ',
            ],
            [
                'documento_segurado' => '11222333000181',
                'tipo_documento' => 'cnpj',
                'nome_segurado' => 'Tech Solutions LTDA',
                'valor_total' => 12000.00,
                'quantidade_parcelas' => 4,
                'valor_parcela' => 3000.00,
                'inicio_vigencia' => Carbon::now(),
                'fim_vigencia' => Carbon::now()->addYear(),
                'cep' => '30130000',
                'logradouro' => 'Avenida Afonso Pena',
                'numero' => '2000',
                'complemento' => null,
                'bairro' => 'Centro',
                'cidade' => 'Belo Horizonte',
                'uf' => 'MG',
            ],
            [
                'documento_segurado' => '45678912301',
                'tipo_documento' => 'cpf',
                'nome_segurado' => 'Pedro Henrique Lima',
                'valor_total' => 1800.00,
                'quantidade_parcelas' => 3,
                'valor_parcela' => 600.00,
                'inicio_vigencia' => Carbon::now()->subYear(),
                'fim_vigencia' => Carbon::now()->subMonth(),
                'cep' => '80010000',
                'logradouro' => 'Rua XV de Novembro',
                'numero' => '100',
                'complemento' => null,
                'bairro' => 'Centro',
                'cidade' => 'Curitiba',
                'uf' => 'PR',
            ],
            [
                'documento_segurado' => '78912345600',
                'tipo_documento' => 'cpf',
                'nome_segurado' => 'Ana Carolina Ferreira',
                'valor_total' => 5000.00,
                'quantidade_parcelas' => 10,
                'valor_parcela' => 500.00,
                'inicio_vigencia' => Carbon::now()->addMonth(),
                'fim_vigencia' => Carbon::now()->addMonths(13),
                'cep' => '90010000',
                'logradouro' => 'Rua dos Andradas',
                'numero' => '1500',
                'complemento' => 'Sala 501',
                'bairro' => 'Centro Histórico',
                'cidade' => 'Porto Alegre',
                'uf' => 'RS',
            ],
        ];

        foreach ($seguros as $index => $seguroData) {
            Seguro::updateOrCreate(
                ['documento_segurado' => $seguroData['documento_segurado']],
                array_merge($seguroData, [
                    'user_id' => $user->id,
                    'seguradora_id' => $seguradoras[$index % $seguradoras->count()]->id,
                    'ramo_id' => $ramos[$index % $ramos->count()]->id,
                ])
            );
        }
    }
}
