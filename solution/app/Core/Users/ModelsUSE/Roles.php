<?php

namespace App\Core\Auth\ModelsATN;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Roles avec cryptage des données sensibles
 * @package App\Core\Auth\Models
 */
class Roles extends Model
{
    use HasUuids;

    // Clé primaire personnalisée
    protected $primaryKey = 'id_role';
    protected $table = 'roles';
    public $incrementing = false;  // La clé primaire n’est pas auto-incrémentée
    protected $keyType = 'string';   

    protected $fillable = [
        'key', // ex : manager_stock
        'name', // "Manager stock"
        'description', // ............
        'is_active',
        'tenant_id', // null si ces un pour le coté super admin
    ];
}