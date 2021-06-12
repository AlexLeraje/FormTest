<?php

use Core\App;
use Core\Functions\Safe;

class AutorizeApiController extends Controller
{
    public function defaultAction() :void
    {
        $class = App::$Router->action.'Model';
        $this->model = new $class();
        $this->model->checkAssess();

        $this->display();
    }

    private function display() :void
    {
        $data = $this->model->getData();
        echo json_encode(Safe::arrayHtmlentities($data));
    }
}