<?php
namespace App\Models;

use App\Core\Auth\ModelsATN\UserIdentify;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditLog
 *
 * Represents an audit trail for actions performed on models.
 * Tracks the user, action type, old/new values, description, and IP address.
 */
class AuditLog extends Model
{
    use HasFactory, HasUuids; // Enables Laravel factory support

    // Primary key configuration
    protected $primaryKey = 'id_audit_log'; // Custom primary key
    protected $keyType = 'string';          // UUID or string-based key
    public $incrementing = false;           // Disable auto-increment

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'universe',
        'action',       // Action performed (created, updated, deleted, etc.)
        'module',
        'model_type',   // Fully qualified class name of the affected model
        'model_id',     // ID of the affected model
        'old_values',   // Original attribute values before change
        'new_values',   // New attribute values after change
        'description',  // Optional description of the action
        'ip_address',   // IP address of the user performing the action
        'tenant_id',
        'user_identity_id'       // ID of the user performing the action
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'old_values' => 'array', // JSON to array
        'new_values' => 'array', // JSON to array
    ];

    // -----------------------------
    // 🔗 RELATIONSHIPS
    // -----------------------------

    /**
     * Each audit log belongs to a user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(UserIdentify::class, 'user_identity_id', 'id_user_identity');
    }

    // -----------------------------
    // 🔍 QUERY SCOPES
    // -----------------------------

    /**
     * Scope: filter logs created today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: filter logs by user ID.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_identity_id', $userId);
    }

    /**
     * Scope: filter logs by action type.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: filter logs by model type and optionally model ID.
     */
    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query = $query->where('model_type', $modelType);
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        return $query;
    }

    // -----------------------------
    // 🧮 ACCESSORS
    // -----------------------------

    /**
     * Accessor: returns a human-readable display name for the model.
     */
    public function getModelDisplayNameAttribute()
    {
        $models = [
            'App\Core\Auth\ModelsATN\UserIdentify' => 'UserIdentify',
            'App\Core\Auth\ModelsATN\AuthToken' => 'AuthToken',
            'App\Core\Auth\ModelsATN\LoginSession' => 'LoginSession',
            'App\Core\MultiTenancy\ModelsMTY\Tenant' => 'Tenant',
        ];
        return $models[$this->model_type] ?? class_basename($this->model_type);
    }

    /**
     * Accessor: returns a human-readable display name for the action.
     */
    public function getActionDisplayNameAttribute()
    {
        $actions = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'viewed' => 'Viewed',
            'login' => 'Login',
            'logout' => 'Logout',
        ];
        return $actions[$this->action] ?? $this->action;
    }
    
}
