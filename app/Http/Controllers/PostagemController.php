<?php

namespace App\Http\Controllers;

use App\Models\Postagem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostagemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Postagem::with(['bairro', 'usuario']);
            
            // Filtros
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            
            if ($request->has('categoria')) {
                $query->where('categoria', $request->categoria);
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
            
            if ($request->has('fixado')) {
                $query->where('fixado', $request->boolean('fixado'));
            }
            
            // Geolocalização
            if ($request->has('lat') && $request->has('lng')) {
                $raio = $request->get('raio', 5000);
                $query->proximas($request->lat, $request->lng, $raio);
            }
            
            // Filtros especiais
            if ($request->has('oficial') && $request->boolean('oficial')) {
                $query->oficiais();
            }
            
            if ($request->has('comunitario') && $request->boolean('comunitario')) {
                $query->comunitarias();
            }
            
            // Ordenação
            $ordenacao = $request->get('ordenacao', 'recentes');
            switch ($ordenacao) {
                case 'mais_curtidas':
                    $query->maisCurtidas();
                    break;
                case 'mais_comentadas':
                    $query->maisComentadas();
                    break;
                default:
                    $query->recentes();
            }
            
            $perPage = $request->get('per_page', 15);
            $postagens = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $postagens->items(),
                'pagination' => [
                    'current_page' => $postagens->currentPage(),
                    'last_page' => $postagens->lastPage(),
                    'per_page' => $postagens->perPage(),
                    'total' => $postagens->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar postagens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'conteudo' => 'required|string|max:1000',
                'tipo' => 'required|in:geral,oficial,pergunta,recomendacao',
                'categoria' => 'nullable|string|max:100',
                'bairro_id' => 'nullable|exists:bairros,id',
                'usuario_id' => 'nullable|exists:usuarios,id',
                'geometria' => 'nullable|array',
                'geometria.type' => 'required_with:geometria|string|in:Point',
                'geometria.coordinates' => 'required_with:geometria|array|size:2',
                'geometria.coordinates.*' => 'numeric',
                'ativo' => 'boolean',
                'aprovado' => 'boolean',
            ]);

            $dados = $request->all();
            $dados['ativo'] = $dados['ativo'] ?? true;
            $dados['aprovado'] = $dados['aprovado'] ?? false;
            $dados['fixado'] = false;
            $dados['rejeitado'] = false;

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $postagem = Postagem::create($dados);
            $postagem->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Postagem criada com sucesso',
                'data' => $postagem
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
                'message' => 'Erro ao criar postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $postagem = Postagem::with(['bairro', 'usuario', 'curtidas', 'comentarios', 'imagens'])->find($id);
            
            if (!$postagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postagem não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $postagem
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $postagem = Postagem::find($id);
            
            if (!$postagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postagem não encontrada'
                ], 404);
            }

            $request->validate([
                'conteudo' => 'sometimes|string|max:1000',
                'tipo' => 'sometimes|in:geral,oficial,pergunta,recomendacao',
                'categoria' => 'nullable|string|max:100',
                'bairro_id' => 'nullable|exists:bairros,id',
                'usuario_id' => 'nullable|exists:usuarios,id',
                'geometria' => 'sometimes|array',
                'geometria.type' => 'required_with:geometria|string|in:Point',
                'geometria.coordinates' => 'required_with:geometria|array|size:2',
                'geometria.coordinates.*' => 'numeric',
                'ativo' => 'boolean',
                'aprovado' => 'boolean',
                'fixado' => 'boolean',
                'rejeitado' => 'boolean',
                'motivo_rejeicao' => 'nullable|string',
            ]);

            $dados = $request->all();

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $postagem->update($dados);
            $postagem->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Postagem atualizada com sucesso',
                'data' => $postagem
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
                'message' => 'Erro ao atualizar postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $postagem = Postagem::find($id);
            
            if (!$postagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postagem não encontrada'
                ], 404);
            }

            $postagem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Postagem removida com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fixar(string $id): JsonResponse
    {
        try {
            $postagem = Postagem::find($id);
            
            if (!$postagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postagem não encontrada'
                ], 404);
            }

            $postagem->fixar();

            return response()->json([
                'success' => true,
                'message' => 'Postagem fixada com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fixar postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function desfixar(string $id): JsonResponse
    {
        try {
            $postagem = Postagem::find($id);
            
            if (!$postagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postagem não encontrada'
                ], 404);
            }

            $postagem->desfixar();

            return response()->json([
                'success' => true,
                'message' => 'Postagem desfixada com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desfixar postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function aprovar(string $id): JsonResponse
    {
        try {
            $postagem = Postagem::find($id);
            
            if (!$postagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postagem não encontrada'
                ], 404);
            }

            $postagem->aprovar();

            return response()->json([
                'success' => true,
                'message' => 'Postagem aprovada com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejeitar(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'motivo' => 'required|string|max:500'
            ]);

            $postagem = Postagem::find($id);
            
            if (!$postagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postagem não encontrada'
                ], 404);
            }

            $postagem->rejeitar($request->motivo);

            return response()->json([
                'success' => true,
                'message' => 'Postagem rejeitada com sucesso'
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
                'message' => 'Erro ao rejeitar postagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function proximas(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'raio' => 'sometimes|numeric|min:100|max:50000',
                'tipo' => 'nullable|string',
            ]);

            $raio = $request->get('raio', 5000);
            $query = Postagem::with(['bairro', 'usuario'])
                ->proximas($request->lat, $request->lng, $raio)
                ->ativas()
                ->aprovadas()
                ->naoRejeitadas();

            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            $postagens = $query->recentes()->get();

            return response()->json([
                'success' => true,
                'data' => $postagens,
                'params' => [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'raio' => $raio,
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
                'message' => 'Erro ao buscar postagens próximas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(): JsonResponse
    {
        try {
            $totalPostagens = Postagem::count();
            $postagensAtivas = Postagem::where('ativo', true)->count();
            $postagensAprovadas = Postagem::where('aprovado', true)->count();
            $postagensRejeitadas = Postagem::where('rejeitado', true)->count();
            $postagensFixadas = Postagem::where('fixado', true)->count();
            $postagensOficiais = Postagem::where('tipo', 'oficial')->count();
            $postagensComunitarias = Postagem::where('tipo', 'comunitario')->count();
            
            $porTipo = Postagem::selectRaw('tipo, count(*) as total')
                ->groupBy('tipo')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalPostagens,
                    'ativas' => $postagensAtivas,
                    'aprovadas' => $postagensAprovadas,
                    'rejeitadas' => $postagensRejeitadas,
                    'fixadas' => $postagensFixadas,
                    'oficiais' => $postagensOficiais,
                    'comunitarias' => $postagensComunitarias,
                    'por_tipo' => $porTipo
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
