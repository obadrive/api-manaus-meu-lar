<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasApiTokens;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'role',
        'bairro_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'senha',
    ];

    // Relacionamentos
    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class);
    }

    public function pontosInteresse(): HasMany
    {
        return $this->hasMany(PontoInteresse::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    public function anuncios(): HasMany
    {
        return $this->hasMany(Anuncio::class);
    }

    public function postagens(): HasMany
    {
        return $this->hasMany(Postagem::class);
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class);
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notificacao::class);
    }

    public function gamificacao(): HasOne
    {
        return $this->hasOne(Gamificacao::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeVerificados($query)
    {
        return $query->where('verificado', true);
    }

    public function scopePorBairro($query, $bairroId)
    {
        return $query->where('bairro_id', $bairroId);
    }

    public function scopeProximos($query, $lat, $lng, $raio = 5000)
    {
        return $query->whereRaw('ST_DWithin(geometria, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)', [$lng, $lat, $raio]);
    }

    // Métodos
    public function isComerciante(): bool
    {
        return $this->role === 'comerciante';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMorador(): bool
    {
        return $this->role === 'morador';
    }
}
