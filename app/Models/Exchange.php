<?php

namespace App\Models;

class Exchange extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name'
    ];
}