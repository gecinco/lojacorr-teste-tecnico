<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@lojacorr.com.br'],
            [
                'name' => 'Administrador Lojacorr',
                'password' => Hash::make('lojacorr2024'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'corretor@lojacorr.com.br'],
            [
                'name' => 'Corretor Teste',
                'password' => Hash::make('corretor123'),
            ]
        );
    }
}
