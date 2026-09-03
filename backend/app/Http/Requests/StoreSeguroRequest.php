<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\DocumentoValido;
use App\Rules\CoerenciaFinanceira;
use App\Rules\VigenciaValida;

class StoreSeguroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'documento_segurado' => ['required', 'string', new DocumentoValido()],
            // Preenchido no prepareForValidation; precisa estar nas regras para
            // entrar no validated() (a coluna no banco é NOT NULL).
            'tipo_documento' => ['required', Rule::in(['cpf', 'cnpj'])],
            'nome_segurado' => 'required|string|min:3|max:255',
            'seguradora_id' => 'required|exists:seguradoras,id',
            'ramo_id' => 'required|exists:ramos,id',
            'valor_total' => 'required|numeric|min:0.01',
            'quantidade_parcelas' => 'required|integer|min:1|max:12',
            'valor_parcela' => [
                'required',
                'numeric',
                'min:0.01',
                new CoerenciaFinanceira($this->valor_total, $this->quantidade_parcelas)
            ],
            'inicio_vigencia' => 'required|date|date_format:Y-m-d',
            'fim_vigencia' => [
                'required',
                'date',
                'date_format:Y-m-d',
                new VigenciaValida($this->inicio_vigencia)
            ],
            'cep' => 'required|string|size:8',
            'logradouro' => 'required|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ];
    }

    public function messages()
    {
        return [
            'documento_segurado.required' => 'O CPF/CNPJ é obrigatório',
            'nome_segurado.required' => 'O nome do segurado é obrigatório',
            'nome_segurado.min' => 'O nome deve ter no mínimo 3 caracteres',
            'seguradora_id.required' => 'Selecione uma seguradora',
            'seguradora_id.exists' => 'Seguradora não encontrada',
            'ramo_id.required' => 'Selecione um ramo',
            'ramo_id.exists' => 'Ramo não encontrado',
            'valor_total.required' => 'O valor total é obrigatório',
            'valor_total.numeric' => 'O valor total deve ser numérico',
            'valor_total.min' => 'O valor total deve ser maior que zero',
            'quantidade_parcelas.required' => 'A quantidade de parcelas é obrigatória',
            'quantidade_parcelas.min' => 'Mínimo de 1 parcela',
            'quantidade_parcelas.max' => 'Máximo de 12 parcelas',
            'valor_parcela.required' => 'O valor da parcela é obrigatório',
            'valor_parcela.numeric' => 'O valor da parcela deve ser numérico',
            'inicio_vigencia.required' => 'A data de início da vigência é obrigatória',
            'inicio_vigencia.date' => 'Data de início inválida',
            'fim_vigencia.required' => 'A data de fim da vigência é obrigatória',
            'fim_vigencia.date' => 'Data de fim inválida',
            'cep.required' => 'O CEP é obrigatório',
            'cep.size' => 'O CEP deve ter 8 dígitos',
            'logradouro.required' => 'O logradouro é obrigatório',
            'bairro.required' => 'O bairro é obrigatório',
            'cidade.required' => 'A cidade é obrigatória',
            'uf.required' => 'O estado é obrigatório',
            'uf.size' => 'O estado deve ter 2 caracteres',
        ];
    }

    protected function prepareForValidation()
    {
        $documento = preg_replace('/[\s.\-\/]/', '', $this->documento_segurado ?? '');
        $cep = preg_replace('/\D/', '', $this->cep ?? '');

        $this->merge([
            'documento_segurado' => $documento,
            'cep' => $cep,
            'tipo_documento' => ctype_digit($documento) && strlen($documento) <= 11 ? 'cpf' : 'cnpj',
        ]);
    }
}
