<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RamoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'codigo' => $this->codigo,
        ];
    }
}
