<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFavoritosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('usuario_id');
            $table->uuid('anuncio_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('anuncio_id')->references('id')->on('anuncios');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_favoritos
            BEFORE UPDATE ON favoritos
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice
        $table->index('usuario_id', 'idx_favoritos_usuario_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('favoritos');
    }
} 