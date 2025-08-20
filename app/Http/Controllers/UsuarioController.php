<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsuarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Usuario::query();
            
            // Filtros
            if ($request->has('role')) {
                $query->where('role', $request->role);
            }
            
            if ($request->has('bairro_id')) {
                $query->where('bairro_id', $request->bairro_id);
            }
            
            if ($request->has('nome')) {
                $query->where('nome', 'like', '%' . $request->nome . '%');
            }
            
            $perPage = $request->get('per_page', 15);
            $usuarios = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $usuarios->items(),
                'pagination' => [
                    'current_page' => $usuarios->currentPage(),
                    'last_page' => $usuarios->lastPage(),
                    'per_page' => $usuarios->perPage(),
                    'total' => $usuarios->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios,email',
                'senha' => 'required|string|min:6',
                'telefone' => 'nullable|string|max:20',
                'cpf_cnpj' => 'nullable|string|max:20',
                                            'role' => 'required|in:morador,comerciante,admin',
                'bairro_id' => 'nullable|exists:bairros,id',
                'geometria' => 'nullable|array',
                'ativo' => 'boolean',
                'verificado' => 'boolean',
                'preferencias' => 'nullable|array',
            ]);

            $dados = $request->all();
            $dados['senha'] = Hash::make($dados['senha']);
            

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $usuario = Usuario::create($dados);
            $usuario->load(['bairro', 'gamificacao']);

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'data' => $usuario
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
                'message' => 'Erro ao criar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $usuario = Usuario::with(['bairro', 'gamificacao'])->find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $usuario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            $request->validate([
                'nome' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:usuarios,email,' . $id,
                'telefone' => 'nullable|string|max:20',
                'cpf_cnpj' => 'nullable|string|max:20',
                                            'role' => 'sometimes|in:morador,comerciante,admin',
                'bairro_id' => 'nullable|exists:bairros,id',
                'geometria' => 'nullable|array',
                'ativo' => 'boolean',
                'verificado' => 'boolean',
                'preferencias' => 'nullable|array',
            ]);

            $dados = $request->except(['senha']);
            
            if ($request->has('senha')) {
                $request->validate(['senha' => 'string|min:6']);
                $dados['senha'] = Hash::make($request->senha);
            }

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $usuario->update($dados);
            $usuario->load(['bairro', 'gamificacao']);

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $usuario
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
                'message' => 'Erro ao atualizar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário removido com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $usuario = Usuario::withTrashed()->find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            if (!$usuario->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não está removido'
                ], 400);
            }

            $usuario->restore();
            $usuario->load(['bairro', 'gamificacao']);

            return response()->json([
                'success' => true,
                'message' => 'Usuário restaurado com sucesso',
                'data' => $usuario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(): JsonResponse
    {
        try {
            $totalUsuarios = Usuario::count();
            $usuariosRemovidos = Usuario::onlyTrashed()->count();
            
            $porTipo = Usuario::selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalUsuarios,
                    'removidos' => $usuariosRemovidos,
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

    public function proximos(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'raio' => 'sometimes|numeric|min:100|max:50000',
            ]);

            $raio = $request->get('raio', 5000);
            $usuarios = Usuario::with(['bairro'])
                ->proximos($request->lat, $request->lng, $raio)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $usuarios,
                'params' => [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
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
                'message' => 'Erro ao buscar usuários próximos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
