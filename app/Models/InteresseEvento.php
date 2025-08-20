<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InteresseEvento extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'interesses_eventos';

    protected $fillable = [
        'evento_id',
        'usuario_id',
        'data_interesse',
        'ativo',
    ];

    protected $casts = [
        'data_interesse' => 'datetime',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
