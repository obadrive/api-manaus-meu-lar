<?php

namespace App\Http\Controllers;

use App\Models\PontoInteresse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PontoInteresseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PontoInteresse::with(['bairro', 'usuario']);
            
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
            
            if ($request->has('verificado')) {
                $query->where('verificado', $request->boolean('verificado'));
            }
            
            if ($request->has('nome')) {
                $query->where('nome', 'like', '%' . $request->nome . '%');
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
            
            $perPage = $request->get('per_page', 15);
            $pontos = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $pontos->items(),
                'pagination' => [
                    'current_page' => $pontos->currentPage(),
                    'last_page' => $pontos->lastPage(),
                    'per_page' => $pontos->perPage(),
                    'total' => $pontos->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar pontos de interesse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'categoria' => 'required|string|max:100',
                'tipo' => 'required|in:oficial,comercial',
                'bairro_id' => 'nullable|exists:bairros,id',
                'usuario_id' => 'nullable|exists:usuarios,id',
                'geometria' => 'required|array',
                'geometria.type' => 'required|string|in:Point',
                'geometria.coordinates' => 'required|array|size:2',
                'geometria.coordinates.*' => 'numeric',
                'endereco' => 'nullable|string',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'website' => 'nullable|url',
                'horario_funcionamento' => 'nullable|array',
                'ativo' => 'boolean',
                'verificado' => 'boolean',
            ]);

            $dados = $request->all();
            $dados['ativo'] = $dados['ativo'] ?? true;
            $dados['verificado'] = $dados['verificado'] ?? false;

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $ponto = PontoInteresse::create($dados);
            $ponto->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Ponto de interesse criado com sucesso',
                'data' => $ponto
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
                'message' => 'Erro ao criar ponto de interesse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $ponto = PontoInteresse::with(['bairro', 'usuario', 'avaliacoes', 'imagens'])->find($id);
            
            if (!$ponto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ponto de interesse não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $ponto
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar ponto de interesse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $ponto = PontoInteresse::find($id);
            
            if (!$ponto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ponto de interesse não encontrado'
                ], 404);
            }

            $request->validate([
                'nome' => 'sometimes|string|max:255',
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
                'horario_funcionamento' => 'nullable|array',
                'ativo' => 'boolean',
                'verificado' => 'boolean',
            ]);

            $dados = $request->all();

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $ponto->update($dados);
            $ponto->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Ponto de interesse atualizado com sucesso',
                'data' => $ponto
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
                'message' => 'Erro ao atualizar ponto de interesse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $ponto = PontoInteresse::find($id);
            
            if (!$ponto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ponto de interesse não encontrado'
                ], 404);
            }

            $ponto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ponto de interesse removido com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover ponto de interesse',
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
            $query = PontoInteresse::with(['bairro', 'usuario'])
                ->proximos($request->lat, $request->lng, $raio)
                ->ativos();

            if ($request->has('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            $pontos = $query->get();

            return response()->json([
                'success' => true,
                'data' => $pontos,
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
                'message' => 'Erro ao buscar pontos próximos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function categorias(): JsonResponse
    {
        try {
            $categorias = PontoInteresse::select('categoria')
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
            $totalPontos = PontoInteresse::count();
            $pontosAtivos = PontoInteresse::where('ativo', true)->count();
            $pontosVerificados = PontoInteresse::where('verificado', true)->count();
            $pontosOficiais = PontoInteresse::where('tipo', 'oficial')->count();
            $pontosComerciais = PontoInteresse::where('tipo', 'comercial')->count();
            
            $porCategoria = PontoInteresse::selectRaw('categoria, count(*) as total')
                ->groupBy('categoria')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalPontos,
                    'ativos' => $pontosAtivos,
                    'verificados' => $pontosVerificados,
                    'oficiais' => $pontosOficiais,
                    'comerciais' => $pontosComerciais,
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
