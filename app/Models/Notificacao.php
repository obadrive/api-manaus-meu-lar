<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'notificacoes';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'mensagem',
        'tipo',
        'categoria',
        'dados_adicional',
        'lida',
        'data_leitura',
        'ativo',
    ];

    protected $casts = [
        'dados_adicional' => 'array',
        'lida' => 'boolean',
        'data_leitura' => 'datetime',
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

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeNaoLidas($query)
    {
        return $query->where('lida', false);
    }

    public function scopeLidas($query)
    {
        return $query->where('lida', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Métodos
    public function marcarComoLida(): void
    {
        $this->update([
            'lida' => true,
            'data_leitura' => now()
        ]);
    }

    public function isLida(): bool
    {
        return $this->lida;
    }
}
