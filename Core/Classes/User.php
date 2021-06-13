<?php

namespace Core\Classes;

use Core\App;

class User
{
    public int $id = 0;
    public string|false $login = FALSE;

    public string $salt = 'GoodNewsEveryone!';

    public function __construct()
    {
        $this->getSessionData();
    }

    /* Если есть сессия, то пытаемся найти такого юзера  */
    private function getSessionData() :void
    {
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_pass']))
        {
            $user_id = abs(intval($_SESSION['user_id']));
            $user_pass = $_SESSION['user_pass'];

            if(!$this->getUserData($user_id, $user_pass))
                $this->unsetSession();
        }
        elseif (isset($_COOKIE['cuser_id']) && isset($_COOKIE['cuser_pass']))
        {
            $user_id = abs(intval($_COOKIE['cuser_id']));
            $user_pass = md5($this->salt.$_COOKIE['cuser_pass']);

            if(!$this->getUserData($user_id, $user_pass))
                $this->unsetSession();
            else
            {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_pass'] = $user_pass;
            }
        }
    }

    private function getUserData($user_id, $user_pass) :bool
    {
        $res = App::$Data->prepare_query('
            SELECT * FROM  `dse_users`
            WHERE `id` = ?
            AND `password` = ? LIMIT 1;',
            $user_id, $user_pass);
        if($res->num_rows)
        {
            $data = $res->fetch_array();

            $this->id = $data['id'];
            $this->login = $data['login'];

            return TRUE;
        }
        else
        {
            // TODO сделать регистрацию гостя в системе
        }

        return FALSE;
    }

    private function userDataExist($login, $pass) :int|false
    {
        $req = App::$Data->prepare_query('
            SELECT * FROM `dse_users` WHERE `login` = ? AND `password` = ? LIMIT 1',
            $login, md5(md5($this->salt.$pass)));
        if($req->num_rows)
        {
            $user = $req->fetch_array();
            return (int) $user['id'];
        }
        return FALSE;
    }

    public function sessionStart($login, $password, $cookie = 0) :bool
    {
        $user_id = $this->userDataExist($login, $password);
        if($user_id)
        {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_pass'] = md5(md5($this->salt.$password));

            if($cookie)
            {
                setcookie('cuser_id', $user_id, time() + 3600 * 24 * 365, '/');
                setcookie('cuser_pass', md5($this->salt.$password), time() + 3600 * 24 * 365, '/');
                return TRUE;
            }
        }

        return FALSE;
    }

    public function unsetSession() :void
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_pass']);

        unset($_COOKIE['cuser_id']);
        unset($_COOKIE['cuser_pass']);
        setcookie('cuser_id', '', 0 , '/');
        setcookie('cuser_pass', '', 0 , '/');
    }

    public static function loginExists(string $login) :int|false
    {
        if($count = App::$Data->selectFrom('Users')->andWhere('login', '=', $login)->execute()->numRows())
            return $count;

        return FALSE;
    }
}