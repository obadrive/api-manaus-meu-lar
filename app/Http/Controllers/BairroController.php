<?php

namespace App\Http\Controllers;

use App\Models\Bairro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BairroController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Bairro::query();
            
            // Filtros
            if ($request->has('nome')) {
                $query->where('nome', 'like', '%' . $request->nome . '%');
            }
            
            // Geolocalização
            if ($request->has('lat') && $request->has('lng')) {
                $raio = $request->get('raio', 5000);
                $query->proximos($request->lat, $request->lng, $raio);
            }
            
            $perPage = $request->get('per_page', 15);
            $bairros = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $bairros->items(),
                'pagination' => [
                    'current_page' => $bairros->currentPage(),
                    'last_page' => $bairros->lastPage(),
                    'per_page' => $bairros->perPage(),
                    'total' => $bairros->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar bairros',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'geometria' => 'required|array',
                'geometria.type' => 'required|string|in:Polygon',
                'geometria.coordinates' => 'required|array',
            ]);

            $dados = $request->all();
            
            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $bairro = Bairro::create($dados);

            return response()->json([
                'success' => true,
                'message' => 'Bairro criado com sucesso',
                'data' => $bairro
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $bairro = Bairro::find($id);
            
            if (!$bairro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bairro não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $bairro
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $bairro = Bairro::find($id);
            
            if (!$bairro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bairro não encontrado'
                ], 404);
            }

            $request->validate([
                'nome' => 'sometimes|string|max:255',
                'geometria' => 'sometimes|array',
                'geometria.type' => 'required_with:geometria|string|in:Polygon',
                'geometria.coordinates' => 'required_with:geometria|array',
            ]);

            $dados = $request->all();

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $bairro->update($dados);

            return response()->json([
                'success' => true,
                'message' => 'Bairro atualizado com sucesso',
                'data' => $bairro
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $bairro = Bairro::find($id);
            
            if (!$bairro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bairro não encontrado'
                ], 404);
            }

            $bairro->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bairro removido com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $bairro = Bairro::withTrashed()->find($id);
            
            if (!$bairro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bairro não encontrado'
                ], 404);
            }

            if (!$bairro->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bairro não está removido'
                ], 400);
            }

            $bairro->restore();

            return response()->json([
                'success' => true,
                'message' => 'Bairro restaurado com sucesso',
                'data' => $bairro
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function proximos(Request $request, string $id): JsonResponse
    {
        try {
            $bairro = Bairro::find($id);
            
            if (!$bairro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bairro não encontrado'
                ], 404);
            }

            $request->validate([
                'raio' => 'sometimes|numeric|min:100|max:50000',
            ]);

            $raio = $request->get('raio', 5000);
            
            // Extrair coordenadas do centro do bairro
            $centro = DB::select("SELECT ST_X(ST_Centroid(geometria)) as lng, ST_Y(ST_Centroid(geometria)) as lat FROM bairros WHERE id = ?", [$id]);
            
            if (empty($centro)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível calcular o centro do bairro'
                ], 400);
            }

            $lat = $centro[0]->lat;
            $lng = $centro[0]->lng;

            $bairrosProximos = Bairro::proximos($lat, $lng, $raio)
                ->where('id', '!=', $id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $bairrosProximos,
                'params' => [
                    'bairro_id' => $id,
                    'lat' => $lat,
                    'lng' => $lng,
                    'raio' => $raio
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar bairros próximos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(string $id): JsonResponse
    {
        try {
            $bairro = Bairro::find($id);
            
            if (!$bairro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bairro não encontrado'
                ], 404);
            }

            // Contar usuários no bairro
            $totalUsuarios = DB::table('usuarios')->where('bairro_id', $id)->count();
            $usuariosAtivos = DB::table('usuarios')->where('bairro_id', $id)->where('ativo', true)->count();
            
            // Contar pontos de interesse
            $totalPontosInteresse = DB::table('pontos_interesse')->where('bairro_id', $id)->count();
            $pontosAtivos = DB::table('pontos_interesse')->where('bairro_id', $id)->where('ativo', true)->count();
            
            // Contar eventos
            $totalEventos = DB::table('eventos')->where('bairro_id', $id)->count();
            $eventosFuturos = DB::table('eventos')->where('bairro_id', $id)->where('data_inicio', '>=', now()->toDateString())->count();
            
            // Contar anúncios
            $totalAnuncios = DB::table('anuncios')->where('bairro_id', $id)->count();
            $anunciosAtivos = DB::table('anuncios')->where('bairro_id', $id)->where('ativo', true)->count();
            
            // Contar postagens
            $totalPostagens = DB::table('postagens')->where('bairro_id', $id)->count();
            $postagensAtivas = DB::table('postagens')->where('bairro_id', $id)->where('ativo', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'bairro' => $bairro,
                    'usuarios' => [
                        'total' => $totalUsuarios,
                        'ativos' => $usuariosAtivos
                    ],
                    'pontos_interesse' => [
                        'total' => $totalPontosInteresse,
                        'ativos' => $pontosAtivos
                    ],
                    'eventos' => [
                        'total' => $totalEventos,
                        'futuros' => $eventosFuturos
                    ],
                    'anuncios' => [
                        'total' => $totalAnuncios,
                        'ativos' => $anunciosAtivos
                    ],
                    'postagens' => [
                        'total' => $totalPostagens,
                        'ativas' => $postagensAtivas
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas do bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
