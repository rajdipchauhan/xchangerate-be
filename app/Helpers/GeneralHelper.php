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
}