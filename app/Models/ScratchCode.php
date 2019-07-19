<?php

namespace App\Models;

use App\Helpers\GeneralHelper;
use Illuminate\Database\Eloquent\Model;

class ScratchCode extends Model
{
    protected $fillable = ['user_id', 'code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}