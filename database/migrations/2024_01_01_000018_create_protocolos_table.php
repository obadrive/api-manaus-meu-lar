<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProtocolosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protocolos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('servico_id');
            $table->uuid('usuario_id');
            $table->string('status', 50)->default('aberto');
            $table->timestamp('data_solicitacao')->useCurrent();
            $table->timestamp('data_conclusao')->nullable();
            $table->uuid('responsavel_id')->nullable(); // Responsável pelo protocolo
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('servico_id')->references('id')->on('servicos');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('responsavel_id')->references('id')->on('usuarios');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_protocolos
            BEFORE UPDATE ON protocolos
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice
        $table->index('status', 'idx_protocolos_status');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('protocolos');
    }
} 