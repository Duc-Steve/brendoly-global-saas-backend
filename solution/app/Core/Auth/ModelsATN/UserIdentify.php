<?php

namespace App\Core\Auth\ModelsATN;

use App\Core\MultiTenancy\ModelsMTY\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Crypt;

/**
 * Modèle User avec cryptage des données sensibles
 * @package App\Core\Auth\Models
 */
class UserIdentify extends Authenticatable
{
    // Ajout des traits pour gérer les tokens API, les notifications et les factories
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    // Clé primaire personnalisée
    protected $primaryKey = 'id_user_identity';
    public $incrementing = false;  // La clé primaire n’est pas auto-incrémentée
    protected $keyType = 'string';   

    // Champs pouvant être remplis en masse
    protected $fillable = [
        'id_user_identity',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'tenant_id',
        'is_active',
        'is_superadmin',
        'last_login_at'
    ];

    // Champs cachés lors de la sérialisation (ex. JSON)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Définition des types des champs
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',   // Hash automatique du mot de passe
        'is_superadmin' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Cryptage automatique du prénom
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = Crypt::encryptString($value);
    }

    // Décryptage automatique du prénom
    public function getFirstNameAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Retourne la valeur brute si décryptage échoue
        }
    }

    // Cryptage automatique du nom
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = Crypt::encryptString($value);
    }

    // Décryptage automatique du nom
    public function getLastNameAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Retourne la valeur brute si décryptage échoue
        }
    }

    // Relation avec le modèle Tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id_tenant');
    }
}
