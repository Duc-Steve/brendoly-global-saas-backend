<?php

namespace App\Core\Auth\ModelsATN;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle RolesPermissions avec cryptage des données sensibles
 * @package App\Core\Auth\Models
 */
class Permissions extends Model
{
    use HasUuids;

    // Clé primaire personnalisée
    protected $primaryKey = 'id_role_permission';
    protected $table = 'roles_permissions';
    public $incrementing = false;  // La clé primaire n’est pas auto-incrémentée
    protected $keyType = 'string';   

    protected $fillable = [
        'role_id',
        'permission_id'
    ];

}