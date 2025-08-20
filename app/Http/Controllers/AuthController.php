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

            return response()->json([
                'success' => true,
                'message' => 'Usuário registrado com sucesso',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $token,
                    'token_type' => 'Bearer'
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

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $token,
                    'token_type' => 'Bearer'
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

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ], 200);

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
}
