<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDocumentosProtocolosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos_protocolos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('protocolo_id');
            $table->string('url', 255);
            $table->string('tipo', 50)->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('protocolo_id')->references('id')->on('protocolos');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_documentos_protocolos
            BEFORE UPDATE ON documentos_protocolos
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');        // Criar índices
        Schema::table('documentos_protocolos', function (Blueprint $table) {
            $table->index('protocolo_id', 'idx_documentos_protocolos_protocolo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documentos_protocolos');
    }
} 