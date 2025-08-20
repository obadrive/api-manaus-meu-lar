<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Compartilhamento extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'compartilhamentos';

    protected $fillable = [
        'usuario_id',
        'shareable_type',
        'shareable_id',
        'plataforma',
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

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorPlataforma($query, $plataforma)
    {
        return $query->where('plataforma', $plataforma);
    }
}
