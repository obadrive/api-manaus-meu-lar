<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransacaoGamificacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'transacoes_gamificacao';

    protected $fillable = [
        'gamificacao_id',
        'tipo',
        'quantidade',
        'motivo',
        'dados',
        'ativo',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'dados' => 'array',
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

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
