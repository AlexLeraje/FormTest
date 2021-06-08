<?php

namespace Core\Classes;

//Класс защиты от межсайтового скриптинга.
class PostProtect
{
    private int $formId;

    public function __construct()
    {
        //Уникальный ид, для борьбы с межсайтовым скриптингом
        if(!isset($_SESSION['form_id']) OR empty($_SESSION['form_id']))
            $_SESSION['form_id'] = rand(11111111, 999999999);
        $this->formId = intval(abs($_SESSION['form_id']));

        if($_POST)
        {
            if(!isset($_POST['formid']) or $_POST['formid'] != $this->formId)
                move::home();
        }
        return $this->formId;
    }

    public function id()
    {
        echo $this->formId;
    }

    public function input()
    {
        echo '<input type="hidden" name="formid" value="'. $this->formId .'" />';
    }
}