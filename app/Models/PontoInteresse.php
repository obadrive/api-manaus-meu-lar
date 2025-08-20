<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PontoInteresse extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pontos_interesse';

    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'tipo',
        'bairro_id',
        'usuario_id',
        'geometria',
        'endereco',
        'telefone',
        'email',
        'website',
        'horario_funcionamento',
        'ativo',
        'verificado',
        'avaliacao_media',
        'total_avaliacoes',
    ];

    protected $casts = [
        'geometria' => 'array',
        'horario_funcionamento' => 'array',
        'ativo' => 'boolean',
        'verificado' => 'boolean',
        'avaliacao_media' => 'decimal:2',
        'total_avaliacoes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(Imagem::class);
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

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorBairro($query, $bairroId)
    {
        return $query->where('bairro_id', $bairroId);
    }

    public function scopeProximos($query, $lat, $lng, $raio = 5000)
    {
        return $query->whereRaw('ST_DWithin(geometria, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)', [$lng, $lat, $raio]);
    }

    public function scopeOficiais($query)
    {
        return $query->where('tipo', 'oficial');
    }

    public function scopeComerciais($query)
    {
        return $query->where('tipo', 'comercial');
    }

    // Métodos
    public function isOficial(): bool
    {
        return $this->tipo === 'oficial';
    }

    public function isComercial(): bool
    {
        return $this->tipo === 'comercial';
    }

    public function calcularAvaliacaoMedia(): void
    {
        $media = $this->avaliacoes()->avg('nota');
        $total = $this->avaliacoes()->count();
        
        $this->update([
            'avaliacao_media' => $media ?? 0,
            'total_avaliacoes' => $total
        ]);
    }
}
