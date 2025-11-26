<?php

namespace App\Core\Auth\ModelsATN;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Permissions avec cryptage des données sensibles
 * @package App\Core\Auth\Models
 */
class Permissions extends Model
{
    use HasUuids;

    // Clé primaire personnalisée
    protected $primaryKey = 'id_permission';
    protected $table = 'permission';
    public $incrementing = false;  // La clé primaire n’est pas auto-incrémentée
    protected $keyType = 'string';   

    protected $fillable = [
        'module', // remplie si ces pour un module (ex : stock)
        'action', // view_item
        'key', // ex: stock.view_item
        'label', // "Voir articles"
        'universe' //platform or tenant
    ];

}