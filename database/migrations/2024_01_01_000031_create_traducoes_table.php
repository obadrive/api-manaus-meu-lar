<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTraducoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('traducoes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('entidade_id');
            $table->string('entidade_tipo', 50); // e.g., 'servico', 'evento'
            $table->string('campo', 50); // e.g., 'nome', 'descricao'
            $table->string('lingua', 10); // e.g., 'pt', 'en'
            $table->text('valor');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_traducoes
            BEFORE UPDATE ON traducoes
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('traducoes');
    }
} 