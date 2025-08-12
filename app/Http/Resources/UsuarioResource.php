<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome ?? null,
            'email' => $this->email ?? null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
