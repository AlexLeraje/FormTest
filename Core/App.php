<?php

namespace Core;

use Core\Classes\Database;
use Core\Classes\Router;
use Core\Classes\User;
use Core\Classes\PostProtect;

class App
{
    private static Database $data;
    private static Router $router;
    private static User $user;

    public function __construct()
    {
        self::systemSet();
        new PostProtect;

        self::$data = self::databazeConnect();
        self::$router = new Router();
        self::$user = new User;
    }

    private static function databazeConnect() :Database
    {
        return new Database();
    }

    /* Парочку нужных настроек */
    private static function systemSet() :void
    {
        error_reporting(E_ALL);
        @ini_set('session.use_trans_sid', '0');
        @ini_set('arg_separator.output', '&amp;');
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Europe/London');

        //стартуем сессию
        session_start();

        ob_start(NULL, 0, PHP_OUTPUT_HANDLER_STDFLAGS);
    }
}