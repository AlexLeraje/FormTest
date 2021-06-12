<?php

namespace Core\Classes;

/**
 * Класс для обработки параметров
 * По у молчанию значения всех параметров будут обрабатываться.
 *
 * Например:
 * echo get->int(); - выведет натуральное число
 * echo get->var(); - выведет необработанное значение параметра
 * echo get->name(); - выведет название параметра
 * echo get->ent(); - выведет обработанное значение параметра
 */
class Get
{
    public array $params = [];

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function __call($name, $arguments)
    {
        $param = $arguments[0];

        if(isset($this->params[$param]))
        {
            $variable = trim($this->params[$param]);

            if($name == 'int')
                return abs(intval($variable));
            elseif($name == 'name')
                return htmlentities($param, ENT_QUOTES, 'UTF-8');
            elseif($name == 'ent')
                return htmlentities($variable, ENT_QUOTES, 'UTF-8');
            elseif($name == 'var')
                return $variable;
        }
        return '';
    }
}