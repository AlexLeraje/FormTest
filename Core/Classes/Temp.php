<?php

namespace Core\Classes;

use Core\Functions\Safe;

class Temp
{
    public array $vars = [];
    private string $temp_path;
    private string $file;
    private mixed $file_eval;
    private array $sections = [];

    private array $replace_offset;
    private array $search_conditions = [];
    private array $replace_conditions = [];

    private array $no_parse = [];
    private int $no_parse_counter = 0;

    function __construct($path)
    {
        $this->temp_path = $path;
    }

    public function set_data($data = []) :void
    {
        foreach($data AS $name => $value)
            $this->assign($name, $value);
    }

    public function assign($name, $var = '') :void
    {
        //Безопасный вывод
        $this->vars[$name] = Safe::arrayHtmlentities($var);
    }

    private function del_short_tags($m) :string
    {
        if($m[1])
            return '<?php echo '.$m[2].'?>';
        else
            return '<?php '.$m[2].'?>';
    }

    private function parse_include($match) :string
    {
        $include_file = $this->temp_path.$match[1].'.dst';

        if(!file_exists($include_file))
            new Error('Template '. $include_file .' didn\'n exists');

        return file_get_contents($include_file);
    }

    private function parse_variables($match) :string
    {
        if(!preg_match('/[^0-9a-z\_\-]+/', $match[1]))
        {
            if(!isset($this->vars[$match[1]]))
                $this->vars[$match[1]] = '';
        }
        return '<?=$'.$match[1].' ?>';
    }

    private function all_strpos(string $haystack, string $needle) :array
    {
        $lastPos = 0;
        $positions = [];

        while (($lastPos = mb_strpos($haystack, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + mb_strlen($needle);
        }

        return $positions;
    }

    private function parse_conditions($match) :string
    {
        $operator = $match[1];

        if(!isset($this->replace_offset[$operator]))
            $this->replace_offset[$operator] = 0;


        $operator_position = mb_strpos($this->file_eval, '@'.$operator, $this->replace_offset[$operator]);
        $this->replace_offset[$operator]  = $operator_position + 1;

        $next_operator_position = mb_strpos($this->file_eval, '@'.$operator, $this->replace_offset[$operator]);
        if(!$next_operator_position)
            $next_operator_position = mb_strlen($this->file_eval);

        $saved_right_offset = $this->replace_offset[$operator];

        $condition = '';
        while($saved_right_offset < $next_operator_position)
        {
            $right_bracket_pos = mb_strpos($this->file_eval, ')', $saved_right_offset);
            if(!$right_bracket_pos)
                break;

            if($right_bracket_pos < $next_operator_position)
            {
                $string = mb_substr($this->file_eval, $operator_position, $right_bracket_pos - $operator_position + 1);

                $br_lefts_count = count($this->all_strpos($string, '('));
                $br_rights_count = count($this->all_strpos($string, ')'));

                if($br_lefts_count == $br_rights_count)
                {
                    $left_br_pos = mb_strpos($string, '(');
                    $condition = mb_substr($string, ($left_br_pos + 1), (mb_strlen($string) - $left_br_pos - 2));

                    $this->search_conditions[] = $string;
                    $this->replace_conditions[] = '<? '.$operator.'('.$condition.'):?>';

                    break;
                }
            }
            $saved_right_offset = $right_bracket_pos + 1;
        }

        if(!$condition)
            new Error('Error template parse. Wrong @'.$operator.' condition!');

        return $match[0];
    }

    private function parse_else($match) :string
    {
        return '<?else:?>';
    }

    private function parse_endif($match) :string
    {
        return '<?endif?>';
    }

    private function parse_endforeach($match) :string
    {
        return '<?endforeach?>';
    }

    private function parse_extends($match) :string
    {
        $extends_file = $this->temp_path.$match[1].'.dst';

        if(!file_exists($extends_file))
            new Error('Template to extend '. $extends_file .' didn\'n exists');

        $this->file_eval = file_get_contents($extends_file);

        $this->parse_blocks();
        return '';
    }

    private function parse_section($match) :string
    {
        $this->sections[$match[1]] = $match[2];
        return '';
    }

    private function parse_yield($match) :string
    {
        if(isset($this->sections[$match[1]]))
            return $this->sections[$match[1]];
        return $match[0];
    }

    public function unhtmlentities($string) :string
    {
        return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    }

    private function parse_special_variables($match) :string
    {
        if($match[1] AND $match[1] == '$')
        {
            if(!preg_match('/[^0-9a-z\_\-]+/', $match[2]))
            {
                if(!isset($this->vars[$match[2]]))
                    $this->vars[$match[2]] = '';
            }
            return '<?=$this->unhtmlentities($'.$match[2].')?>';
        }
        else
            return '<? '.$match[2].' ?>';
    }

    function parse_blocks() :void
    {
        // @section('name_section', 'section_content')
        $this->file_eval = preg_replace_callback('#@section\(\'([0-9a-z\-_\.]{1,300})\', ?\'(.+?)\'\)#sui', array($this, 'parse_section'), $this->file_eval);
        // @section('name_section')section_content@endsection
        $this->file_eval = preg_replace_callback('#@section\(\'([0-9a-z\-_\.]{1,300})\'\)(.+?)@endsection#sui', array($this, 'parse_section'), $this->file_eval);
        // @extends('template')
        preg_replace_callback('#@extends ?\(\'([0-9a-z\-_./]{1,300})\'\)#sui', array($this, 'parse_extends'), $this->file_eval);
        //@yield('blockname')
        $this->file_eval = preg_replace_callback('#@yield ?\(\'([0-9a-z\-_\.]{1,300})\'\)#sui', array($this, 'parse_yield'), $this->file_eval);
        // @include('template')
        $this->file_eval = preg_replace_callback('#@include ?\(\'([0-9a-z\-_./]{1,300})\'\)#sui', array($this, 'parse_include'), $this->file_eval);
        // @section('name_section', 'section_content')
        $this->file_eval = preg_replace_callback('#@section\(\'([0-9a-z\-_\.]{1,300})\', ?\'(.+?)\'\)#sui', array($this, 'parse_section'), $this->file_eval);
        // @section('name_section')section_content@endsection
        $this->file_eval = preg_replace_callback('#@section\(\'([0-9a-z\-_\.]{1,300})\'\)(.+?)@endsection#sui', array($this, 'parse_section'), $this->file_eval);
        //@yield('blockname') (повторно, чтоб можно было в инклудах блоки парсить)
        $this->file_eval = preg_replace_callback('#@yield ?\(\'([0-9a-z\-_\.]{1,300})\'\)#sui', array($this, 'parse_yield'), $this->file_eval);
    }

    private function some_strange_bug_fix($string) :string
    {
        $string = preg_replace('#{{\ ?\$([0-9a-z\-_\[\]\"\']{1,300})\ ?}}(\
)#suix', '\0'."\n", $string);

        $string = preg_replace('#{!!\ ?(\$?)([0-9a-zа-яёА-Я\-_\[\]\"\':\(\)\-\>\\\ \/\*\!]{1,300})\ ?!!}(\
)#suix', '\0'."\n", $string);

        return $string;
    }

    private function save_noparse($m) :string
    {
        $this->no_parse[] = $m[1];

        return '@noparse @endnoparse';
    }

    private function return_noparse($m) :string
    {
        $out = '';
        if(isset($this->no_parse[$this->no_parse_counter]))
            $out = $this->no_parse[$this->no_parse_counter];

        $this->no_parse_counter++;

        return $out;
    }

    private function save_no_parse() :void
    {
        $this->file_eval = preg_replace_callback('#@noparse(.+?)@endnoparse#sui', array($this, 'save_noparse'), $this->file_eval);
    }

    private function return_no_parse() :void
    {
        $this->file_eval = preg_replace_callback('#@noparse(.+?)@endnoparse#sui', array($this, 'return_noparse'), $this->file_eval);
    }

    private function parse_special() :string
    {
        $this->save_no_parse();
        $this->parse_blocks();

        $this->file_eval = $this->some_strange_bug_fix($this->file_eval);
        $this->file_eval = preg_replace_callback('#{{\ ?\$([0-9a-z\-_\[\]\"\']{1,300})\ ?}}#suix', array($this, 'parse_variables'), $this->file_eval);
        // неэкранированные переменные {{ $var }}
        $this->file_eval = preg_replace_callback('#{!!\ ?(\$?)([0-9a-zа-яёА-Я\-_\[\]\"\':\(\)\-\>\\\ \/\*\!\,\s\$\.\=]{1,1000})\ ?!!}#suix', array($this, 'parse_special_variables'), $this->file_eval);

        // @if @elseif @foreach
        $this->file_eval = preg_replace_callback('#@(if|elseif|foreach)#sui', array($this, 'parse_conditions'), $this->file_eval);
        $this->file_eval = str_replace($this->search_conditions, $this->replace_conditions, $this->file_eval);

        // @else
        $this->file_eval = preg_replace_callback('#@else#sui', array($this, 'parse_else'), $this->file_eval);
        // @endif
        $this->file_eval = preg_replace_callback('#@endif#sui', array($this, 'parse_endif'), $this->file_eval);
        // @endforeach
        $this->file_eval = preg_replace_callback('#@endforeach#sui', array($this, 'parse_endforeach'), $this->file_eval);

        //Заменяем укороченные теги php на полные
        $this->file_eval = preg_replace_callback('/\<\?(\=)?(.*?)\?\>/si', array('self', 'del_short_tags'), $this->file_eval);

        $this->return_no_parse();

        return $this->file_eval;
    }

    public function return($file) :string
    {
        $this->file = $file;
        unset($file);

        if (!$this->file)
            new Error('Template is not defined');
        $this->file = $this->file.'.dst';
        if(!file_exists($this->temp_path.$this->file))
            new Error('File '.$this->file.' not found in '.$this->temp_path);

        $this->file_eval = file_get_contents($this->temp_path.$this->file);
        $this->file_eval = $this->parse_special();

        foreach ($this->vars as $key => $value)
            $$key = $value;

        $buffer_before = ob_get_clean();
        ob_start(NULL, 0, PHP_OUTPUT_HANDLER_STDFLAGS);

        $used_classes = '
            use Core\App;
        ';

        eval($used_classes.' ?>'.$this->file_eval.'<?');

        $template_executed = ob_get_clean();

        ob_start(NULL, 0, PHP_OUTPUT_HANDLER_STDFLAGS);
        echo $buffer_before;

        return $template_executed;

    }

    public function display($file) :void
    {
        echo $this->return($file);
    }
}