<?php

namespace Core\Classes;

class Move
{
    public static function home()
    {
        header('Location:/');
        exit();
    }

    public static function page404()
    {
        header('Location:/page404');
        exit();
    }
}