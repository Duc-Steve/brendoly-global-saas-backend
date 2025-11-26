<?php

namespace App\Core\Auth\ModelsATN;

use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{

    
    protected $table = 'login_sessions';
    protected $fillable = [
        'ip_address',
        'device',
        'logged_in_at',
        'logged_out_at',
        'status',    // success, failed, locked
        'universe',  // platform | tenant
        'user_identity_id',
    ];

    protected $casts = [
        'logged_in_at'  => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function userIdentity()
    {
        return $this->belongsTo(UserIdentify::class);
    }
}