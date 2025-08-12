<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BairroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'geometria' => $this->geometria,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relacionamentos (quando carregados)
            'usuarios_count' => $this->when(isset($this->usuarios_count), $this->usuarios_count),
            'pontos_interesse_count' => $this->when(isset($this->pontos_interesse_count), $this->pontos_interesse_count),
            'eventos_count' => $this->when(isset($this->eventos_count), $this->eventos_count),
            'anuncios_count' => $this->when(isset($this->anuncios_count), $this->anuncios_count),
            'postagens_count' => $this->when(isset($this->postagens_count), $this->postagens_count),
            
            // Relacionamentos completos (quando solicitados)
            'usuarios' => UsuarioResource::collection($this->whenLoaded('usuarios')),
            'pontos_interesse' => PontoInteresseResource::collection($this->whenLoaded('pontosInteresse')),
            'eventos' => EventoResource::collection($this->whenLoaded('eventos')),
            'anuncios' => AnuncioResource::collection($this->whenLoaded('anuncios')),
            'postagens' => PostagemResource::collection($this->whenLoaded('postagens')),
        ];
    }
}
