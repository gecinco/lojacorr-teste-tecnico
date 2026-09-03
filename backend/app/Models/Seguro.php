<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguro extends Model
{
    protected $table = 'seguros';

    protected $fillable = [
        'user_id',
        'seguradora_id',
        'ramo_id',
        'documento_segurado',
        'tipo_documento',
        'nome_segurado',
        'valor_total',
        'quantidade_parcelas',
        'valor_parcela',
        'inicio_vigencia',
        'fim_vigencia',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'quantidade_parcelas' => 'integer',
        'inicio_vigencia' => 'date',
        'fim_vigencia' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seguradora()
    {
        return $this->belongsTo(Seguradora::class);
    }

    public function ramo()
    {
        return $this->belongsTo(Ramo::class);
    }

    /**
     * Compara datas como string (Y-m-d) para evitar instanciar Carbon.
     * O cast 'date' garante que os atributos sejam Carbon; comparamos via ->format().
     */
    public function getStatusVigenciaAttribute(): string
    {
        $hoje = date('Y-m-d');
        $inicio = $this->inicio_vigencia instanceof \DateTimeInterface
            ? $this->inicio_vigencia->format('Y-m-d')
            : (string) $this->inicio_vigencia;
        $fim = $this->fim_vigencia instanceof \DateTimeInterface
            ? $this->fim_vigencia->format('Y-m-d')
            : (string) $this->fim_vigencia;

        if ($hoje < $inicio) {
            return 'a_vencer';
        }

        if ($hoje > $fim) {
            return 'vencido';
        }

        return 'vigente';
    }

    public function getDocumentoFormatadoAttribute(): string
    {
        $documento = $this->documento_segurado;

        if ($this->tipo_documento === 'cpf') {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
    }

    public function scopeByDocumento($query, $documento)
    {
        $documentoLimpo = preg_replace('/[\s.\-\/]/', '', $documento);
        return $query->where('documento_segurado', $documentoLimpo);
    }

    public function scopeByRamo($query, $ramoId)
    {
        return $query->where('ramo_id', $ramoId);
    }

    public function scopeBySeguradora($query, $seguradoraId)
    {
        return $query->where('seguradora_id', $seguradoraId);
    }
}
