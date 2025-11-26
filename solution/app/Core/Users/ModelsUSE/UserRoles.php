<?php

namespace App\Core\Auth\ModelsATN;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle UserRoles avec cryptage des données sensibles
 * @package App\Core\Auth\Models
 */
class UserRoles extends Model
{
    use HasUuids;

    // Clé primaire personnalisée
    protected $primaryKey = 'id_user_role';
    protected $table = 'user_roles';
    public $incrementing = false;  // La clé primaire n’est pas auto-incrémentée
    protected $keyType = 'string';   

    protected $fillable = [
        'user_identify_id',
        'role_id'
    ];
}