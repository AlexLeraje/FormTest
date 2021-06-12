<?php

use Core\Functions\Safe;
use Core\App;
use Core\Classes\Get;
use Core\Classes\Post;

abstract class Model
{
    public Get $GET;
    public Post $POST;

    public function __construct()
    {
        $this->GET = new Get(App::$Router->get);
        $this->POST = new Post($_POST);
    }

    public function apiError($errorText) :void
    {
        exit(json_encode(Safe::arrayHtmlentities(array(
            'api_error' => $errorText,
        ))));
    }
}