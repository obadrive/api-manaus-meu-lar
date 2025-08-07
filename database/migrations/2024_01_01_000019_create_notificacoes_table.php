<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotificacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('usuario_id');
            $table->text('mensagem');
            $table->boolean('lida')->default(false);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_notificacoes
            BEFORE UPDATE ON notificacoes
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice
        $table->index('lida', 'idx_notificacoes_lida');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificacoes');
    }
} 