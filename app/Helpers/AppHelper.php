<?php

namespace App\Helpers;

class AppHelper
{
    /**
     * Checks if values in a given array are empty or not
     * 
     * @return Boolean
     */
    public static function hasEmpty($values)
    {
        foreach ($values as $val) {
            if (!$val) {
                return true;
            }
        }
        return false;
    }
}
