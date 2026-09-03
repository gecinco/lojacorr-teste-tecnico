<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CoerenciaFinanceira implements Rule
{
    private $valorTotal;
    private $quantidadeParcelas;

    public function __construct($valorTotal, $quantidadeParcelas)
    {
        $this->valorTotal = (float) $valorTotal;
        $this->quantidadeParcelas = (int) $quantidadeParcelas;
    }

    public function passes($attribute, $value)
    {
        if ($this->quantidadeParcelas <= 0) {
            return false;
        }

        $valorParcela = (float) $value;
        $valorCalculadoCentavos = (int) round($valorParcela * $this->quantidadeParcelas * 100);
        $valorTotalCentavos = (int) round($this->valorTotal * 100);
        $diferencaCentavos = abs($valorCalculadoCentavos - $valorTotalCentavos);

        // Tolerancia: 1 centavo por parcela (arredondamento contabil padrao)
        $toleranciaCentavos = $this->quantidadeParcelas;

        return $diferencaCentavos <= $toleranciaCentavos;
    }

    public function message()
    {
        if ($this->quantidadeParcelas <= 0) {
            return 'Quantidade de parcelas inválida.';
        }

        $valorEsperadoCentavos = (int) round(($this->valorTotal * 100) / $this->quantidadeParcelas);
        $valorEsperado = number_format($valorEsperadoCentavos / 100, 2, ',', '.');

        return "O valor das parcelas não confere com o valor total. " .
               "Valor esperado da parcela: R$ {$valorEsperado}";
    }
}