<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar um novo usuário
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios,email',
                'senha' => 'required|string|min:6',
                'role' => 'required|in:morador,comerciante,turista,admin',
            ]);

            $usuario = Usuario::create([
                'nome' => $request->nome,
                'email' => $request->email,
                'senha' => Hash::make($request->senha),
                'role' => $request->role,
            ]);

            $token = $usuario->createToken('auth_token')->plainTextToken;
            $refreshToken = $usuario->createToken('refresh_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuário registrado com sucesso',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'refresh_token' => $refreshToken,
                    'expires_in' => 3600 // 1 hora
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fazer login do usuário
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'senha' => 'required|string',
            ]);

            $usuario = Usuario::where('email', $request->email)->first();

            if (!$usuario || !Hash::check($request->senha, $usuario->senha)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            $token = $usuario->createToken('auth_token')->plainTextToken;
            $refreshToken = $usuario->createToken('refresh_token')->plainTextToken;

            $response = response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'refresh_token' => $refreshToken,
                    'expires_in' => 3600 // 1 hora
                ]
            ], 200);

            // Adicionar cookies
            $response->cookie('user_id', $usuario->id, 60 * 24 * 30); // 30 dias
            $response->cookie('auth_token', $token, 60 * 24 * 30); // 30 dias

            return $response;

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fazer logout do usuário
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            $response = response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ], 200);

            // Remover cookies
            $response->cookie('user_id', '', -1);
            $response->cookie('auth_token', '', -1);

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fazer logout de todos os dispositivos
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout de todos os dispositivos realizado com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer logout de todos os dispositivos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renovar token de acesso
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string',
            ]);

            // Busca o token no banco de dados
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->refresh_token);
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh token inválido'
                ], 401);
            }

            $usuario = $token->tokenable;
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 401);
            }

            // Revoga o token antigo
            $token->delete();

            // Cria um novo token
            $newToken = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token renovado com sucesso',
                'data' => [
                    'token' => $newToken,
                    'expires_in' => 3600 // 1 hora
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
                'message' => 'Erro ao renovar token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $usuario = $request->user();

            return response()->json([
                'success' => true,
                'data' => $usuario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar dados do usuário autenticado
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $usuario = $request->user();

            $request->validate([
                'nome' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:usuarios,email,' . $usuario->id,
                'bairro_id' => 'nullable|exists:bairros,id',
            ]);

            $dados = $request->only(['nome', 'email', 'bairro_id']);
            $usuario->update($dados);
            $usuario->load(['bairro', 'gamificacao']);

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => $usuario
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
                'message' => 'Erro ao atualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alterar senha do usuário autenticado
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $usuario = $request->user();

            $request->validate([
                'senha_atual' => 'required|string',
                'nova_senha' => 'required|string|min:6|confirmed',
            ]);

            if (!Hash::check($request->senha_atual, $usuario->senha)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha atual incorreta'
                ], 400);
            }

            $usuario->update([
                'senha' => Hash::make($request->nova_senha)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso'
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
                'message' => 'Erro ao alterar senha',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar tokens do usuário
     */
    public function tokens(Request $request): JsonResponse
    {
        try {
            $tokens = $request->user()->tokens()->get();

            return response()->json([
                'success' => true,
                'data' => $tokens
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tokens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revogar um token específico
     */
    public function revokeToken(Request $request, $tokenId): JsonResponse
    {
        try {
            $token = $request->user()->tokens()->find($tokenId);

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token não encontrado'
                ], 404);
            }

            $token->delete();

            return response()->json([
                'success' => true,
                'message' => 'Token revogado com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao revogar token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter userId do cookie
     */
    public function getUserId(Request $request): JsonResponse
    {
        try {
            $userId = $request->cookie('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $usuario = Usuario::find($userId);

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'usuario' => $usuario
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter userId',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
