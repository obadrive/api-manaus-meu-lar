<?php

namespace App\Http\Controllers;

use App\Http\Requests\BairroRequest;
use App\Http\Resources\BairroResource;
use App\Services\BairroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class BairroController extends Controller
{
    public function __construct(
        private BairroService $bairroService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filtros = $request->only(['nome', 'lat', 'lng', 'raio']);
            $perPage = $request->get('per_page', 15);
            
            $bairros = $this->bairroService->listarBairros($filtros, $perPage);

            return response()->json([
                'success' => true,
                'data' => BairroResource::collection($bairros->items()),
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(BairroRequest $request): JsonResponse
    {
        try {
            $bairro = $this->bairroService->criarBairro($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Bairro criado com sucesso',
                'data' => new BairroResource($bairro)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $bairro = $this->bairroService->buscarPorId($id);

            return response()->json([
                'success' => true,
                'data' => new BairroResource($bairro)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bairro não encontrado'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BairroRequest $request, string $id): JsonResponse
    {
        try {
            $bairro = $this->bairroService->atualizarBairro($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Bairro atualizado com sucesso',
                'data' => new BairroResource($bairro)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bairro não encontrado'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->bairroService->removerBairro($id);

            return response()->json([
                'success' => true,
                'message' => 'Bairro removido com sucesso'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bairro não encontrado'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar bairro removido (soft delete)
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $bairro = $this->bairroService->restaurarBairro($id);

            return response()->json([
                'success' => true,
                'message' => 'Bairro restaurado com sucesso',
                'data' => new BairroResource($bairro)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bairro não encontrado'
            ], 404);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar bairro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar bairros por proximidade
     */
    public function proximos(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'raio' => 'sometimes|numeric|min:100|max:50000', // 100m a 50km
            ]);

            $raio = $request->get('raio', 5000); // 5km padrão
            
            $bairros = $this->bairroService->buscarPorProximidade(
                $request->lat, 
                $request->lng, 
                $raio
            );

            return response()->json([
                'success' => true,
                'data' => BairroResource::collection($bairros),
                'params' => [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'raio' => $raio
                ]
            ], 200);

        } catch (ValidationException $e) {
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

    /**
     * Obter estatísticas dos bairros
     */
    public function estatisticas(): JsonResponse
    {
        try {
            $estatisticas = $this->bairroService->obterEstatisticas();

            return response()->json([
                'success' => true,
                'data' => $estatisticas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar bairros com contagem de usuários
     */
    public function comContagemUsuarios(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $bairros = $this->bairroService->listarComContagemUsuarios($perPage);

            return response()->json([
                'success' => true,
                'data' => BairroResource::collection($bairros->items()),
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
                'message' => 'Erro ao listar bairros com contagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar bairros por região (bounding box)
     */
    public function porRegiao(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'min_lat' => 'required|numeric|between:-90,90',
                'max_lat' => 'required|numeric|between:-90,90|gte:min_lat',
                'min_lng' => 'required|numeric|between:-180,180',
                'max_lng' => 'required|numeric|between:-180,180|gte:min_lng',
            ]);

            $bairros = $this->bairroService->buscarPorRegiao(
                $request->min_lat,
                $request->max_lat,
                $request->min_lng,
                $request->max_lng
            );

            return response()->json([
                'success' => true,
                'data' => BairroResource::collection($bairros),
                'params' => [
                    'min_lat' => $request->min_lat,
                    'max_lat' => $request->max_lat,
                    'min_lng' => $request->min_lng,
                    'max_lng' => $request->max_lng,
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar bairros por região',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
