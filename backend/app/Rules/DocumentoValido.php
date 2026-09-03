<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DocumentoValido implements Rule
{
    private $message = 'Documento inválido';

    public function passes($attribute, $value)
    {
        // CPF: somente dígitos. CNPJ: aceita o formato alfanumérico da Receita (2026),
        // letras apenas na base de 12 caracteres — os 2 dígitos verificadores seguem numéricos.
        $documento = preg_replace('/[\s.\-\/]/', '', $value);

        if (preg_match('/^\d{11}$/', $documento)) {
            return $this->validaCpf($documento);
        }

        if (preg_match('/^[0-9A-Za-z]{12}\d{2}$/', $documento)) {
            return $this->validaCnpj($documento);
        }

        return false;
    }

    private function validaCpf($cpf)
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            $this->message = 'CPF inválido: sequência repetida';
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                $this->message = 'CPF inválido: dígito verificador incorreto';
                return false;
            }
        }

        return true;
    }

    private function validaCnpj($cnpj)
    {
        if (preg_match('/^(.)\1{13}$/', $cnpj)) {
            $this->message = 'CNPJ inválido: sequência repetida';
            return false;
        }

        $base = substr($cnpj, 0, 12);
        $digitos = substr($cnpj, 12, 2);

        if ($this->calculaDigitoCnpj($base) != $digitos[0]) {
            $this->message = 'CNPJ inválido: primeiro dígito verificador incorreto';
            return false;
        }

        if ($this->calculaDigitoCnpj($base . $digitos[0]) != $digitos[1]) {
            $this->message = 'CNPJ inválido: segundo dígito verificador incorreto';
            return false;
        }

        return true;
    }

    /**
     * Dígito verificador de CNPJ (pesos 2 a 9, direita para a esquerda).
     * No formato alfanumérico, o valor do caractere é o próprio dígito ou
     * o código ASCII do caractere menos 48 (mesma tabela da Receita).
     */
    private function calculaDigitoCnpj($base)
    {
        $soma = 0;
        $peso = 2;

        for ($i = strlen($base) - 1; $i >= 0; $i--) {
            $caractere = $base[$i];
            $valor = is_numeric($caractere) ? $caractere : ord(strtoupper($caractere)) - 48;
            $soma += $valor * $peso++;
            if ($peso > 9) {
                $peso = 2;
            }
        }

        $resto = $soma % 11;
        return $resto < 2 ? 0 : 11 - $resto;
    }

    public function message()
    {
        return $this->message;
    }
}
