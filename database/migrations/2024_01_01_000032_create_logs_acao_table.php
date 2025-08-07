<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLogsAcaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs_acao', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('usuario_id')->nullable();
            $table->string('acao', 50); // e.g., 'criacao', 'edicao', 'exclusao'
            $table->uuid('entidade_id')->nullable();
            $table->string('entidade_tipo', 50)->nullable(); // e.g., 'anuncio', 'evento'
            $table->text('detalhes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });

        // Criar índice
        $table->index('usuario_id', 'idx_logs_acao_usuario_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs_acao');
    }
} 