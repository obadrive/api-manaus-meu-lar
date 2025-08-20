<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anuncio extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'anuncios';

    protected $fillable = [
        'titulo',
        'descricao',
        'categoria',
        'tipo',
        'bairro_id',
        'usuario_id',
        'geometria',
        'endereco',
        'telefone',
        'email',
        'website',
        'preco',
        'preco_negociavel',
        'condicao',
        'ativo',
        'aprovado',
        'rejeitado',
        'motivo_rejeicao',
        'data_expiracao',
        'total_visualizacoes',
        'total_favoritos',
        'avaliacao_media',
        'total_avaliacoes',
    ];

    protected $casts = [
        'geometria' => 'array',
        'preco' => 'decimal:2',
        'preco_negociavel' => 'boolean',
        'ativo' => 'boolean',
        'aprovado' => 'boolean',
        'rejeitado' => 'boolean',
        'data_expiracao' => 'datetime',
        'total_visualizacoes' => 'integer',
        'total_favoritos' => 'integer',
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

    public function imagens(): HasMany
    {
        return $this->hasMany(Imagem::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    public function visualizacoes(): HasMany
    {
        return $this->hasMany(Visualizacao::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeAprovados($query)
    {
        return $query->where('aprovado', true);
    }

    public function scopeNaoRejeitados($query)
    {
        return $query->where('rejeitado', false);
    }

    public function scopeNaoExpirados($query)
    {
        return $query->where(function($q) {
            $q->whereNull('data_expiracao')
              ->orWhere('data_expiracao', '>', now());
        });
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorBairro($query, $bairroId)
    {
        return $query->where('bairro_id', $bairroId);
    }

    public function scopePorPreco($query, $minPreco, $maxPreco)
    {
        return $query->whereBetween('preco', [$minPreco, $maxPreco]);
    }

    public function scopePorCondicao($query, $condicao)
    {
        return $query->where('condicao', $condicao);
    }

    public function scopeProximos($query, $lat, $lng, $raio = 5000)
    {
        return $query->whereRaw('ST_DWithin(geometria, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)', [$lng, $lat, $raio]);
    }

    public function scopeOficiais($query)
    {
        return $query->where('tipo', 'oficial');
    }

    public function scopeComerciais($query)
    {
        return $query->where('tipo', 'comercial');
    }

    public function scopeMaisVistos($query)
    {
        return $query->orderBy('total_visualizacoes', 'desc');
    }

    public function scopeMaisFavoritados($query)
    {
        return $query->orderBy('total_favoritos', 'desc');
    }

    public function scopeMelhorAvaliados($query)
    {
        return $query->orderBy('avaliacao_media', 'desc');
    }

    // Métodos
    public function isOficial(): bool
    {
        return $this->tipo === 'oficial';
    }

    public function isComercial(): bool
    {
        return $this->tipo === 'comercial';
    }

    public function isAprovado(): bool
    {
        return $this->aprovado;
    }

    public function isRejeitado(): bool
    {
        return $this->rejeitado;
    }

    public function isExpirado(): bool
    {
        if (!$this->data_expiracao) {
            return false;
        }
        return $this->data_expiracao < now();
    }

    public function isFavoritado($usuarioId): bool
    {
        return $this->favoritos()->where('usuario_id', $usuarioId)->exists();
    }

    public function incrementarVisualizacao(): void
    {
        $this->increment('total_visualizacoes');
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

    public function atualizarFavoritos(): void
    {
        $this->update([
            'total_favoritos' => $this->favoritos()->count()
        ]);
    }

    public function renovar(): void
    {
        $this->update([
            'data_expiracao' => now()->addDays(30),
            'ativo' => true
        ]);
    }
}
