<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePontosInteresseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pontos_interesse', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('nome', 255);
            $table->text('descricao')->nullable();
            $table->uuid('categoria_id');
            $table->uuid('bairro_id')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->foreign('bairro_id')->references('id')->on('bairros');
        });

        // Adicionar coluna de localização usando PostGIS
        DB::statement('ALTER TABLE pontos_interesse ADD COLUMN localizacao GEOMETRY(Point, 4326)');

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_pontos_interesse
            BEFORE UPDATE ON pontos_interesse
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índices
        Schema::table('pontos_interesse', function (Blueprint $table) {
            $table->index('categoria_id', 'idx_pontos_interesse_categoria_id');
        });
        DB::statement('CREATE INDEX idx_pontos_interesse_localizacao ON pontos_interesse USING GIST (localizacao)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pontos_interesse');
    }
} 