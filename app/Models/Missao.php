<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Missao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'missoes';

    protected $fillable = [
        'gamificacao_id',
        'titulo',
        'descricao',
        'tipo',
        'categoria',
        'objetivo',
        'recompensa_xp',
        'recompensa_gocoins',
        'data_inicio',
        'data_fim',
        'status',
        'ativo',
    ];

    protected $casts = [
        'objetivo' => 'array',
        'recompensa_xp' => 'integer',
        'recompensa_gocoins' => 'integer',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function gamificacao(): BelongsTo
    {
        return $this->belongsTo(Gamificacao::class);
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeVigentes($query)
    {
        return $query->where(function($q) {
            $q->whereNull('data_fim')
              ->orWhere('data_fim', '>', now());
        });
    }
}
