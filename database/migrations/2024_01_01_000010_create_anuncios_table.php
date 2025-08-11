<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAnunciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anuncios', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('titulo', 255);
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2)->nullable();
            $table->uuid('categoria_id');
            $table->uuid('usuario_id');
            $table->uuid('bairro_id')->nullable();
            $table->uuid('orgao_id')->nullable();
            $table->string('status', 50)->default('pendente');
            $table->timestamp('validade')->nullable();
            $table->integer('visualizacoes')->default(0);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('bairro_id')->references('id')->on('bairros');
            $table->foreign('orgao_id')->references('id')->on('orgaos_prefeitura');
        });

        // Adicionar coluna de localização usando PostGIS
        DB::statement('ALTER TABLE anuncios ADD COLUMN localizacao GEOMETRY(Point, 4326)');

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_anuncios
            BEFORE UPDATE ON anuncios
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índices
        Schema::table('anuncios', function (Blueprint $table) {
            $table->index('categoria_id', 'idx_anuncios_categoria_id');
        });
        DB::statement('CREATE INDEX idx_anuncios_localizacao ON anuncios USING GIST (localizacao)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anuncios');
    }
} 