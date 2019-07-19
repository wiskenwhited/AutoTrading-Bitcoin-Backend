<?php
/**
 * Created by PhpStorm.
 * User: damirseremet
 * Date: 03/08/2017
 * Time: 10:46
 */

namespace App\Helpers;

class GeneralHelper
{
    public static function ConvertToKeyValueArray($data, $first_element_message = '')
    {
        $arr = [];
        if ($first_element_message) {
            $arr[0] = $first_element_message;
        }

        foreach ($data as $value) {
            if (isset($value['key'])) {
                $arr[$value['key']] = isset($value['value']) ? $value['value'] : '';
            }
        }

        return $arr;
    }


    public static function QuickOnlyNumbers($length = 25)
    {
        $pool = '0123456789';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

}