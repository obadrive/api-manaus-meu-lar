<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUsuariosMissoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios_missoes', function (Blueprint $table) {
            $table->uuid('usuario_id');
            $table->uuid('missao_id');
            $table->boolean('completada')->default(false);
            $table->timestamp('data_completacao')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->primary(['usuario_id', 'missao_id']);
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('missao_id')->references('id')->on('missoes');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_usuarios_missoes
            BEFORE UPDATE ON usuarios_missoes
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice
        $table->index('usuario_id', 'idx_usuarios_missoes_usuario_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios_missoes');
    }
} 