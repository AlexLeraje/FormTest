<?php

namespace Core\Classes;

class Router
{
    public string $module = 'Autorize';
    public string $action = 'Index';
    private array $params = [];

    public function __construct()
    {
        $this->parseRoute();
        $this->runModule();
    }

    /**
     * Разбираем строку
     * 0 - Выполняемый модуль
     * 1- Выполняемое действие
     * все остальное попарно параметры, четное - название, нечетное - параметр
     * например: 2-название параметра, 3- сам параметр и тд
     */
    private function parseRoute() :void
    {
        $url = $_GET['route'] ?? '';
        if($url)
        {
            $route_arr = self::split($url);

            //Загружаемый модуль
            if(isset($route_arr[0]) AND $this->checkAction($route_arr[0]))
            {
                $this->module = $route_arr[0];
                unset($route_arr[0]);
            }

            //Выполняемое действие
            if(isset($route_arr[1]) AND $this->checkAction($route_arr[1]))
            {
                $this->action = $route_arr[1];
                unset($route_arr[1]);
            }

            $this->params = $route_arr;
        }
    }

    /** Проверка действий и названий параметров. */
    private function checkAction(string $action) :int|false
    {
        return preg_match("/^[a-z0-9\_]+$/", $action);
    }

    private static function split($url) :array|false
    {
        return preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function runModule() :void
    {
        $path_control = 'Modules/'.$this->module.'/'.$this->module.'Controller.php';
        $path_model = 'Modules/'.$this->module.'/Models/'.$this->action.'Model.php';

        if(file_exists($path_control) AND file_exists($path_model))
        {
            //Абстрактные классы
            require_once('Core/Classes/Controller.php');
            require_once('Core/Classes/Model.php');

            //Общая модель на модуль (не обязательно),
            //Чтоб вытащить общие для модуля данные и не плодить копипасту в других моделях
            //Чтоб его использовать нужно наследоваться не от абстрактной модели, а от него.
            $path_grang_model = 'Modules/'.$this->module.'/'.$this->module.'Model.php';
            if(file_exists($path_grang_model))
                require_once($path_grang_model);

            //Классы модуля
            require_once($path_control);
            require_once($path_model);

            $class = $this->module.'Controller';

            $action = new $class();
            $action->defaultAction();
        }
        else
            new Error('Module '.$this->module.'/'.$this->action.' not found!');
    }
}