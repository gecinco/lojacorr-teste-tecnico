<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Como todas as queries de listagem filtram por user_id, ele precisa ser o
 * PREFIXO dos índices compostos para que o MariaDB possa usá-los.
 * Também adicionamos um índice único (user_id, documento_segurado) para
 * acelerar o filtro por documento na tela do corretor.
 */
class AddCompositeUserIndexesToSegurosTable extends Migration
{
    public function up()
    {
        Schema::table('seguros', function (Blueprint $table) {
            $table->index(['user_id', 'seguradora_id'], 'seguros_user_seguradora_idx');
            $table->index(['user_id', 'ramo_id'], 'seguros_user_ramo_idx');
            $table->index(['user_id', 'documento_segurado'], 'seguros_user_documento_idx');
            $table->index(['user_id', 'inicio_vigencia'], 'seguros_user_inicio_vig_idx');
            $table->index(['user_id', 'fim_vigencia'], 'seguros_user_fim_vig_idx');
        });
    }

    public function down()
    {
        Schema::table('seguros', function (Blueprint $table) {
            $table->dropIndex('seguros_user_seguradora_idx');
            $table->dropIndex('seguros_user_ramo_idx');
            $table->dropIndex('seguros_user_documento_idx');
            $table->dropIndex('seguros_user_inicio_vig_idx');
            $table->dropIndex('seguros_user_fim_vig_idx');
        });
    }
}
