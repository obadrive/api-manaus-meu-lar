<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Visualizacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'visualizacoes';

    protected $fillable = [
        'usuario_id',
        'viewable_type',
        'viewable_id',
        'ip_address',
        'user_agent',
        'ativo',
    ];

    protected $casts = [
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

    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }
}
