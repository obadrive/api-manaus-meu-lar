<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servico extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'servicos';

    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'tipo',
        'setor',
        'bairro_id',
        'usuario_id',
        'geometria',
        'endereco',
        'telefone',
        'email',
        'website',
        'horario_funcionamento',
        'documentos_necessarios',
        'prazo_estimado',
        'gratuito',
        'preco',
        'ativo',
        'disponivel_online',
        'total_solicitacoes',
        'avaliacao_media',
        'total_avaliacoes',
    ];

    protected $casts = [
        'geometria' => 'array',
        'horario_funcionamento' => 'array',
        'documentos_necessarios' => 'array',
        'gratuito' => 'boolean',
        'preco' => 'decimal:2',
        'ativo' => 'boolean',
        'disponivel_online' => 'boolean',
        'total_solicitacoes' => 'integer',
        'avaliacao_media' => 'decimal:2',
        'total_avaliacoes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function solicitacoes(): HasMany
    {
        return $this->hasMany(SolicitacaoServico::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(Imagem::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeOnline($query)
    {
        return $query->where('disponivel_online', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorSetor($query, $setor)
    {
        return $query->where('setor', $setor);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorBairro($query, $bairroId)
    {
        return $query->where('bairro_id', $bairroId);
    }

    public function scopeGratuitos($query)
    {
        return $query->where('gratuito', true);
    }

    public function scopePagos($query)
    {
        return $query->where('gratuito', false);
    }

    public function scopeOficiais($query)
    {
        return $query->where('tipo', 'oficial');
    }

    public function scopeComunitarios($query)
    {
        return $query->where('tipo', 'comunitario');
    }

    public function scopeMaisSolicitados($query)
    {
        return $query->orderBy('total_solicitacoes', 'desc');
    }

    public function scopeMelhorAvaliados($query)
    {
        return $query->orderBy('avaliacao_media', 'desc');
    }

    public function scopeProximos($query, $lat, $lng, $raio = 5000)
    {
        return $query->whereRaw('ST_DWithin(geometria, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)', [$lng, $lat, $raio]);
    }

    // Métodos
    public function isOficial(): bool
    {
        return $this->tipo === 'oficial';
    }

    public function isComunitario(): bool
    {
        return $this->tipo === 'comunitario';
    }

    public function isGratuito(): bool
    {
        return $this->gratuito;
    }

    public function isOnline(): bool
    {
        return $this->disponivel_online;
    }

    public function calcularAvaliacaoMedia(): void
    {
        $media = $this->avaliacoes()->avg('nota');
        $total = $this->avaliacoes()->count();
        
        $this->update([
            'avaliacao_media' => $media ?? 0,
            'total_avaliacoes' => $total
        ]);
    }

    public function atualizarSolicitacoes(): void
    {
        $this->update([
            'total_solicitacoes' => $this->solicitacoes()->count()
        ]);
    }

    public function getDocumentosNecessariosArray(): array
    {
        return $this->documentos_necessarios ?? [];
    }

    public function getHorarioFuncionamentoArray(): array
    {
        return $this->horario_funcionamento ?? [];
    }
}
