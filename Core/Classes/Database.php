<?php

namespace Core\Classes;

class Database
{
    private string $databazeDir = 'Databaze';
    private string $databazeExt = 'xml';
    private array $databazeList;

    private string $currentTable;
    private array $whereConditions;

    public function __construct()
    {
        $this->databazeList = $this->getTablesList($this->databazeDir);
    }

    public function selectFrom(string $table) :Database
    {
        $this->newQuery($table);
        return $this;
    }

    public function where(array $conditions = []) :Database
    {
        $this->whereConditions = $conditions;
        return $this;
    }

    public function execute() :mixed
    {
        return [];
    }

    private function newQuery(string $table) :void
    {
        $this->currentTable = $table;
        $this->whereConditions = [];
    }

    private function getTablesList(string $databazeDir) :array
    {
        $filesList = scandir($databazeDir);

        $outList = [];
        foreach($filesList as $value)
        {
            if($value == '.' OR $value == '..')
                continue;

            $pathInfo =  pathinfo($databazeDir.'/'.$value);
            if($pathInfo['extension'] == $this->databazeExt AND $pathInfo['filename'])
                $outList[$pathInfo['filename']] = $pathInfo['filename'].'.'.$pathInfo['extension'];
        }

        return $outList;
    }
}