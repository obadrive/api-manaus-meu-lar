<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo ?? null,
            'descricao' => $this->descricao ?? null,
            'data_inicio' => $this->data_inicio ?? null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
