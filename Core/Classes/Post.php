<?php

namespace Core\Classes;

/**
 * Обертка для POST
 *
 * Например:
 * echo post->int(); - выведет натуральное число
 * echo post->var(); - выведет необработанное значение параметра
 * echo post->name(); - выведет название параметра
 * echo post->ent(); - выведет обработанное значение параметра
 */
class Post
{
    const NO_TRIM = 1;
    private array $post;

    public function __construct($params)
    {
        $this->post = $params;
    }

    public function __call($name, $arguments)
    {
        $param = $arguments[0];
        $setting = isset($arguments[1]) ? $arguments[1] : 0;

        if(isset($this->post[$param]))
        {
            if($setting != SELF::NO_TRIM)
                $variable = is_array($this->post[$param]) ? $this->post[$param] : trim($this->post[$param]);
            else
                $variable = $this->post[$param];

            $string_params = array('int', 'name', 'ent', 'var');
            if(in_array($name, $string_params) and is_array($this->post[$param]))
                new Error('Cant use string params to array, use method "array"!');

            if($name == 'int')
                return abs(intval($variable));
            elseif($name == 'name')
                return htmlentities($param, ENT_QUOTES, 'UTF-8');
            elseif($name == 'ent')
                return htmlentities($variable, ENT_QUOTES, 'UTF-8');
            elseif($name == 'var')
                return (string) $variable;
            elseif($name == 'array')
                return $variable;
            else
                new Error('Method did not exists!');

        }
        return '';
    }
}