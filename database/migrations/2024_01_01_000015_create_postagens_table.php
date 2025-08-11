<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePostagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postagens', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('usuario_id');
            $table->text('conteudo');
            $table->enum('tipo', ['geral', 'comunicado_oficial', 'pergunta', 'recomendacao']);
            $table->uuid('bairro_id')->nullable();
            $table->boolean('fixado')->default(false);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('bairro_id')->references('id')->on('bairros');
        });

        // Adicionar coluna de localização usando PostGIS
        DB::statement('ALTER TABLE postagens ADD COLUMN localizacao GEOMETRY(Point, 4326)');

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_postagens
            BEFORE UPDATE ON postagens
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índices
        Schema::table('postagens', function (Blueprint $table) {
            $table->index('tipo', 'idx_postagens_tipo');
        });
        DB::statement('CREATE INDEX idx_postagens_localizacao ON postagens USING GIST (localizacao)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postagens');
    }
} 