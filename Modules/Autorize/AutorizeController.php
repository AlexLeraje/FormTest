<?php

use Core\App;
use Core\Classes\Temp;

class AutorizeController extends Controller
{
    public function defaultAction() :void
    {
        $class = App::$router->action.'Model';
        $this->model = new $class();

        $this->display();
    }

    private function display() :void
    {
        $view = new Temp('Theme/Templates/');

        //Вывод тела
        $view->set_data($this->model->getData());
        $view->display('Authorize'.App::$router->action);
    }
}