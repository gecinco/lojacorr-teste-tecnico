<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeguroResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'documento_segurado' => $this->documento_segurado,
            'documento_formatado' => $this->documento_formatado,
            'tipo_documento' => $this->tipo_documento,
            'nome_segurado' => $this->nome_segurado,
            'seguradora' => new SeguradoraResource($this->whenLoaded('seguradora')),
            'ramo' => new RamoResource($this->whenLoaded('ramo')),
            'valor_total' => (float) $this->valor_total,
            'valor_total_formatado' => 'R$ ' . number_format($this->valor_total, 2, ',', '.'),
            'quantidade_parcelas' => $this->quantidade_parcelas,
            'valor_parcela' => (float) $this->valor_parcela,
            'valor_parcela_formatado' => 'R$ ' . number_format($this->valor_parcela, 2, ',', '.'),
            'inicio_vigencia' => $this->inicio_vigencia->format('Y-m-d'),
            'inicio_vigencia_formatado' => $this->inicio_vigencia->format('d/m/Y'),
            'fim_vigencia' => $this->fim_vigencia->format('Y-m-d'),
            'fim_vigencia_formatado' => $this->fim_vigencia->format('d/m/Y'),
            'status_vigencia' => $this->status_vigencia,
            'endereco' => [
                'cep' => $this->cep,
                'cep_formatado' => preg_replace('/(\d{5})(\d{3})/', '$1-$2', $this->cep),
                'logradouro' => $this->logradouro,
                'numero' => $this->numero,
                'complemento' => $this->complemento,
                'bairro' => $this->bairro,
                'cidade' => $this->cidade,
                'uf' => $this->uf,
                'endereco_completo' => $this->getEnderecoCompleto(),
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    private function getEnderecoCompleto()
    {
        $partes = [$this->logradouro];
        
        if ($this->numero) {
            $partes[] = $this->numero;
        }
        
        if ($this->complemento) {
            $partes[] = $this->complemento;
        }
        
        $partes[] = $this->bairro;
        $partes[] = $this->cidade . '/' . $this->uf;
        $partes[] = preg_replace('/(\d{5})(\d{3})/', '$1-$2', $this->cep);
        
        return implode(', ', $partes);
    }
}
