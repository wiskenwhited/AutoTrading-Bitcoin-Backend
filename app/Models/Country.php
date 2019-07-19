<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;

    public function scopeGetDropdown($scope, $default = false, $placeholder = false)
    {
        $list = $scope->select('country_code', 'country_name', 'id')->get();
        if (! $list) {
            return [];
        }
        $list = $list->toArray();
        if ($placeholder) {
            $list = [null => $placeholder] + $list;
        }

        return $list;
    }
}