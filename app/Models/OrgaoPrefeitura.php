<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgaoPrefeitura extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'orgaos_prefeitura';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'orgao_id');
    }
}
