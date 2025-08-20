<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnuncioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Anuncio::with(['bairro', 'usuario']);
            
            // Filtros
            if ($request->has('categoria')) {
                $query->where('categoria', $request->categoria);
            }
            
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            
            if ($request->has('bairro_id')) {
                $query->where('bairro_id', $request->bairro_id);
            }
            
            if ($request->has('ativo')) {
                $query->where('ativo', $request->boolean('ativo'));
            }
            
            if ($request->has('aprovado')) {
                $query->where('aprovado', $request->boolean('aprovado'));
            }
            
            if ($request->has('rejeitado')) {
                $query->where('rejeitado', $request->boolean('rejeitado'));
            }
            
            if ($request->has('condicao')) {
                $query->where('condicao', $request->condicao);
            }
            
            if ($request->has('preco_min')) {
                $query->where('preco', '>=', $request->preco_min);
            }
            
            if ($request->has('preco_max')) {
                $query->where('preco', '<=', $request->preco_max);
            }
            
            // Geolocalização
            if ($request->has('lat') && $request->has('lng')) {
                $raio = $request->get('raio', 5000);
                $query->proximos($request->lat, $request->lng, $raio);
            }
            
            // Filtros especiais
            if ($request->has('oficial') && $request->boolean('oficial')) {
                $query->oficiais();
            }
            
            if ($request->has('comercial') && $request->boolean('comercial')) {
                $query->comerciais();
            }
            
            // Ordenação
            $ordenacao = $request->get('ordenacao', 'recentes');
            switch ($ordenacao) {
                case 'mais_vistos':
                    $query->maisVistos();
                    break;
                case 'mais_favoritados':
                    $query->maisFavoritados();
                    break;
                case 'melhor_avaliados':
                    $query->melhorAvaliados();
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
            
            $perPage = $request->get('per_page', 15);
            $anuncios = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $anuncios->items(),
                'pagination' => [
                    'current_page' => $anuncios->currentPage(),
                    'last_page' => $anuncios->lastPage(),
                    'per_page' => $anuncios->perPage(),
                    'total' => $anuncios->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar anúncios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'titulo' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'categoria' => 'required|string|max:100',
                'tipo' => 'required|in:oficial,comercial',
                'bairro_id' => 'nullable|exists:bairros,id',
                'usuario_id' => 'nullable|exists:usuarios,id',
                'geometria' => 'nullable|array',
                'geometria.type' => 'required_with:geometria|string|in:Point',
                'geometria.coordinates' => 'required_with:geometria|array|size:2',
                'geometria.coordinates.*' => 'numeric',
                'endereco' => 'nullable|string',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'website' => 'nullable|url',
                'preco' => 'nullable|numeric|min:0',
                'preco_negociavel' => 'boolean',
                'condicao' => 'nullable|string|max:50',
                'ativo' => 'boolean',
                'aprovado' => 'boolean',
                'data_expiracao' => 'nullable|date|after:today',
            ]);

            $dados = $request->all();
            $dados['ativo'] = $dados['ativo'] ?? true;
            $dados['aprovado'] = $dados['aprovado'] ?? false;
            $dados['rejeitado'] = false;
            $dados['preco_negociavel'] = $dados['preco_negociavel'] ?? false;
            $dados['data_expiracao'] = $dados['data_expiracao'] ?? now()->addDays(30);

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $anuncio = Anuncio::create($dados);
            $anuncio->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Anúncio criado com sucesso',
                'data' => $anuncio
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
                'message' => 'Erro ao criar anúncio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $anuncio = Anuncio::with(['bairro', 'usuario', 'avaliacoes', 'comentarios', 'imagens', 'favoritos'])->find($id);
            
            if (!$anuncio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anúncio não encontrado'
                ], 404);
            }

            // Incrementar visualização
            $anuncio->incrementarVisualizacao();

            return response()->json([
                'success' => true,
                'data' => $anuncio
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar anúncio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $anuncio = Anuncio::find($id);
            
            if (!$anuncio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anúncio não encontrado'
                ], 404);
            }

            $request->validate([
                'titulo' => 'sometimes|string|max:255',
                'descricao' => 'nullable|string',
                'categoria' => 'sometimes|string|max:100',
                'tipo' => 'sometimes|in:oficial,comercial',
                'bairro_id' => 'nullable|exists:bairros,id',
                'usuario_id' => 'nullable|exists:usuarios,id',
                'geometria' => 'sometimes|array',
                'geometria.type' => 'required_with:geometria|string|in:Point',
                'geometria.coordinates' => 'required_with:geometria|array|size:2',
                'geometria.coordinates.*' => 'numeric',
                'endereco' => 'nullable|string',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'website' => 'nullable|url',
                'preco' => 'nullable|numeric|min:0',
                'preco_negociavel' => 'boolean',
                'condicao' => 'nullable|string|max:50',
                'ativo' => 'boolean',
                'aprovado' => 'boolean',
                'rejeitado' => 'boolean',
                'motivo_rejeicao' => 'nullable|string',
                'data_expiracao' => 'nullable|date',
            ]);

            $dados = $request->all();

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $anuncio->update($dados);
            $anuncio->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Anúncio atualizado com sucesso',
                'data' => $anuncio
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
                'message' => 'Erro ao atualizar anúncio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $anuncio = Anuncio::find($id);
            
            if (!$anuncio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anúncio não encontrado'
                ], 404);
            }

            $anuncio->delete();

            return response()->json([
                'success' => true,
                'message' => 'Anúncio removido com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover anúncio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function proximos(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'raio' => 'sometimes|numeric|min:100|max:50000',
                'categoria' => 'nullable|string',
                'tipo' => 'nullable|in:oficial,comercial',
            ]);

            $raio = $request->get('raio', 5000);
            $query = Anuncio::with(['bairro', 'usuario'])
                ->proximos($request->lat, $request->lng, $raio)
                ->ativos()
                ->aprovados()
                ->naoRejeitados()
                ->naoExpirados();

            if ($request->has('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            $anuncios = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $anuncios,
                'params' => [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'raio' => $raio,
                    'categoria' => $request->categoria,
                    'tipo' => $request->tipo
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
                'message' => 'Erro ao buscar anúncios próximos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function categorias(): JsonResponse
    {
        try {
            $categorias = Anuncio::select('categoria')
                ->distinct()
                ->whereNotNull('categoria')
                ->pluck('categoria');

            return response()->json([
                'success' => true,
                'data' => $categorias
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar categorias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(): JsonResponse
    {
        try {
            $totalAnuncios = Anuncio::count();
            $anunciosAtivos = Anuncio::where('ativo', true)->count();
            $anunciosAprovados = Anuncio::where('aprovado', true)->count();
            $anunciosRejeitados = Anuncio::where('rejeitado', true)->count();
            $anunciosExpirados = Anuncio::where('data_expiracao', '<', now())->count();
            $anunciosOficiais = Anuncio::where('tipo', 'oficial')->count();
            $anunciosComerciais = Anuncio::where('tipo', 'comercial')->count();
            
            $porCategoria = Anuncio::selectRaw('categoria, count(*) as total')
                ->groupBy('categoria')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalAnuncios,
                    'ativos' => $anunciosAtivos,
                    'aprovados' => $anunciosAprovados,
                    'rejeitados' => $anunciosRejeitados,
                    'expirados' => $anunciosExpirados,
                    'oficiais' => $anunciosOficiais,
                    'comerciais' => $anunciosComerciais,
                    'por_categoria' => $porCategoria
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
