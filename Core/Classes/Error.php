<?php

namespace Core\Classes;

class Error
{
    private static array $backtrace;

    public function __construct($text = '')
    {
        self::$backtrace = debug_backtrace();

        $class_call = self::$backtrace[1];
        $file_call =  self::$backtrace[2];

        echo self::template([
                'file' => $file_call['file'],
                'file_class' => $class_call['file'] ?? '',
                'line' => $file_call['line'],
                'line_class' => $class_call['line'] ?? '',
                'class' => $class_call['class'] ?? '',
                'text' => $text,
            ]);

        exit();
    }

    private static function template($arguments) :string
    {
        $replace = array();
        $search = array();
        foreach($arguments AS $key => $value)
        {
            $search[] = '{$'.$key.'}';
            $replace[] = htmlentities($value, ENT_QUOTES, 'UTF-8');
        }

        $view = str_replace($search, $replace, self::view());
        return str_replace('\n', '<br/>', $view);
    }

    private static function view() :string
    {
        return '
                <!DOCTYPE html>
                    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
                        <body>
                           <b>ERROR OCCURED</b>
                           <br/>FILE CALL in "{$file}" on line <b>{$line}</b>
                           <br/>CLASS CALL in "{$file_class}" on line <b>{$line_class}</b>
                           <hr/>
                           Class <b>{$class}</b> say:
                           {$text}
                        </body>
                    </html>
                ';
    }
}