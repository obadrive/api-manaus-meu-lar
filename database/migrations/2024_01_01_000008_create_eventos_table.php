<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('titulo', 255);
            $table->text('descricao')->nullable();
            $table->timestamp('data_inicio');
            $table->timestamp('data_fim')->nullable();
            $table->uuid('bairro_id')->nullable();
            $table->uuid('organizador_id')->nullable();
            $table->uuid('orgao_id')->nullable();
            $table->string('status', 50)->default('pendente');
            $table->integer('limite_inscritos')->nullable();
            $table->string('tipo', 50)->nullable(); // 'oficial' ou 'comunitario'
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('bairro_id')->references('id')->on('bairros');
            $table->foreign('organizador_id')->references('id')->on('usuarios');
            $table->foreign('orgao_id')->references('id')->on('orgaos_prefeitura');
        });

        // Adicionar coluna de localização usando PostGIS
        DB::statement('ALTER TABLE eventos ADD COLUMN localizacao GEOMETRY(Point, 4326)');

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_eventos
            BEFORE UPDATE ON eventos
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índices
        Schema::table('eventos', function (Blueprint $table) {
            $table->index('data_inicio', 'idx_eventos_data_inicio');
        });
        DB::statement('CREATE INDEX idx_eventos_localizacao ON eventos USING GIST (localizacao)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventos');
    }
} 