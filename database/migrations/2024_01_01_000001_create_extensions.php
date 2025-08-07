<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateExtensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ativar extensão uuid-ossp
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        
        // Ativar extensão postgis
        DB::statement('CREATE EXTENSION IF NOT EXISTS "postgis"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remover extensões
        DB::statement('DROP EXTENSION IF EXISTS "postgis"');
        DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp"');
    }
} 