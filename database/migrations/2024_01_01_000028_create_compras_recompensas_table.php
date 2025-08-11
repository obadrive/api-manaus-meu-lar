<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateComprasRecompensasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_recompensas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('usuario_id');
            $table->uuid('recompensa_id');
            $table->timestamp('data_compra')->useCurrent();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('recompensa_id')->references('id')->on('recompensas');
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_compras_recompensas
            BEFORE UPDATE ON compras_recompensas
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column()
        ');        // Criar índices
        Schema::table('compras_recompensas', function (Blueprint $table) {
            $table->index('usuario_id', 'idx_compras_recompensas_usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compras_recompensas');
    }
} 