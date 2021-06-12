<?php

namespace Core;

use Core\Classes\Database;
use Core\Classes\Router;
use Core\Classes\User;
use Core\Classes\PostProtect;

class App
{
    public static Database $Data;
    public static Router $Router;
    public static User $User;
    public static PostProtect $FormProtect;

    public function __construct()
    {
        self::systemSet();
        self::$FormProtect = new PostProtect();

        self::$Data = self::databazeConnect();
        self::$User = new User;

        self::runRoute();
    }

    private static function databazeConnect() :Database
    {
        return new Database();
    }

    private static function runRoute()
    {
        self::$Router = new Router();
        self::$Router->runModule();
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