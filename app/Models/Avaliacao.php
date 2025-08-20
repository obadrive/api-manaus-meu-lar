<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Avaliacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'usuario_id',
        'nota',
        'comentario',
        'categoria',
        'ativo',
        'avaliable_type',
        'avaliable_id',
    ];

    protected $casts = [
        'nota' => 'integer',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function avaliable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorNota($query, $nota)
    {
        return $query->where('nota', $nota);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeMelhores($query)
    {
        return $query->orderBy('nota', 'desc');
    }

    // Métodos
    public function getNotaFormatadaAttribute(): string
    {
        return number_format($this->nota, 1);
    }

    public function isPositiva(): bool
    {
        return $this->nota >= 4;
    }

    public function isNegativa(): bool
    {
        return $this->nota <= 2;
    }

    public function isNeutra(): bool
    {
        return $this->nota == 3;
    }
}
