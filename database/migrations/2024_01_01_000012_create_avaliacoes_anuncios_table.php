<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAvaliacoesAnunciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacoes_anuncios', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('anuncio_id');
            $table->uuid('usuario_id');
            $table->integer('nota')->unsigned(); // 1-5 estrelas
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('anuncio_id')->references('id')->on('anuncios');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            
            // Constraint para nota entre 1 e 5
            $table->check('nota >= 1 AND nota <= 5');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_avaliacoes_anuncios
            BEFORE UPDATE ON avaliacoes_anuncios
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice
        $table->index('anuncio_id', 'idx_avaliacoes_anuncios_anuncio_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacoes_anuncios');
    }
} 