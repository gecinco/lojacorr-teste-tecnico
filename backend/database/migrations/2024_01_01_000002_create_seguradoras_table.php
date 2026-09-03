<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeguradorasTable extends Migration
{
    public function up()
    {
        Schema::create('seguradoras', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('codigo', 20)->unique();
            $table->boolean('ativa')->default(true);
            $table->timestamps();

            $table->index('codigo');
            $table->index('ativa');
        });
    }

    public function down()
    {
        Schema::dropIfExists('seguradoras');
    }
}
