<?php

use Core\Functions\Safe;

abstract class Model
{
    public function apiError($errorText) :void
    {
        exit(json_encode(Safe::arrayHtmlentities(array(
            'api_error' => $errorText,
        ))));
    }
}