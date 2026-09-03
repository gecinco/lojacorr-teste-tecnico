<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class VigenciaValida implements Rule
{
    private $inicioVigencia;

    public function __construct($inicioVigencia)
    {
        $this->inicioVigencia = $inicioVigencia;
    }

    public function passes($attribute, $value)
    {
        if (!$this->inicioVigencia || !$value) {
            return false;
        }

        try {
            $inicio = Carbon::parse($this->inicioVigencia);
            $fim = Carbon::parse($value);
            
            return $fim->gt($inicio);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message()
    {
        return 'A data de fim da vigência deve ser posterior à data de início';
    }
}
