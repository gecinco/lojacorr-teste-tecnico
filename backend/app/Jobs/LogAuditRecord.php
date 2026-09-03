<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Grava auditoria em MongoDB fora do caminho crítico da requisição HTTP. */
class LogAuditRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array */
    private $payload;

    public $tries = 3;
    public $timeout = 30;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        AuditLog::create($this->payload);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::warning('Job LogAuditRecord falhou', [
            'action' => $this->payload['action'] ?? null,
            'entity_id' => $this->payload['entity_id'] ?? null,
            'exception' => $exception->getMessage(),
        ]);
    }
}
