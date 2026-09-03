<?php

namespace Tests\Unit;

use App\Rules\DocumentoValido;
use Tests\TestCase;

class DocumentoValidoTest extends TestCase
{
    public function testValidaCpfCorreto()
    {
        $rule = new DocumentoValido();

        $this->assertTrue($rule->passes('documento', '12345678909'));
        $this->assertTrue($rule->passes('documento', '111.444.777-35'));
    }

    public function testRejeitaCpfComDigitosVerificadoresIncorretos()
    {
        $rule = new DocumentoValido();

        $this->assertFalse($rule->passes('documento', '12345678900'));
        $this->assertFalse($rule->passes('documento', '11144477700'));
    }

    public function testRejeitaCpfComSequenciaRepetida()
    {
        $rule = new DocumentoValido();

        $this->assertFalse($rule->passes('documento', '11111111111'));
        $this->assertFalse($rule->passes('documento', '00000000000'));
        $this->assertFalse($rule->passes('documento', '99999999999'));
    }

    public function testRejeitaCpfComTamanhoIncorreto()
    {
        $rule = new DocumentoValido();

        $this->assertFalse($rule->passes('documento', '123456789'));
        $this->assertFalse($rule->passes('documento', '1234567890123'));
    }

    public function testValidaCnpjCorreto()
    {
        $rule = new DocumentoValido();

        $this->assertTrue($rule->passes('documento', '11222333000181'));
        $this->assertTrue($rule->passes('documento', '11.222.333/0001-81'));
    }

    public function testRejeitaCnpjComDigitosVerificadoresIncorretos()
    {
        $rule = new DocumentoValido();

        $this->assertFalse($rule->passes('documento', '11222333000100'));
    }

    public function testRejeitaCnpjComSequenciaRepetida()
    {
        $rule = new DocumentoValido();

        $this->assertFalse($rule->passes('documento', '11111111111111'));
        $this->assertFalse($rule->passes('documento', '00000000000000'));
    }

    public function testRejeitaCnpjComTamanhoIncorreto()
    {
        $rule = new DocumentoValido();

        // 13 e 15 dígitos não são CNPJs válidos
        $this->assertFalse($rule->passes('documento', '1122233300018'));
        $this->assertFalse($rule->passes('documento', '112223330001812'));
    }

    public function testRetornaMensagemApropriadaParaCpfInvalido()
    {
        $rule = new DocumentoValido();
        $rule->passes('documento', '12345678900');

        $this->assertStringContainsString('CPF', $rule->message());
    }

    public function testRetornaMensagemApropriadaParaCnpjInvalido()
    {
        $rule = new DocumentoValido();
        $rule->passes('documento', '11222333000100');

        $this->assertStringContainsString('CNPJ', $rule->message());
    }

    public function testAceitaCnpjAlfanumericoValido()
    {
        $rule = new DocumentoValido();

        $this->assertTrue($rule->passes('documento', '12ABC34501DE35'));
        $this->assertTrue($rule->passes('documento', '12.abc.345/01de-35'));
    }

    public function testRejeitaCnpjAlfanumericoComDigitoIncorreto()
    {
        $rule = new DocumentoValido();

        $this->assertFalse($rule->passes('documento', '12ABC34501DE34'));
    }

    public function testRejeitaCnpjAlfanumericoComLetraNoDigitoVerificador()
    {
        $rule = new DocumentoValido();

        $this->assertFalse($rule->passes('documento', '12ABC34501DE3X'));
    }
}
