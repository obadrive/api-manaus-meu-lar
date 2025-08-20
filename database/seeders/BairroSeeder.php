<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BairroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bairros = [
            'Centro',
            'Adrianópolis',
            'Chapada',
            'Cidade Nova',
            'Compensa',
            'Educandos',
            'Flores',
            'Japiim',
            'Nossa Senhora das Graças',
            'Parque 10 de Novembro',
            'Petrópolis',
            'Praça 14 de Janeiro',
            'São Raimundo',
            'Tarumã',
            'Vila da Prata',
            'Aleixo',
            'Cachoeirinha',
            'Coroado',
            'Distrito Industrial I',
            'Distrito Industrial II',
            'Dom Pedro',
            'Glória',
            'Jorge Teixeira',
            'Mauazinho',
            'Monte das Oliveiras',
            'Nova Cidade',
            'Puraquequara',
            'Raiz',
            'Redenção',
            'Santa Etelvina',
            'Santa Luzia',
            'Santo Antônio',
            'São Francisco',
            'São Geraldo',
            'São Jorge',
            'São Lázaro',
            'Teresópolis',
            'Vila Buriti',
        ];

        foreach ($bairros as $nome) {
            DB::table('bairros')->insert([
                'id' => Str::uuid(),
                'nome' => $nome,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
