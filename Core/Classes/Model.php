<?php

use Core\Functions\Safe;
use Core\App;
use Core\Classes\Get;
use Core\Classes\Post;
use Core\Classes\Validate;

abstract class Model
{
    public Get $GET;
    public Post $POST;

    public function __construct()
    {
        $this->GET = new Get(App::$Router->get);
        $this->POST = new Post($_POST);
    }

    public function validate($string) :Validate
    {
        return new Validate($string);
    }

    public function apiError($errorText) :void
    {
        exit(json_encode(Safe::arrayHtmlentities(array(
            'api_error' => $errorText,
        ))));
    }

    public function arrayEmpty(array $array) :bool
    {
        $clear_array = array_filter($array, function($element) {
            return !empty($element);
        });

        if($clear_array)
            return FALSE;
        return TRUE;
    }

    abstract function getData();
}