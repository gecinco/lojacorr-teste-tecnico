<?php

namespace App\Services;

use App\Jobs\LogAuditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        string $action,
        string $entityType,
        $entityId,
        ?array $oldData = null,
        ?array $newData = null,
        array $metadata = []
    ) {
        try {
            $user = Auth::user();

            $payload = [
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => (string) $entityId,
                'user_id'     => $user ? $user->id : null,
                'user_name'   => $user ? $user->name : 'Sistema',
                'old_data'    => $oldData,
                'new_data'    => $newData,
                'ip_address'  => Request::ip(),
                'user_agent'  => Request::userAgent(),
                'metadata'    => $metadata,
            ];

            // Despachado via fila: quando QUEUE_CONNECTION=sync roda inline,
            // caso contrário sai do caminho crítico da resposta HTTP.
            LogAuditRecord::dispatch($payload);
        } catch (\Exception $e) {
            \Log::warning('Falha ao registrar log de auditoria', [
                'action' => $action,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
