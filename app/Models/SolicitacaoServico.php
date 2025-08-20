<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoServico extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'solicitacoes_servicos';

    protected $fillable = [
        'servico_id',
        'usuario_id',
        'protocolo',
        'status',
        'dados_solicitacao',
        'observacoes',
        'data_solicitacao',
        'data_conclusao',
        'ativo',
    ];

    protected $casts = [
        'dados_solicitacao' => 'array',
        'data_solicitacao' => 'datetime',
        'data_conclusao' => 'datetime',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('data_solicitacao', 'desc');
    }
}
