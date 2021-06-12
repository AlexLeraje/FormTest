<?php

namespace Core\Classes;

use \domDocument;

class Database
{
    private string $databazeDir = 'Databaze';
    private string $databazeExt = 'xml';
    private array $databazeList;

    private array $allowedColsTypes = [
        'int', 'string', 'autoint',
    ];

    private string $currentTable;
    private string $currentAction;
    private array $whereConditions;

    public function __construct()
    {
        //$this->createTable('Users', [
        //    'id' => 'autoint',
        //    'userName' => 'string',
        //    'userPassword' => 'string',
        //    'userMail' => 'string',
        //]);
    }

    public function selectFrom(string $table) :Database
    {
        $this->newQuery($table);
        $this->currentAction = 'select';
        return $this;
    }

    public function where(array $conditions = []) :Database
    {
        $this->whereConditions = $conditions;
        return $this;
    }

    public function execute() :mixed
    {
        $execActions = [
            'select' => 'selectAction',
        ];

        return [];
    }

    public function createTable(string $tableName, $tableStructure = []) :bool
    {
        //TODO дополнительно сделать проверку названия таблицы регуляркой

        if(file_exists($this->databazeDir.'/'.$tableName.'.'.$this->databazeExt))
            new Error('Table '.$tableName.' already exists!');

        $xml = new domDocument("1.0", "utf-8");

        $table = $xml->createElement('table');
        $xml->appendChild($table);

        $head = $xml->createElement('head');
        $table->appendChild($head);

        $id = 1;
        foreach($tableStructure AS $key => $value)
        {
            //TODO сделать проверку названия поля $key регуляркой
            if($key AND in_array($value, $this->allowedColsTypes))
            {
                $col = $xml->createElement('col', $key);
                $col->setAttribute('type', $value);
                $col->setAttribute('id', $id);
                $head->appendChild($col);
                $id++;
            }
            else
                new Error('Unknown col type: '.$value);
        }
        $rows = $xml->createElement('rows');
        $table->appendChild($rows);
        $xml->save($this->databazeDir.'/'.$tableName.'.'.$this->databazeExt);

        return TRUE;
    }

    private function newQuery(string $table) :void
    {
        $databazeList = $this->getTablesList($this->databazeDir);
        if(!isset($databazeList[$table]))
            new Error('Table '.$table.' didn\'t exists!');

        $this->currentTable = $table;
        $this->whereConditions = [];
    }

    private function getTablesList(string $databazeDir) :array
    {
        if($this->databazeList)
            return $this->databazeList;

        $filesList = scandir($databazeDir);
        $this->databazeList = [];
        foreach($filesList as $value)
        {
            if($value == '.' OR $value == '..')
                continue;

            $pathInfo =  pathinfo($databazeDir.'/'.$value);
            if($pathInfo['extension'] == $this->databazeExt AND $pathInfo['filename'])
                $outList[$pathInfo['filename']] = $pathInfo['filename'].'.'.$pathInfo['extension'];
        }

        return $this->databazeList;
    }
}