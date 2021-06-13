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
            $this->registerUser(
                $this->POST->var('Login'),
                $this->POST->var('Password'),
                $this->POST->var('Mail'),
                $this->POST->var('UserName')
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
                ->must()->string()->min_width(2)
                ->custom([$this, 'userNameCheck'])
                ->out(),
        ];
    }

    private function registerUser($login, $password, $mail, $userName)
    {
        App::$Data->insertInto('Users')->set([
            'login' => $login,
            'password' => md5(md5(App::$User->salt.$password)),
            'mail' => $mail,
            'name' => $userName,
        ])->execute();

        App::$User->sessionStart($login, $password);

        return TRUE;
    }

    public function loginExists(Validate $validate) :Validate
    {
        if(App::$User->loginExists($validate->string))
            $validate->errors[] = 'Логин занят!';

        return $validate;
    }

    public function comparePasswords(Validate $validate, $password) :Validate
    {
        if($validate->string != $password)
            $validate->errors[] = 'Пароли не совпадают!';

        return $validate;
    }

    public function mailExists(Validate $validate) :Validate
    {
        if(App::$User->mailExists($validate->string))
            $validate->errors[] = 'Такой почтовый ящик уже есть в системе!';

        return $validate;
    }

    public function userNameCheck(Validate $validate) :Validate
    {
        if($validate->string AND preg_match('/[^\da-zа-яё]+/ui', $validate->string))
            $validate->errors[] = 'Имя может содержать только буквы и цифры!';

        return $validate;
    }

    private function checkAccess() :void
    {
        if (App::$User->id)
            $this->apiError('Go Away!!');
    }
}