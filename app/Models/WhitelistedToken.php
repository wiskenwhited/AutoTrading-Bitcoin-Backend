<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhitelistedToken extends Model
{
    protected $fillable = [
        'user_id',
        'token'
    ];

    public function scopeByToken($query, $token)
    {
        return $query->where('token', $token);
    }
}