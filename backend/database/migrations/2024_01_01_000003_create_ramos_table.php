<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRamosTable extends Migration
{
    public function up()
    {
        Schema::create('ramos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('codigo', 20)->unique();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('codigo');
            $table->index('ativo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ramos');
    }
}
