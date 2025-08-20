<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter IDs dos bairros e órgãos
        $bairros = DB::table('bairros')->pluck('id')->toArray();
        $orgaos = DB::table('orgaos_prefeitura')->pluck('id')->toArray();
        
        // Se não houver bairros ou órgãos, criar alguns básicos
        if (empty($bairros)) {
            $bairroId = Str::uuid();
            DB::table('bairros')->insert([
                'id' => $bairroId,
                'nome' => 'Centro',
                'zona' => 'Centro-Sul',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $bairros = [$bairroId];
        }
        
        if (empty($orgaos)) {
            $orgaoId = Str::uuid();
            DB::table('orgaos_prefeitura')->insert([
                'id' => $orgaoId,
                'nome' => 'Secretaria Municipal de Cultura',
                'sigla' => 'SEMC',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $orgaos = [$orgaoId];
        }

        $eventos = [
            [
                'titulo' => 'Festival de Cultura Amazônica',
                'descricao' => 'Celebração da cultura regional com música, dança e artesanato local. Uma oportunidade única para conhecer as tradições amazônicas.',
                'data_inicio' => Carbon::now()->addDays(5)->setTime(18, 0),
                'data_fim' => Carbon::now()->addDays(5)->setTime(22, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 500,
                'tipo' => 'oficial',
            ],
            [
                'titulo' => 'Feira de Produtos Regionais',
                'descricao' => 'Feira com produtos típicos da região amazônica, incluindo frutas, artesanato e comidas tradicionais.',
                'data_inicio' => Carbon::now()->addDays(3)->setTime(8, 0),
                'data_fim' => Carbon::now()->addDays(3)->setTime(18, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 200,
                'tipo' => 'comunitario',
            ],
            [
                'titulo' => 'Workshop de Sustentabilidade',
                'descricao' => 'Aprenda sobre práticas sustentáveis e como contribuir para a preservação da Amazônia.',
                'data_inicio' => Carbon::now()->addDays(7)->setTime(14, 0),
                'data_fim' => Carbon::now()->addDays(7)->setTime(17, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 50,
                'tipo' => 'oficial',
            ],
            [
                'titulo' => 'Cine Amazônia',
                'descricao' => 'Exibição de filmes sobre a Amazônia e sua biodiversidade. Sessão gratuita ao ar livre.',
                'data_inicio' => Carbon::now()->addDays(10)->setTime(19, 0),
                'data_fim' => Carbon::now()->addDays(10)->setTime(21, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 300,
                'tipo' => 'oficial',
            ],
            [
                'titulo' => 'Aulão de Zumba',
                'descricao' => 'Aula gratuita de zumba para toda a comunidade. Traga sua garrafa de água e venha se exercitar!',
                'data_inicio' => Carbon::now()->addDays(2)->setTime(17, 0),
                'data_fim' => Carbon::now()->addDays(2)->setTime(18, 30),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 100,
                'tipo' => 'comunitario',
            ],
            [
                'titulo' => 'Palestra sobre Saúde Mental',
                'descricao' => 'Palestra sobre a importância da saúde mental e como buscar ajuda quando necessário.',
                'data_inicio' => Carbon::now()->addDays(8)->setTime(15, 0),
                'data_fim' => Carbon::now()->addDays(8)->setTime(16, 30),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 80,
                'tipo' => 'oficial',
            ],
            [
                'titulo' => 'Mutirão de Limpeza',
                'descricao' => 'Ajude a manter nossa cidade limpa! Mutirão de limpeza em parceria com a comunidade.',
                'data_inicio' => Carbon::now()->addDays(4)->setTime(7, 0),
                'data_fim' => Carbon::now()->addDays(4)->setTime(11, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 150,
                'tipo' => 'comunitario',
            ],
            [
                'titulo' => 'Exposição de Arte Local',
                'descricao' => 'Exposição de artistas locais com obras inspiradas na cultura amazônica.',
                'data_inicio' => Carbon::now()->addDays(6)->setTime(10, 0),
                'data_fim' => Carbon::now()->addDays(6)->setTime(18, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 200,
                'tipo' => 'oficial',
            ],
            [
                'titulo' => 'Oficina de Culinária Regional',
                'descricao' => 'Aprenda a preparar pratos típicos da região amazônica com chefs locais.',
                'data_inicio' => Carbon::now()->addDays(12)->setTime(14, 0),
                'data_fim' => Carbon::now()->addDays(12)->setTime(17, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 30,
                'tipo' => 'comunitario',
            ],
            [
                'titulo' => 'Show de Música Regional',
                'descricao' => 'Apresentação de músicos locais com repertório de músicas regionais e folclóricas.',
                'data_inicio' => Carbon::now()->addDays(15)->setTime(20, 0),
                'data_fim' => Carbon::now()->addDays(15)->setTime(23, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 400,
                'tipo' => 'oficial',
            ],
            [
                'titulo' => 'Campanha de Vacinação',
                'descricao' => 'Campanha de vacinação contra gripe e outras doenças. Traga sua carteira de vacinação.',
                'data_inicio' => Carbon::now()->addDays(1)->setTime(8, 0),
                'data_fim' => Carbon::now()->addDays(1)->setTime(17, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => null,
                'tipo' => 'oficial',
            ],
            [
                'titulo' => 'Encontro de Empreendedores',
                'descricao' => 'Networking e palestras sobre empreendedorismo e inovação em Manaus.',
                'data_inicio' => Carbon::now()->addDays(9)->setTime(19, 0),
                'data_fim' => Carbon::now()->addDays(9)->setTime(22, 0),
                'bairro_id' => $bairros[array_rand($bairros)],
                'orgao_id' => $orgaos[array_rand($orgaos)],
                'status' => 'aprovado',
                'limite_inscritos' => 120,
                'tipo' => 'comunitario',
            ],
        ];

        foreach ($eventos as $evento) {
            DB::table('eventos')->insert([
                'id' => Str::uuid(),
                'titulo' => $evento['titulo'],
                'descricao' => $evento['descricao'],
                'data_inicio' => $evento['data_inicio'],
                'data_fim' => $evento['data_fim'],
                'bairro_id' => $evento['bairro_id'],
                'organizador_id' => null, // Será preenchido quando houver usuários
                'orgao_id' => $evento['orgao_id'],
                'status' => $evento['status'],
                'limite_inscritos' => $evento['limite_inscritos'],
                'tipo' => $evento['tipo'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
