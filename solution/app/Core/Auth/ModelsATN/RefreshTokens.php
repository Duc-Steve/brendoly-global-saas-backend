<?php

namespace App\Core\Auth\ModelsATN;

use Illuminate\Database\Eloquent\Model;

class RefreshTokens extends Model
{
    protected $table = 'refresh_tokens';
    protected $primaryKey = 'id_refresh_token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_refresh_token',
        'token',
        'user_identity_id',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Définir la valeur par défaut
    protected $attributes = [
        'expires_at' => null, // ou une valeur par défaut si souhaité
    ];

    public function user()
    {
        return $this->belongsTo(UserIdentify::class, 'user_identity_id');
    }
}