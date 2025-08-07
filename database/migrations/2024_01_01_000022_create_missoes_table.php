<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMissoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missoes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('nome', 255);
            $table->text('descricao')->nullable();
            $table->integer('recompensa'); // GoCoins
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        // Criar trigger para updated_at
        DB::statement('
            CREATE TRIGGER trigger_update_updated_at_missoes
            BEFORE UPDATE ON missoes
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
        Schema::dropIfExists('missoes');
    }
} 