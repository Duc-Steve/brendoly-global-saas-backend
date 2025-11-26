<?php

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

if (!function_exists('audit')) {
    function audit($universe, $action, $module, $description, $tenantId, $model = null, $userIdentifyId)
    {
        try {
            // Si le modèle est un Model Eloquent, on récupère ses infos
            $modelType = $model instanceof Model ? get_class($model) : null;
            $modelId = $model instanceof Model ? $model->getKey() : (is_string($model) ? $model : null);

            AuditLog::create([
                'id_audit_log' => Str::uuid(),
                'universe' => $universe,
                'action' => $action,
                'module' => $module,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'description' => $description,
                'ip_address' => request()->ip(),
                'tenant_id' => $tenantId,
                'user_identify_id' => $userIdentifyId,
            ]);
        
        } catch (\Exception $e) {
            // Log l'erreur mais ne bloque pas l'application
            Log::error('Erreur audit: ' . $e->getMessage());
        }
    }
}
