<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comentario extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'comentarios';

    protected $fillable = [
        'usuario_id',
        'conteudo',
        'ativo',
        'aprovado',
        'rejeitado',
        'motivo_rejeicao',
        'commentable_type',
        'commentable_id',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'aprovado' => 'boolean',
        'rejeitado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
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

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAntigos($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    // Métodos
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

    public function isAprovado(): bool
    {
        return $this->aprovado;
    }

    public function isRejeitado(): bool
    {
        return $this->rejeitado;
    }
}
