<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bairro extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bairros';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'geometria',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'geometria' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the usuarios for this bairro.
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }

    /**
     * Get the pontos de interesse for this bairro.
     */
    public function pontosInteresse(): HasMany
    {
        return $this->hasMany(PontoInteresse::class);
    }

    /**
     * Get the eventos for this bairro.
     */
    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    /**
     * Get the anuncios for this bairro.
     */
    public function anuncios(): HasMany
    {
        return $this->hasMany(Anuncio::class);
    }

    /**
     * Get the postagens for this bairro.
     */
    public function postagens(): HasMany
    {
        return $this->hasMany(Postagem::class);
    }

    /**
     * Scope para buscar por nome
     */
    public function scopePorNome($query, $nome)
    {
        return $query->where('nome', 'like', "%{$nome}%");
    }

    /**
     * Scope para buscar por proximidade geográfica
     */
    public function scopeProximos($query, $lat, $lng, $raio = 5000)
    {
        return $query->whereRaw('ST_DWithin(geometria, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)', [$lng, $lat, $raio]);
    }
}
