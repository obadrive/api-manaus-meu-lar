<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evento extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'bairro_id',
        'organizador_id',
        'orgao_id',
        'status',
        'limite_inscritos',
        'tipo',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class);
    }

    public function organizador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'organizador_id');
    }

    public function orgao(): BelongsTo
    {
        return $this->belongsTo(OrgaoPrefeitura::class, 'orgao_id');
    }

    // Scopes
    public function scopeAprovados($query)
    {
        return $query->where('status', 'aprovado');
    }

    public function scopeFuturos($query)
    {
        return $query->where('data_inicio', '>=', now());
    }

    public function scopeOficiais($query)
    {
        return $query->where('tipo', 'oficial');
    }

    public function scopeComunitarios($query)
    {
        return $query->where('tipo', 'comunitario');
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

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    public function isFuturo(): bool
    {
        return $this->data_inicio >= now();
    }
}
