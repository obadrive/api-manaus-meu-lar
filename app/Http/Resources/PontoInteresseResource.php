<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PontoInteresseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome ?? null,
            'tipo' => $this->tipo ?? null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
