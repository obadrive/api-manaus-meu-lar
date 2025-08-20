<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Servico::with(['bairro', 'usuario']);
            
            // Filtros
            if ($request->has('categoria')) {
                $query->where('categoria', $request->categoria);
            }
            
            if ($request->has('setor')) {
                $query->where('setor', $request->setor);
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
            
            if ($request->has('disponivel_online')) {
                $query->where('disponivel_online', $request->boolean('disponivel_online'));
            }
            
            if ($request->has('gratuito')) {
                $query->where('gratuito', $request->boolean('gratuito'));
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
            
            if ($request->has('comunitario') && $request->boolean('comunitario')) {
                $query->comunitarios();
            }
            
            if ($request->has('online') && $request->boolean('online')) {
                $query->online();
            }
            
            // Ordenação
            $ordenacao = $request->get('ordenacao', 'nome');
            switch ($ordenacao) {
                case 'mais_solicitados':
                    $query->maisSolicitados();
                    break;
                case 'melhor_avaliados':
                    $query->melhorAvaliados();
                    break;
                default:
                    $query->orderBy('nome', 'asc');
            }
            
            $perPage = $request->get('per_page', 15);
            $servicos = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $servicos->items(),
                'pagination' => [
                    'current_page' => $servicos->currentPage(),
                    'last_page' => $servicos->lastPage(),
                    'per_page' => $servicos->perPage(),
                    'total' => $servicos->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar serviços',
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
                'setor' => 'required|string|max:100',
                'tipo' => 'required|in:oficial,comunitario',
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
                'horario_funcionamento' => 'nullable|array',
                'documentos_necessarios' => 'nullable|array',
                'prazo_estimado' => 'nullable|string|max:100',
                'gratuito' => 'boolean',
                'preco' => 'nullable|numeric|min:0|required_if:gratuito,false',
                'ativo' => 'boolean',
                'disponivel_online' => 'boolean',
            ]);

            $dados = $request->all();
            $dados['ativo'] = $dados['ativo'] ?? true;
            $dados['disponivel_online'] = $dados['disponivel_online'] ?? false;
            $dados['gratuito'] = $dados['gratuito'] ?? true;

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $servico = Servico::create($dados);
            $servico->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Serviço criado com sucesso',
                'data' => $servico
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
                'message' => 'Erro ao criar serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $servico = Servico::with(['bairro', 'usuario', 'avaliacoes', 'comentarios', 'imagens'])->find($id);
            
            if (!$servico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serviço não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $servico
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $servico = Servico::find($id);
            
            if (!$servico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serviço não encontrado'
                ], 404);
            }

            $request->validate([
                'nome' => 'sometimes|string|max:255',
                'descricao' => 'nullable|string',
                'categoria' => 'sometimes|string|max:100',
                'setor' => 'sometimes|string|max:100',
                'tipo' => 'sometimes|in:oficial,comunitario',
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
                'documentos_necessarios' => 'nullable|array',
                'prazo_estimado' => 'nullable|string|max:100',
                'gratuito' => 'boolean',
                'preco' => 'nullable|numeric|min:0|required_if:gratuito,false',
                'ativo' => 'boolean',
                'disponivel_online' => 'boolean',
            ]);

            $dados = $request->all();

            if (isset($dados['geometria'])) {
                $geometria = json_encode($dados['geometria']);
                $dados['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
            }

            $servico->update($dados);
            $servico->load(['bairro', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Serviço atualizado com sucesso',
                'data' => $servico
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
                'message' => 'Erro ao atualizar serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $servico = Servico::find($id);
            
            if (!$servico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serviço não encontrado'
                ], 404);
            }

            $servico->delete();

            return response()->json([
                'success' => true,
                'message' => 'Serviço removido com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover serviço',
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
                'setor' => 'nullable|string',
            ]);

            $raio = $request->get('raio', 5000);
            $query = Servico::with(['bairro', 'usuario'])
                ->proximos($request->lat, $request->lng, $raio)
                ->ativos();

            if ($request->has('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            if ($request->has('setor')) {
                $query->where('setor', $request->setor);
            }

            $servicos = $query->orderBy('nome', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $servicos,
                'params' => [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'raio' => $raio,
                    'categoria' => $request->categoria,
                    'setor' => $request->setor
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
                'message' => 'Erro ao buscar serviços próximos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function categorias(): JsonResponse
    {
        try {
            $categorias = Servico::select('categoria')
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

    public function setores(): JsonResponse
    {
        try {
            $setores = Servico::select('setor')
                ->distinct()
                ->whereNotNull('setor')
                ->pluck('setor');

            return response()->json([
                'success' => true,
                'data' => $setores
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar setores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(): JsonResponse
    {
        try {
            $totalServicos = Servico::count();
            $servicosAtivos = Servico::where('ativo', true)->count();
            $servicosOnline = Servico::where('disponivel_online', true)->count();
            $servicosGratuitos = Servico::where('gratuito', true)->count();
            $servicosOficiais = Servico::where('tipo', 'oficial')->count();
            $servicosComunitarios = Servico::where('tipo', 'comunitario')->count();
            
            $porCategoria = Servico::selectRaw('categoria, count(*) as total')
                ->groupBy('categoria')
                ->get();

            $porSetor = Servico::selectRaw('setor, count(*) as total')
                ->groupBy('setor')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalServicos,
                    'ativos' => $servicosAtivos,
                    'online' => $servicosOnline,
                    'gratuitos' => $servicosGratuitos,
                    'oficiais' => $servicosOficiais,
                    'comunitarios' => $servicosComunitarios,
                    'por_categoria' => $porCategoria,
                    'por_setor' => $porSetor
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
