<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInteracoesPostagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interacoes_postagens', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('postagem_id');
            $table->uuid('usuario_id');
            $table->enum('tipo', ['curtida', 'comentario']);
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('postagem_id')->references('id')->on('postagens');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_interacoes_postagens
            BEFORE UPDATE ON interacoes_postagens
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');        // Criar índices
        Schema::table('interacoes_postagens', function (Blueprint $table) {
            $table->index('postagem_id', 'idx_interacoes_postagens_postagem_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interacoes_postagens');
    }
} 