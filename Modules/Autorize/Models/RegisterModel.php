<?php

use Core\Classes\Move;
use Core\App;

class RegisterModel extends Model
{
    public function getData() :array
    {
        $this->checkAcces();
        return [];
    }

    private function checkAcces() :void
    {
        if(App::$User->id)
            Move::home();
    }
}