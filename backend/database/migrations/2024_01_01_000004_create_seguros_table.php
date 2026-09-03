<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegurosTable extends Migration
{
    public function up()
    {
        Schema::create('seguros', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('seguradora_id');
            $table->unsignedInteger('ramo_id');
            
            // Dados do Segurado
            $table->string('documento_segurado', 20);
            $table->enum('tipo_documento', ['cpf', 'cnpj']);
            $table->string('nome_segurado');
            
            // Dados Financeiros
            $table->decimal('valor_total', 15, 2);
            $table->tinyInteger('quantidade_parcelas')->unsigned();
            $table->decimal('valor_parcela', 15, 2);
            
            // Vigência
            $table->date('inicio_vigencia');
            $table->date('fim_vigencia');
            
            // Endereço
            $table->string('cep', 9);
            $table->string('logradouro');
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('uf', 2);
            
            $table->timestamps();

            // Chaves Estrangeiras
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('seguradora_id')
                ->references('id')
                ->on('seguradoras')
                ->onDelete('restrict');
            
            $table->foreign('ramo_id')
                ->references('id')
                ->on('ramos')
                ->onDelete('restrict');

            // Índices para otimização de consultas
            $table->index('documento_segurado');
            $table->index('tipo_documento');
            $table->index('inicio_vigencia');
            $table->index('fim_vigencia');
            $table->index(['inicio_vigencia', 'fim_vigencia']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seguros');
    }
}
