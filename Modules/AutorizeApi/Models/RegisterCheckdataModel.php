<?php

use Core\App;
use Core\Classes\Validate;

class RegisterCheckdataModel extends AutorizeApiModel
{
    public function getData() :array
    {
        $this->checkAccess();
        $error = $this->checkData();

        $out =[];
        if(!$this->arrayEmpty($error))
            $out['formerror'] = $error;
        else
            $this->register_user(
                $this->POST->var('login'),
                $this->POST->var('password'),
                $this->POST->var('mail')
            );

        return $out;
    }

    private function checkData() :array
    {
        $password = $this->POST->var('Password');
        return [
            'Login' => $this->validate($this->POST->var('Login'))
                ->must()->login()
                ->custom([$this, 'loginExists'])
                ->out(),
            'Password' => $this->validate($password)
                ->must()->password()
                ->out(),
            'RepeatPassword' => $this->validate($this->POST->var('RepeatPassword'))
                ->must()->password()
                ->custom([$this, 'comparePasswords', $password])
                ->out(),
            'Mail' => $this->validate($this->POST->var('Mail'))
                ->must()->mail()
                ->custom([$this, 'mailExists'])
                ->out(),
            'UserName' => $this->validate($this->POST->var('UserName'))
                ->string()->min_width(2)->out(),
        ];
    }

    public function loginExists(Validate $validate) :Validate
    {
        if(App::$User->loginExists($validate->string))
            $validate->errors[] = 'Логин занят!';

        return $validate;
    }

    private function checkAccess() :void
    {
        if (App::$User->id)
            $this->apiError('Go Away!!');
    }
}