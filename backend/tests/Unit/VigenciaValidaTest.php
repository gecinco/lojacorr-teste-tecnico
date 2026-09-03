<?php

namespace Tests\Unit;

use App\Rules\VigenciaValida;
use Tests\TestCase;

class VigenciaValidaTest extends TestCase
{
    public function testValidaQuandoFimEPosteriorAoInicio()
    {
        $rule = new VigenciaValida('2024-01-01');

        $this->assertTrue($rule->passes('fim_vigencia', '2024-12-31'));
    }

    public function testValidaComDiferencaDeApenasUmDia()
    {
        $rule = new VigenciaValida('2024-06-15');

        $this->assertTrue($rule->passes('fim_vigencia', '2024-06-16'));
    }

    public function testRejeitaQuandoFimEIgualAoInicio()
    {
        $rule = new VigenciaValida('2024-06-15');

        $this->assertFalse($rule->passes('fim_vigencia', '2024-06-15'));
    }

    public function testRejeitaQuandoFimEAnteriorAoInicio()
    {
        $rule = new VigenciaValida('2024-12-31');

        $this->assertFalse($rule->passes('fim_vigencia', '2024-01-01'));
    }

    public function testRejeitaQuandoInicioENulo()
    {
        $rule = new VigenciaValida(null);

        $this->assertFalse($rule->passes('fim_vigencia', '2024-12-31'));
    }

    public function testRejeitaQuandoFimENulo()
    {
        $rule = new VigenciaValida('2024-01-01');

        $this->assertFalse($rule->passes('fim_vigencia', null));
    }

    public function testRejeitaQuandoDatasSaoInvalidas()
    {
        $rule = new VigenciaValida('data-invalida');

        $this->assertFalse($rule->passes('fim_vigencia', '2024-12-31'));
    }

    public function testRetornaMensagemDeErroApropriada()
    {
        $rule = new VigenciaValida('2024-12-31');
        $rule->passes('fim_vigencia', '2024-01-01');

        $this->assertStringContainsString('posterior', $rule->message());
    }
}
