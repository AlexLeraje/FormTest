<?php
const CORE = 1;

spl_autoload_register(function ($class)
{
    $path = str_replace('\\', '/', $class.'.php');
    if(file_exists(__DIR__ . "/" . $path))
        require_once($path);
    else
    {
        die('Class '.$path.' not found!');
    }
});

new Core\App();