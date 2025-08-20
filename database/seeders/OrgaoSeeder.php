<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrgaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgaos = [
            'Secretaria Municipal de Educação',
            'Secretaria Municipal de Saúde',
            'Secretaria Municipal de Cultura',
            'Secretaria Municipal de Esporte e Lazer',
            'Secretaria Municipal de Meio Ambiente',
            'Secretaria Municipal de Assistência Social',
            'Secretaria Municipal de Infraestrutura',
            'Secretaria Municipal de Turismo',
            'Fundação Municipal de Cultura, Turismo e Eventos',
            'Instituto Municipal de Planejamento Urbano',
        ];

        foreach ($orgaos as $nome) {
            DB::table('orgaos_prefeitura')->insert([
                'id' => Str::uuid(),
                'nome' => $nome,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
