<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Imagem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'imagens';

    protected $fillable = [
        'nome',
        'descricao',
        'url',
        'url_thumbnail',
        'tamanho',
        'tipo_mime',
        'alt_text',
        'legenda',
        'ordem',
        'ativo',
        'imageable_type',
        'imageable_id',
    ];

    protected $casts = [
        'tamanho' => 'integer',
        'ordem' => 'integer',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('imageable_type', $tipo);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem', 'asc');
    }

    // Métodos
    public function getUrlCompletaAttribute(): string
    {
        return asset('storage/' . $this->url);
    }

    public function getThumbnailCompletoAttribute(): string
    {
        return asset('storage/' . $this->url_thumbnail);
    }

    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
