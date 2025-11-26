<?php

namespace App\Core\MultiTenancy\ModelsMTY;

use App\Core\Auth\ModelsATN\UserIdentify;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Modèle Tenant avec cryptage des données sensibles de l'entreprise
 * @package App\Core\MultiTenancy\ModelsMTY
 */
class Tenant extends Model
{
    use HasFactory, HasUuids;

    // Clé primaire personnalisée
    protected $primaryKey = 'id_tenant';
    public $incrementing = false;  // La clé n’est pas auto-incrémentée
    protected $keyType = 'string';   

    // Champs pouvant être remplis en masse
    protected $fillable = [
        'id_tenant',
        'name',
        'type',
        'sector',
        'employees_number',
        'address',
        'city',
        'zipcode',
        'country',
        'is_active'
    ];

    // Définition des types des champs
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Fonction interne pour crypter une valeur
    protected function encryptAttribute($value)
    {
        return $value ? Crypt::encryptString($value) : null;
    }

    // Fonction interne pour décrypter une valeur
    protected function decryptAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value; // Retourne la valeur brute si décryptage échoue
        }
    }

    // Accesseurs et mutateurs pour crypter/décrypter automatiquement les attributs
    public function setNameAttribute($value) { $this->attributes['name'] = $this->encryptAttribute($value); }
    public function getNameAttribute($value) { return $this->decryptAttribute($value); }

    public function setTypeAttribute($value) { $this->attributes['type'] = $this->encryptAttribute($value); }
    public function getTypeAttribute($value) { return $this->decryptAttribute($value); }

    public function setSectorAttribute($value) { $this->attributes['sector'] = $this->encryptAttribute($value); }
    public function getSectorAttribute($value) { return $this->decryptAttribute($value); }

    public function setEmployeesNumberAttribute($value) { $this->attributes['employees_number'] = $this->encryptAttribute($value); }
    public function getEmployeesNumberAttribute($value) { return $this->decryptAttribute($value); }

    public function setAddressAttribute($value) { $this->attributes['address'] = $this->encryptAttribute($value); }
    public function getAddressAttribute($value) { return $this->decryptAttribute($value); }

    public function setCityAttribute($value) { $this->attributes['city'] = $this->encryptAttribute($value); }
    public function getCityAttribute($value) { return $this->decryptAttribute($value); }

    public function setCountryAttribute($value) { $this->attributes['country'] = $this->encryptAttribute($value); }
    public function getCountryAttribute($value) { return $this->decryptAttribute($value); }

    // Relation avec le modèle User : un tenant peut avoir plusieurs utilisateurs
    public function users()
    {
        return $this->hasMany(UserIdentify::class, 'tenant_id', 'id_tenant');
    }
}
