<?php

namespace core\Functions;

class Safe
{
    public static function arrayHtmlentities($array)
    {
        if(!is_array($array))
        {
            return htmlentities($array, ENT_QUOTES, 'UTF-8');
        }
        elseif(is_array($array))
        {
            $out = array();
            foreach($array AS $key => $value)
                $out[$key] = self::arrayHtmlentities($value);
            return $out;
        }
        return false;
    }
}