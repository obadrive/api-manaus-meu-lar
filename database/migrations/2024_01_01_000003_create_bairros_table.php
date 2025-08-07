<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBairrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bairros', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('nome', 255);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        // Adicionar coluna de geometria usando PostGIS
        DB::statement('ALTER TABLE bairros ADD COLUMN geometria GEOMETRY(Polygon, 4326)');

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_bairros
            BEFORE UPDATE ON bairros
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice geoespacial
        DB::statement('CREATE INDEX idx_bairros_geometria ON bairros USING GIST (geometria)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bairros');
    }
} 