<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Postagem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'postagens';

    protected $fillable = [
        'conteudo',
        'tipo',
        'categoria',
        'bairro_id',
        'usuario_id',
        'geometria',
        'fixado',
        'ativo',
        'aprovado',
        'rejeitado',
        'motivo_rejeicao',
        'total_curtidas',
        'total_comentarios',
        'total_compartilhamentos',
        'total_denuncias',
    ];

    protected $casts = [
        'geometria' => 'array',
        'fixado' => 'boolean',
        'ativo' => 'boolean',
        'aprovado' => 'boolean',
        'rejeitado' => 'boolean',
        'total_curtidas' => 'integer',
        'total_comentarios' => 'integer',
        'total_compartilhamentos' => 'integer',
        'total_denuncias' => 'integer',
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

    public function curtidas(): HasMany
    {
        return $this->hasMany(Curtida::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    public function compartilhamentos(): HasMany
    {
        return $this->hasMany(Compartilhamento::class);
    }

    public function denuncias(): HasMany
    {
        return $this->hasMany(Denuncia::class);
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeAprovadas($query)
    {
        return $query->where('aprovado', true);
    }

    public function scopeNaoRejeitadas($query)
    {
        return $query->where('rejeitado', false);
    }

    public function scopeFixadas($query)
    {
        return $query->where('fixado', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorBairro($query, $bairroId)
    {
        return $query->where('bairro_id', $bairroId);
    }

    public function scopeOficiais($query)
    {
        return $query->where('tipo', 'oficial');
    }

    public function scopeComunitarias($query)
    {
        return $query->where('tipo', 'comunitario');
    }

    public function scopeMaisCurtidas($query)
    {
        return $query->orderBy('total_curtidas', 'desc');
    }

    public function scopeMaisComentadas($query)
    {
        return $query->orderBy('total_comentarios', 'desc');
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeProximas($query, $lat, $lng, $raio = 5000)
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

    public function isFixado(): bool
    {
        return $this->fixado;
    }

    public function isAprovado(): bool
    {
        return $this->aprovado;
    }

    public function isRejeitado(): bool
    {
        return $this->rejeitado;
    }

    public function isCurtido($usuarioId): bool
    {
        return $this->curtidas()->where('usuario_id', $usuarioId)->exists();
    }

    public function isCompartilhado($usuarioId): bool
    {
        return $this->compartilhamentos()->where('usuario_id', $usuarioId)->exists();
    }

    public function isDenunciado($usuarioId): bool
    {
        return $this->denuncias()->where('usuario_id', $usuarioId)->exists();
    }

    public function atualizarContadores(): void
    {
        $this->update([
            'total_curtidas' => $this->curtidas()->count(),
            'total_comentarios' => $this->comentarios()->count(),
            'total_compartilhamentos' => $this->compartilhamentos()->count(),
            'total_denuncias' => $this->denuncias()->count()
        ]);
    }

    public function fixar(): void
    {
        $this->update(['fixado' => true]);
    }

    public function desfixar(): void
    {
        $this->update(['fixado' => false]);
    }

    public function aprovar(): void
    {
        $this->update([
            'aprovado' => true,
            'rejeitado' => false,
            'motivo_rejeicao' => null
        ]);
    }

    public function rejeitar(string $motivo): void
    {
        $this->update([
            'aprovado' => false,
            'rejeitado' => true,
            'motivo_rejeicao' => $motivo
        ]);
    }
}
