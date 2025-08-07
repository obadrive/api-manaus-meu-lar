<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateConfiguracoesUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracoes_usuario', function (Blueprint $table) {
            $table->uuid('usuario_id')->primary();
            $table->string('idioma', 10)->default('pt');
            $table->string('tema', 50)->default('claro');
            $table->boolean('notificacoes_ativadas')->default(true);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_configuracoes_usuario
            BEFORE UPDATE ON configuracoes_usuario
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracoes_usuario');
    }
} 