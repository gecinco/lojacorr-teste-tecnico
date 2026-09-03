<?php

namespace Tests\Unit;

use App\Rules\CoerenciaFinanceira;
use Tests\TestCase;

class CoerenciaFinanceiraTest extends TestCase
{
    public function testValidaQuandoValorParcelaMultiplicadoPeloTotal()
    {
        $rule = new CoerenciaFinanceira(1200.00, 12);

        $this->assertTrue($rule->passes('valor_parcela', 100.00));
    }

    public function testValidaComToleranciaDeUmCentavo()
    {
        $rule = new CoerenciaFinanceira(100.00, 3);

        // 100 / 3 = 33.333... arredondado = 33.33
        // 33.33 * 3 = 99.99 (diferença de 0.01, dentro da tolerância)
        $this->assertTrue($rule->passes('valor_parcela', 33.33));
    }

    public function testRejeitaQuandoDiferencaExcedeTolerancia()
    {
        $rule = new CoerenciaFinanceira(1200.00, 12);

        $this->assertFalse($rule->passes('valor_parcela', 90.00));
        $this->assertFalse($rule->passes('valor_parcela', 110.00));
    }

    public function testRejeitaQuantidadeDeParcelasZero()
    {
        $rule = new CoerenciaFinanceira(1200.00, 0);

        $this->assertFalse($rule->passes('valor_parcela', 100.00));
    }

    public function testRejeitaQuantidadeDeParcelasNegativa()
    {
        $rule = new CoerenciaFinanceira(1200.00, -1);

        $this->assertFalse($rule->passes('valor_parcela', 100.00));
    }

    public function testRetornaMensagemDeErroApropriada()
    {
        $rule = new CoerenciaFinanceira(1200.00, 12);
        $rule->passes('valor_parcela', 90.00);

        $this->assertStringContainsString('valor das parcelas', $rule->message());
        $this->assertStringContainsString('R$', $rule->message());
    }

    public function testFuncionaComValoresDecimaisComplexos()
    {
        $rule = new CoerenciaFinanceira(999.99, 7);

        // 999.99 / 7 = 142.8557... ≈ 142.86 → 142.86 * 7 = 1000.02 (diferença de 0.03).
        // Dentro da tolerância de 1 centavo por parcela → é arredondamento válido.
        $this->assertTrue($rule->passes('valor_parcela', 142.86));

        // Diferença acima da tolerância continua rejeitada.
        $this->assertFalse($rule->passes('valor_parcela', 143.00));
    }

    public function testFuncionaComParcelaUnica()
    {
        $rule = new CoerenciaFinanceira(5000.00, 1);

        $this->assertTrue($rule->passes('valor_parcela', 5000.00));
    }
}
