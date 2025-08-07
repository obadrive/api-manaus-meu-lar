<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateContatosAnunciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contatos_anuncios', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('anuncio_id');
            $table->string('tipo', 50); // e.g., 'telefone', 'email', 'whatsapp'
            $table->string('valor', 255);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('anuncio_id')->references('id')->on('anuncios');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_contatos_anuncios
            BEFORE UPDATE ON contatos_anuncios
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');

        // Criar índice
        $table->index('anuncio_id', 'idx_contatos_anuncios_anuncio_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contatos_anuncios');
    }
} 