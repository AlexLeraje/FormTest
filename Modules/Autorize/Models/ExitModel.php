<?php

use Core\Classes\Move;
use Core\App;

class ExitModel extends model
{
    public function getData() :array
    {
        if($this->is_post())
        {
            App::$User->unsetSession();
            Move::home();
        }
        else
            Move::home();

        return [];
    }

    //Смотрим была ли вообще отправлена форма
    private function is_post()
    {
        if($this->POST->var('act'))
            return TRUE;
        return FALSE;
    }
}