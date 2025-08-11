<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUsuariosConquistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios_conquistas', function (Blueprint $table) {
            $table->uuid('usuario_id');
            $table->uuid('conquista_id');
            $table->timestamp('data_obtencao')->useCurrent();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->primary(['usuario_id', 'conquista_id']);
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('conquista_id')->references('id')->on('conquistas');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_usuarios_conquistas
            BEFORE UPDATE ON usuarios_conquistas
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');        // Criar índices
        Schema::table('usuarios_conquistas', function (Blueprint $table) {
            $table->index('usuario_id', 'idx_usuarios_conquistas_usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios_conquistas');
    }
} 