<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnuncioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo ?? null,
            'descricao' => $this->descricao ?? null,
            'tipo' => $this->tipo ?? null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
