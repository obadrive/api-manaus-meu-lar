<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Evento::with(['bairro']);
            
            // Filtros básicos
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            
            if ($request->has('bairro_id')) {
                $query->where('bairro_id', $request->bairro_id);
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filtros de data
            if ($request->has('data_inicio')) {
                $query->where('data_inicio', '>=', $request->data_inicio);
            }
            
            if ($request->has('data_fim')) {
                $query->where('data_fim', '<=', $request->data_fim);
            }
            
            if ($request->has('futuros') && $request->boolean('futuros')) {
                $query->where('data_inicio', '>=', now());
            }
            
            $perPage = $request->get('per_page', 15);
            $eventos = $query->orderBy('data_inicio', 'asc')->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $eventos->items(),
                    'pagination' => [
                        'current_page' => $eventos->currentPage(),
                        'last_page' => $eventos->lastPage(),
                        'per_page' => $eventos->perPage(),
                        'total' => $eventos->total(),
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar eventos',
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
                'data_inicio' => 'required|date',
                'data_fim' => 'nullable|date|after_or_equal:data_inicio',
                'bairro_id' => 'nullable|exists:bairros,id',
                'organizador_id' => 'nullable|exists:usuarios,id',
                'orgao_id' => 'nullable|exists:orgaos_prefeitura,id',
                'status' => 'string|in:pendente,aprovado,rejeitado,cancelado',
                'limite_inscritos' => 'nullable|integer|min:1',
                'tipo' => 'required|in:oficial,comunitario',
            ]);

            $dados = $request->all();
            $dados['status'] = $dados['status'] ?? 'pendente';

            // Obter userId do cookie se não fornecido
            if (!isset($dados['organizador_id'])) {
                $userId = $request->cookie('user_id');
                if ($userId) {
                    $dados['organizador_id'] = $userId;
                }
            }

            $evento = Evento::create($dados);
            $evento->load(['bairro']);

            return response()->json([
                'success' => true,
                'message' => 'Evento criado com sucesso',
                'data' => $evento
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
                'message' => 'Erro ao criar evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $evento = Evento::with(['bairro'])->find($id);
            
            if (!$evento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evento não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $evento
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $evento = Evento::find($id);
            
            if (!$evento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evento não encontrado'
                ], 404);
            }

            $request->validate([
                'titulo' => 'sometimes|string|max:255',
                'descricao' => 'nullable|string',
                'data_inicio' => 'sometimes|date',
                'data_fim' => 'nullable|date|after_or_equal:data_inicio',
                'bairro_id' => 'nullable|exists:bairros,id',
                'organizador_id' => 'nullable|exists:usuarios,id',
                'orgao_id' => 'nullable|exists:orgaos_prefeitura,id',
                'status' => 'string|in:pendente,aprovado,rejeitado,cancelado',
                'limite_inscritos' => 'nullable|integer|min:1',
                'tipo' => 'sometimes|in:oficial,comunitario',
            ]);

            $dados = $request->all();

            // Obter userId do cookie se não fornecido
            if (!isset($dados['organizador_id'])) {
                $userId = $request->cookie('user_id');
                if ($userId) {
                    $dados['organizador_id'] = $userId;
                }
            }

            $evento->update($dados);
            $evento->load(['bairro']);

            return response()->json([
                'success' => true,
                'message' => 'Evento atualizado com sucesso',
                'data' => $evento
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
                'message' => 'Erro ao atualizar evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $evento = Evento::find($id);
            
            if (!$evento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evento não encontrado'
                ], 404);
            }

            $evento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Evento removido com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
