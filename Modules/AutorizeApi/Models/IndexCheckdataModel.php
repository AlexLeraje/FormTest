<?php

use Core\App;

class IndexCheckdataModel extends AutorizeApiModel
{
    public function getData() :array
    {
        $this->checkAccess();

        $error = $this->checkData(
            $this->POST->var('Login'),
            $this->POST->var('Password')
        );

        $out = array();
        if (!$this->arrayEmpty($error))
            $out['formerror'] = $error;
        return $out;
    }

    private function checkData($login, $password) :array
    {
        $error = [
            'Login' => $this->validate($login)->must()->login()->out(),
            'Password' => $this->validate($password)->must()->password()->out(),
        ];

        if (!$error['Login'] and !$error['Password']) {
            if(!App::$User->sessionStart($login, $password, $this->POST->var('RememberMe')))
                $error['Login'] = 'Неверный логин или пароль!';
        }


        return $error;
    }

    private function checkAccess()
    {
        if(App::$User->id)
            $this->api_error('Go Away!!');
    }

}