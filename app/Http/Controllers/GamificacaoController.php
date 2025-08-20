<?php

namespace App\Http\Controllers;

use App\Models\Gamificacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamificacaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Gamificacao::with(['usuario']);
            
            // Filtros
            if ($request->has('usuario_id')) {
                $query->where('usuario_id', $request->usuario_id);
            }
            
            if ($request->has('nivel_min')) {
                $query->where('nivel', '>=', $request->nivel_min);
            }
            
            if ($request->has('nivel_max')) {
                $query->where('nivel', '<=', $request->nivel_max);
            }
            
            $perPage = $request->get('per_page', 15);
            $gamificacoes = $query->orderBy('nivel', 'desc')->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $gamificacoes->items(),
                'pagination' => [
                    'current_page' => $gamificacoes->currentPage(),
                    'last_page' => $gamificacoes->lastPage(),
                    'per_page' => $gamificacoes->perPage(),
                    'total' => $gamificacoes->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar gamificações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'usuario_id' => 'required|exists:usuarios,id',
                'nivel' => 'sometimes|integer|min:1',
                'xp_total' => 'sometimes|integer|min:0',
                'gocoins' => 'sometimes|integer|min:0',
                'avatar_atual' => 'nullable|string',
                'conquistas_desbloqueadas' => 'nullable|array',
                'missoes_ativas' => 'nullable|array',
                'missoes_concluidas' => 'nullable|array',
            ]);

            $dados = $request->all();
            $dados['nivel'] = $dados['nivel'] ?? 1;
            $dados['xp_total'] = $dados['xp_total'] ?? 0;
            $dados['gocoins'] = $dados['gocoins'] ?? 0;

            $gamificacao = Gamificacao::create($dados);
            $gamificacao->load(['usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Gamificação criada com sucesso',
                'data' => $gamificacao
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
                'message' => 'Erro ao criar gamificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $gamificacao = Gamificacao::with(['usuario'])->find($id);
            
            if (!$gamificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gamificação não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $gamificacao
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar gamificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $gamificacao = Gamificacao::find($id);
            
            if (!$gamificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gamificação não encontrada'
                ], 404);
            }

            $request->validate([
                'nivel' => 'sometimes|integer|min:1',
                'xp_total' => 'sometimes|integer|min:0',
                'gocoins' => 'sometimes|integer|min:0',
                'avatar_atual' => 'nullable|string',
                'conquistas_desbloqueadas' => 'nullable|array',
                'missoes_ativas' => 'nullable|array',
                'missoes_concluidas' => 'nullable|array',
            ]);

            $dados = $request->all();
            $gamificacao->update($dados);
            $gamificacao->load(['usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Gamificação atualizada com sucesso',
                'data' => $gamificacao
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
                'message' => 'Erro ao atualizar gamificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $gamificacao = Gamificacao::find($id);
            
            if (!$gamificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gamificação não encontrada'
                ], 404);
            }

            $gamificacao->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gamificação removida com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover gamificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function adicionarXP(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'xp' => 'required|integer|min:1'
            ]);

            $gamificacao = Gamificacao::find($id);
            
            if (!$gamificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gamificação não encontrada'
                ], 404);
            }

            $gamificacao->adicionarXP($request->xp);
            $gamificacao->load(['usuario']);

            return response()->json([
                'success' => true,
                'message' => 'XP adicionado com sucesso',
                'data' => $gamificacao
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
                'message' => 'Erro ao adicionar XP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function adicionarGoCoins(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'gocoins' => 'required|integer|min:1'
            ]);

            $gamificacao = Gamificacao::find($id);
            
            if (!$gamificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gamificação não encontrada'
                ], 404);
            }

            $gamificacao->adicionarGoCoins($request->gocoins);
            $gamificacao->load(['usuario']);

            return response()->json([
                'success' => true,
                'message' => 'GoCoins adicionados com sucesso',
                'data' => $gamificacao
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
                'message' => 'Erro ao adicionar GoCoins',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(): JsonResponse
    {
        try {
            $totalGamificacoes = Gamificacao::count();
            $nivelMedio = Gamificacao::avg('nivel');
            $xpTotal = Gamificacao::sum('xp_total');
            $gocoinsTotal = Gamificacao::sum('gocoins');
            
            $topNiveis = Gamificacao::with(['usuario'])
                ->orderBy('nivel', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalGamificacoes,
                    'nivel_medio' => round($nivelMedio, 2),
                    'xp_total' => $xpTotal,
                    'gocoins_total' => $gocoinsTotal,
                    'top_niveis' => $topNiveis
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
