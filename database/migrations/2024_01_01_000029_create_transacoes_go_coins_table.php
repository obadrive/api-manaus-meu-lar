<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTransacoesGoCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transacoes_go_coins', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('usuario_id');
            $table->integer('valor'); // Positivo para ganho, negativo para gasto
            $table->string('descricao', 255)->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_transacoes_go_coins
            BEFORE UPDATE ON transacoes_go_coins
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice
        $table->index('usuario_id', 'idx_transacoes_go_coins_usuario_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transacoes_go_coins');
    }
} 