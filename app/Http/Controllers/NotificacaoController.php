<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Notificacao::with(['usuario']);
            
            // Filtros
            if ($request->has('usuario_id')) {
                $query->where('usuario_id', $request->usuario_id);
            }
            
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            
            if ($request->has('categoria')) {
                $query->where('categoria', $request->categoria);
            }
            
            if ($request->has('lida')) {
                $query->where('lida', $request->boolean('lida'));
            }
            
            if ($request->has('ativo')) {
                $query->where('ativo', $request->boolean('ativo'));
            }
            
            $perPage = $request->get('per_page', 15);
            $notificacoes = $query->recentes()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $notificacoes->items(),
                'pagination' => [
                    'current_page' => $notificacoes->currentPage(),
                    'last_page' => $notificacoes->lastPage(),
                    'per_page' => $notificacoes->perPage(),
                    'total' => $notificacoes->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar notificações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'usuario_id' => 'required|exists:usuarios,id',
                'titulo' => 'required|string|max:255',
                'mensagem' => 'required|string',
                'tipo' => 'required|string|max:100',
                'categoria' => 'nullable|string|max:100',
                'dados_adicional' => 'nullable|array',
                'ativo' => 'boolean',
            ]);

            $dados = $request->all();
            $dados['ativo'] = $dados['ativo'] ?? true;
            $dados['lida'] = false;

            $notificacao = Notificacao::create($dados);
            $notificacao->load(['usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Notificação criada com sucesso',
                'data' => $notificacao
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
                'message' => 'Erro ao criar notificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $notificacao = Notificacao::with(['usuario'])->find($id);
            
            if (!$notificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $notificacao
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar notificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $notificacao = Notificacao::find($id);
            
            if (!$notificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada'
                ], 404);
            }

            $request->validate([
                'titulo' => 'sometimes|string|max:255',
                'mensagem' => 'sometimes|string',
                'tipo' => 'sometimes|string|max:100',
                'categoria' => 'nullable|string|max:100',
                'dados_adicional' => 'nullable|array',
                'lida' => 'boolean',
                'ativo' => 'boolean',
            ]);

            $dados = $request->all();
            $notificacao->update($dados);
            $notificacao->load(['usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Notificação atualizada com sucesso',
                'data' => $notificacao
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
                'message' => 'Erro ao atualizar notificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $notificacao = Notificacao::find($id);
            
            if (!$notificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada'
                ], 404);
            }

            $notificacao->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificação removida com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover notificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function marcarComoLida(string $id): JsonResponse
    {
        try {
            $notificacao = Notificacao::find($id);
            
            if (!$notificacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada'
                ], 404);
            }

            $notificacao->marcarComoLida();
            $notificacao->load(['usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como lida',
                'data' => $notificacao
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificação como lida',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function marcarTodasComoLidas(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'usuario_id' => 'required|exists:usuarios,id'
            ]);

            $count = Notificacao::where('usuario_id', $request->usuario_id)
                ->where('lida', false)
                ->update([
                    'lida' => true,
                    'data_leitura' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificações marcadas como lidas',
                'data' => [
                    'notificacoes_marcadas' => $count
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
                'message' => 'Erro ao marcar notificações como lidas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function naoLidas(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'usuario_id' => 'required|exists:usuarios,id'
            ]);

            $notificacoes = Notificacao::with(['usuario'])
                ->where('usuario_id', $request->usuario_id)
                ->naoLidas()
                ->recentes()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $notificacoes,
                'total' => $notificacoes->count()
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
                'message' => 'Erro ao buscar notificações não lidas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(): JsonResponse
    {
        try {
            $totalNotificacoes = Notificacao::count();
            $notificacoesLidas = Notificacao::where('lida', true)->count();
            $notificacoesNaoLidas = Notificacao::where('lida', false)->count();
            $notificacoesAtivas = Notificacao::where('ativo', true)->count();
            
            $porTipo = Notificacao::selectRaw('tipo, count(*) as total')
                ->groupBy('tipo')
                ->get();

            $porCategoria = Notificacao::selectRaw('categoria, count(*) as total')
                ->whereNotNull('categoria')
                ->groupBy('categoria')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalNotificacoes,
                    'lidas' => $notificacoesLidas,
                    'nao_lidas' => $notificacoesNaoLidas,
                    'ativas' => $notificacoesAtivas,
                    'por_tipo' => $porTipo,
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
