<?php

namespace App\Models;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $virtual = [];

    public function setVirtualField($name, $value)
    {
        $this->virtual[$name] = $value;
    }
}