<?php

namespace Core\Classes;

class Validate
{
    public static int $calls = 0;

    public mixed $string = FALSE;
    public array $errors = [];

    public function __construct(mixed $string)
    {
        if(is_numeric($this->string) OR is_string($this->string))
            $this->string = trim($string);
        else
            $this->string = $string;
    }

    function __toString()
    {
        return $this->out();
    }

    //Преобразует ошибку-обьект в строку
    public function out()
    {
        $first_error = '';
        if(isset($this->errors[0]))
            $first_error = $this->errors[0];

        return $first_error;
    }

    //Отправляет error в новый экземпляр класса
    public function set_errors(array $errors)
    {
        $this->errors = $errors;
    }

    //Копирует класс сам в себя
    private function copy_validate()
    {
        self::$calls++;

        return $this;
    }

    // Проверяет является ли значение числом
    public function int()
    {
        if($this->string AND !is_numeric($this->string))
            $this->errors[] = 'Введенное значение не является числом!';

        return $this->copy_validate();
    }

    //проверяет является ли значение строкой или числом
    public function string()
    {
        if($this->string AND (!is_numeric($this->string) AND !is_string($this->string)))
            $this->errors[] = 'Введенное значение не является строкой!';

        return $this->copy_validate();
    }

    //проверяет не превышает длина значения установленную величину
    public function max_width(int $max_width)
    {
        if($this->string AND (mb_strlen($this->string) > $max_width))
            $this->errors[] = 'Значение слишком длинное, максимально символов: '.$max_width.'!';

        return $this->copy_validate();
    }

    //Проверяет не является длина строки меньше заданного значения
    public function min_width(int $min_width)
    {
        if($this->string AND (mb_strlen($this->string) < $min_width))
            $this->errors[] = 'Значение слишком короткое, минимально символов: '.$min_width.'!';

        return $this->copy_validate();
    }

    //проверяет не превышает ли число максимального значения
    public function max_value(int $max_value)
    {
        if($this->string AND !is_numeric($this->string))
            $this->errors[] = 'Введенное значение не является числом!';

        if($this->string AND intval($this->string) > $max_value)
            $this->errors[] = 'Введенное число слишком большое, максимальное: '.$max_value.'!';

        return $this->copy_validate();
    }

    //Проверяет не меньше ли число установленнной величины
    public function min_value(int $min_value)
    {
        if($this->string AND !is_numeric($this->string))
            $this->errors[] = 'Введенное значение не является числом!';

        if($this->string AND intval($this->string) < $min_value)
            $this->errors[] = 'Введенное число слишком маленькое, минимальное: '.$min_value.'!';

        return $this->copy_validate();
    }

    private function price_check($price)
    {
        $price = str_replace(',', '.', $price);
        if($price AND !preg_match('/^[0-9]+(\.[0-9]{2})?$/', $price))
            return FALSE;
        return TRUE;
    }

    //Проверка для цены, по сути - строка с числом не более чем 2 знаков после запятой, или целое число
    public function price()
    {
        if($this->string AND !$this->price_check($this->string))
            $this->errors[] = 'Введенное значение не является ценой!';

        return $this->copy_validate();
    }

    //проверяет не превышает ли цена максимальную цену
    public function max_price($max_price)
    {
        if($max_price AND !$this->price_check($max_price))
            $this->errors[] = 'Максимальное значение не является ценой!';

        if($this->string AND !$this->price_check($this->string))
            $this->errors[] = 'Введенное значение не является ценой!';

        if($this->string > $max_price)
            $this->errors[] = 'Введенная цена слишком велика, максимальная цена: '.$max_price.'!';

        return $this->copy_validate();
    }

    //проверяет не меньше ли цена минимального значения
    public function min_price($min_price)
    {
        if($min_price AND !$this->price_check($min_price))
            $this->errors[] = 'Минимальное значение не является ценой!';

        if($this->string AND !$this->price_check($this->string))
            $this->errors[] = 'Введенное значение не является ценой!';

        if($this->string < $min_price)
            $this->errors[] = 'Введенная цена слишком мала, минимальная цена: '.$min_price.'!';

        return $this->copy_validate();
    }

    private function float_check($float)
    {
        $float = str_replace(',', '.', $float);
        if($float AND !preg_match('/^[-+]?[0-9]*[.,]?[0-9]+(?:[eE][-+]?[0-9]+)?$/', $float))
            return FALSE;
        return TRUE;
    }

    //проверка чисел с плавающей запятой или целыз чисел
    public function float()
    {
        if($this->string AND !$this->float_check($this->string))
            $this->errors[] = 'Введенное значение не является целым или числом с плавающей точкой!!';

        return $this->copy_validate();
    }

    //проверяет не превышает ли дробь максимальную дробь
    public function max_float($max_float)
    {
        if($max_float AND !$this->float_check($max_float))
            $this->errors[] = 'Максимальное значение не является целым или числом с плавающей точкой!!';

        if($this->string AND !$this->float_check($this->string))
            $this->errors[] = 'Введенное значение не является целым или числом с плавающей точкой!!';

        if($this->string > $max_float)
            $this->errors[] = 'Введенное число слишком велико, максимальное число: '.$max_float.'!';

        return $this->copy_validate();
    }

    //проверяет не меньше ли дробь минимального значения
    public function min_float($min_float)
    {
        if($min_float AND !$this->price_check($min_float))
            $this->errors[] = 'Минимальное значение не является целым или числом с плавающей точкой!';

        if($this->string AND !$this->price_check($this->string))
            $this->errors[] = 'Введенное значение не является целым или числом с плавающей точкой!';

        if($this->string < $min_float)
            $this->errors[] = 'Введенное число слишком мало, минимальное число: '.$min_float.'!';

        return $this->copy_validate();
    }

    //обозначает обязательное для заполнения поле
    public function must()
    {
        if(!$this->string)
            $this->errors[] = 'Поле обязательно для заполнения!';

        return $this->copy_validate();
    }

    //Проверка корректности почты
    public function mail()
    {
        //Ультимативная регулярка для проверки мыл
        if($this->string AND !preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $this->string))
            $this->errors[] = 'Некорректный почтовый ящик!';

        return $this->copy_validate();
    }

    //Проверка корректности логина
    public function login()
    {
        if($this->string)
        {
            $validate = new Validate($this->string);
            $validate->set_errors($this->errors);

            if($login_check = $validate->min_width(6)->out())
                $this->errors[] = $login_check;
            elseif(preg_match('/[^\da-z]+/ui', $this->string) AND preg_match('/[^\dа-яА-ЯёЁ]+/ui', $this->string))
                $this->errors[] = 'Логин может содержать только буквы и цифры!';
        }

        return $this->copy_validate();
    }

    //Проверка корректности пароля
    public function password()
    {
        if($this->string)
        {
            $validate = new Validate($this->string);
            $validate->set_errors($this->errors);

            if($pass_check = $validate->min_width(6)->out())
                $this->errors[] = $pass_check;
        }

        return $this->copy_validate();
    }

    //Проверка корректности данных - массива
    public function array(): Validate
    {
        if($this->string AND !is_array($this->string))
            $this->errors[] = 'Неправильные данные, ожидается массив данных!';

        return $this->copy_validate();
    }

    //callback для расширения функционала прямо по месту использования
    public function custom(array $method)
    {
        $pointer = $method[0];
        $function  = $method[1];

        unset($method[0], $method[1]);

        $return = call_user_func_array(array($pointer, $function), array_merge([$this], $method));
        if(!$return)
            new Error('Custom method not exists or private!');
        return $return;
    }
}

