<?php

namespace App\Services;

use App\Models\Bairro;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BairroService
{
    /**
     * Listar bairros com filtros e paginação
     */
    public function listarBairros(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Bairro::query();

        // Aplicar filtros
        $this->aplicarFiltros($query, $filtros);

        return $query->paginate($perPage);
    }

    /**
     * Buscar bairro por ID
     */
    public function buscarPorId(string $id): Bairro
    {
        $bairro = Bairro::find($id);
        
        if (!$bairro) {
            throw new ModelNotFoundException('Bairro não encontrado');
        }

        return $bairro;
    }

    /**
     * Criar novo bairro
     */
    public function criarBairro(array $dados): Bairro
    {
        // Validar geometria
        $this->validarGeometria($dados['geometria']);

        // Converter geometria para formato PostGIS
        $geometria = json_encode($dados['geometria']);
        
        return Bairro::create([
            'nome' => $dados['nome'],
            'geometria' => DB::raw("ST_GeomFromGeoJSON('{$geometria}')")
        ]);
    }

    /**
     * Atualizar bairro existente
     */
    public function atualizarBairro(string $id, array $dados): Bairro
    {
        $bairro = $this->buscarPorId($id);
        
        $updateData = [];

        // Atualizar nome se fornecido
        if (isset($dados['nome'])) {
            $updateData['nome'] = $dados['nome'];
        }

        // Atualizar geometria se fornecida
        if (isset($dados['geometria'])) {
            $this->validarGeometria($dados['geometria']);
            $geometria = json_encode($dados['geometria']);
            $updateData['geometria'] = DB::raw("ST_GeomFromGeoJSON('{$geometria}')");
        }

        if (!empty($updateData)) {
            $bairro->update($updateData);
            $bairro->refresh();
        }

        return $bairro;
    }

    /**
     * Remover bairro (soft delete)
     */
    public function removerBairro(string $id): bool
    {
        $bairro = $this->buscarPorId($id);
        return $bairro->delete();
    }

    /**
     * Restaurar bairro removido (soft delete)
     */
    public function restaurarBairro(string $id): Bairro
    {
        $bairro = Bairro::withTrashed()->find($id);
        
        if (!$bairro) {
            throw new ModelNotFoundException('Bairro não encontrado');
        }

        if (!$bairro->trashed()) {
            throw new \InvalidArgumentException('Bairro não está removido');
        }

        $bairro->restore();
        return $bairro;
    }

    /**
     * Buscar bairros por proximidade geográfica
     */
    public function buscarPorProximidade(float $lat, float $lng, int $raio = 5000): Collection
    {
        return Bairro::proximos($lat, $lng, $raio)->get();
    }

    /**
     * Aplicar filtros na query
     */
    private function aplicarFiltros($query, array $filtros): void
    {
        // Filtro por nome
        if (isset($filtros['nome'])) {
            $query->porNome($filtros['nome']);
        }

        // Filtro por proximidade geográfica
        if (isset($filtros['lat']) && isset($filtros['lng'])) {
            $raio = $filtros['raio'] ?? 5000; // Raio padrão 5km
            $query->proximos($filtros['lat'], $filtros['lng'], $raio);
        }
    }

    /**
     * Validar estrutura da geometria
     */
    private function validarGeometria(array $geometria): void
    {
        if (!isset($geometria['type']) || $geometria['type'] !== 'Polygon') {
            throw new \InvalidArgumentException('Tipo de geometria deve ser Polygon');
        }

        if (!isset($geometria['coordinates']) || !is_array($geometria['coordinates'])) {
            throw new \InvalidArgumentException('Coordenadas da geometria são obrigatórias');
        }

        // Validar que as coordenadas formam um polígono válido
        if (empty($geometria['coordinates']) || count($geometria['coordinates']) < 3) {
            throw new \InvalidArgumentException('Polígono deve ter pelo menos 3 pontos');
        }
    }

    /**
     * Buscar estatísticas dos bairros
     */
    public function obterEstatisticas(): array
    {
        $totalBairros = Bairro::count();
        $bairrosAtivos = Bairro::whereNull('deleted_at')->count();
        $bairrosRemovidos = Bairro::onlyTrashed()->count();

        return [
            'total' => $totalBairros,
            'ativos' => $bairrosAtivos,
            'removidos' => $bairrosRemovidos,
        ];
    }

    /**
     * Buscar bairros com contagem de usuários
     */
    public function listarComContagemUsuarios(int $perPage = 15): LengthAwarePaginator
    {
        return Bairro::withCount('usuarios')
            ->orderBy('usuarios_count', 'desc')
            ->paginate($perPage);
    }

    /**
     * Buscar bairros por região (usando bounding box)
     */
    public function buscarPorRegiao(float $minLat, float $maxLat, float $minLng, float $maxLng): Collection
    {
        return Bairro::whereRaw(
            'ST_Intersects(geometria, ST_MakeEnvelope(?, ?, ?, ?, 4326))',
            [$minLng, $minLat, $maxLng, $maxLat]
        )->get();
    }
}
