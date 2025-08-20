<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conquista extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'conquistas';

    protected $fillable = [
        'gamificacao_id',
        'titulo',
        'descricao',
        'icone',
        'categoria',
        'tipo',
        'objetivo',
        'recompensa_xp',
        'recompensa_gocoins',
        'nivel_necessario',
        'desbloqueada',
        'data_desbloqueio',
        'ativo',
    ];

    protected $casts = [
        'objetivo' => 'array',
        'recompensa_xp' => 'integer',
        'recompensa_gocoins' => 'integer',
        'nivel_necessario' => 'integer',
        'desbloqueada' => 'boolean',
        'data_desbloqueio' => 'datetime',
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

    public function scopeDesbloqueadas($query)
    {
        return $query->where('desbloqueada', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel_necessario', '<=', $nivel);
    }
}
