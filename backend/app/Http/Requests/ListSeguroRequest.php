<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListSeguroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'documento' => 'nullable|string',
            'status' => 'nullable|string|in:vigente,a_vencer,vencido',
            'inicio_vigencia_de' => 'nullable|date|date_format:Y-m-d',
            'inicio_vigencia_ate' => 'nullable|date|date_format:Y-m-d',
            'fim_vigencia_de' => 'nullable|date|date_format:Y-m-d',
            'fim_vigencia_ate' => 'nullable|date|date_format:Y-m-d',
            'seguradora_id' => 'nullable|exists:seguradoras,id',
            'ramo_id' => 'nullable|exists:ramos,id',
            'sort_by' => 'nullable|string|in:id,documento_segurado,nome_segurado,valor_total,inicio_vigencia,fim_vigencia,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|in:10,25,50',
            'page' => 'nullable|integer|min:1',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->documento) {
            $this->merge([
                'documento' => preg_replace('/\D/', '', $this->documento),
            ]);
        }
    }
}
