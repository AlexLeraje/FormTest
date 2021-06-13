<?php

namespace Core\Classes;

use \domDocument;

class Database
{
    private string $databazeDir = 'Databaze';
    private string $databazeExt = 'xml';
    private array $tablesList = [];

    private array $allowedColsTypes = [
        'int', 'string', 'autoint',
    ];

    private string $currentTable;
    private string $currentAction;
    private array $whereConditions;
    private array $insertData;

    public function __construct()
    {
        //$this->createTable('Users', [
        //    'id' => 'autoint',
        //    'login' => 'string',
        //    'password' => 'string',
        //    'mail' => 'string',
        //    'name' => 'string',
        //]);

        //$this->insertInto('Users')->set([
        //    'login' => 'web-demon',
        //    'mail' => 'alex.leraje@gmail.com',
        //])->execute();



        //$out = $this->selectFrom('Users')
        //    ->andWhere('login', '=', 'web-demon')
        //    ->execute();

    }

    public function insertInto(string $table) :Database
    {
        $this->newQuery($table);
        $this->currentAction = 'insert';
        return $this;
    }

    public function update(string $table) :Database
    {
        $this->newQuery($table);
        $this->currentAction = 'update';
        return $this;
    }

    public function selectFrom(string $table) :Database
    {
        $this->newQuery($table);
        $this->currentAction = 'select';
        return $this;
    }

    public function deleteFrom(string $table) :Database
    {
        $this->newQuery($table);
        $this->currentAction = 'delete';
        return $this;
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

    public function set($insertData) :Database
    {
        $this->insertData = $insertData;
        return $this;
    }

    public function andWhere(string $col , string $operand, string $value) :Database
    {
        $this->whereConditions[] = [
            'col' => $col,
            'operand' => $operand,
            'value' => $value,
            'action' => 'and',
        ];
        return $this;
    }

    public function orWhere(string $col , string $operand, string $value) :Database
    {
        $this->whereConditions[] = [
            'col' => $col,
            'operand' => $operand,
            'value' => $value,
            'action' => 'or',
        ];
        return $this;
    }

    public function execute() :mixed
    {
        $execActions = [
            'select' => 'selectAction',
            'insert' => 'insertAction',
            'delete' => 'deleteAction',
            'update' => 'updateAction',
        ];

        if(isset($execActions[$this->currentAction]))
        {
            $currentAction = $execActions[$this->currentAction];
            return $this->$currentAction();
        }

        new Error('Action '.$execActions[$this->currentAction].' didn\'t exists!');

        return FALSE;
    }

    private function selectAction() :DataResult
    {
        $table = new TableParser($this->databazeDir.'/'.$this->currentTable.'.'.$this->databazeExt);
        $structure = $table->getStructure();
        $data = $table->getData();

        $allowedOperands = ['='];

        $outData = $data;
        foreach($this->whereConditions AS $where)
        {
            if(!in_array($where['operand'], $allowedOperands))
                new Error('Uncknown operand "'.$where['operand'].'"');

            if(!isset($structure[$where['col']]))
                new Error('Uncknown col "'.$where['col'].'"');

            if($where['action'] == 'and')
            {
                $newData = [];
                foreach($outData AS $value)
                {
                    if($where['operand'] == '=')
                    {
                        if($value[$where['col']] == $where['value'])
                            $newData[] = $value;
                    }
                }
                $outData = $newData;
            }
        }

        return new DataResult($outData);
    }

    private function insertAction() :int
    {
        $table = new TableParser($this->databazeDir.'/'.$this->currentTable.'.'.$this->databazeExt);
        $structure = $table->getStructure();

        if(!$this->insertData)
            new Error('No insert data!');

        $errorCols = [];
        foreach($this->insertData AS $key => $value)
        {
            if(!isset($structure[$key]))
                $errorCols[] = $key;
        }

        if($errorCols)
            new Error('Unknown cols "'.implode(',', $errorCols).'"!');

        $insertId = 0;
        $insertRow = [];
        foreach ($structure AS $key => $value)
        {
            if($value['type'] == 'autoint')
            {
                $insertId = $value['lastId']+1;
                $insertRow[$key] = $insertId;
            }
            else
            {
                if(!isset($this->insertData[$key]))
                {
                    if($value['type'] == 'string')
                        $insertRow[$key] = '';
                    elseif($value['type'] == 'int')
                        $insertRow[$key] = 0;
                }
                else
                    $insertRow[$key] = $this->insertData[$key];
            }
        }

        $table->insertRow($insertRow);
        $table->save();

        return $insertId;
    }

    private function deleteAction() :array
    {
        return [];
    }

    private function updateAction() :array
    {
        return [];
    }

    private function newQuery(string $table) :void
    {
        $tablesList = $this->getTablesList($this->databazeDir);
        if(!in_array($table, $tablesList))
            new Error('Table '.$table.' didn\'t exists!');

        $this->currentTable = $table;
        $this->whereConditions = [];
        $this->insertData = [];
    }

    private function getTablesList(string $databazeDir) :array
    {
        if($this->tablesList)
            return $this->tablesList;

        $filesList = scandir($databazeDir);
        $this->tablesList = [];
        foreach($filesList as $value)
        {
            if($value == '.' OR $value == '..')
                continue;

            $pathInfo = pathinfo($databazeDir.'/'.$value);
            if($pathInfo['extension'] == $this->databazeExt AND $pathInfo['filename'])
                $this->tablesList[] = $pathInfo['filename'];
        }

        return $this->tablesList;
    }
}

class TableParser
{
    public array $structure;
    public array $data;
    private string $tablePath;
    private \DOMDocument $xml;
    private \DOMNode|null $rows;

    public function __construct(string $tablePath)
    {
        $this->tablePath = $tablePath;

        $this->xml = new domDocument("1.0", "utf-8");
        $this->xml->load($this->tablePath);

        $table = $this->xml->documentElement;
        $elements = $table->childNodes;
        for ($i = 0; $i < $elements->length; $i++)
        {
            $col = $elements->item($i);
            if($col->tagName == 'head')
                $this->structure = $this->parseStructure($col);
            elseif($col->tagName == 'rows')
            {
                $this->data = $this->parseData($col);
                $this->rows = &$col;
            }

        }
    }

    public function getStructure() :array
    {
        return $this->structure;
    }

    public function getData() :array
    {
        return $this->data;
    }

    private function parseStructure(\DOMElement $head) :array
    {
        $elements = $head->childNodes;
        $outRows = [];
        for ($i = 0; $i < $elements->length; $i++)
        {
            $col = $elements->item($i);

            $outRows[$col->nodeValue] = [
                'name' => $col->nodeValue,
                'type' => $col->getAttribute('type'),
                'lastId' => 0,
            ];
        }

        return $outRows;
    }

    private function parseData(\DOMElement $data) :array
    {
        $elements = $data->childNodes;
        $outRows = [];
        for ($i = 0; $i < $elements->length; $i++)
        {
            $row = $elements->item($i);
            $item = $row->childNodes;
            $out_items = [];
            for ($a = 0; $a < $item->length; $a++)
            {
                $cell = $item->item($a);
                $out_items[$cell->nodeName] = $cell->nodeValue;
                if($this->structure[$cell->nodeName]['type'] == 'autoint')
                {
                    if($cell->nodeValue >= $this->structure[$cell->nodeName]['lastId'])
                        $this->structure[$cell->nodeName]['lastId'] = $cell->nodeValue;
                }
            }
            $outRows[] = $out_items;
        }
        return $outRows;
    }

    public function insertRow(array $data) :void
    {
        $row = $this->xml->createElement('row');

        foreach($data AS $key => $value)
        {
            $element = $this->xml->createElement($key, $value);
            $row->appendChild($element);
        }

        $this->rows->appendChild($row);
    }

    public function save() :void
    {
        $this->xml->save($this->tablePath);
    }
}

class DataResult
{
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function numRows() :int
    {
        return count($this->data);
    }

    public function get() :array|false
    {
        $outData = array_shift($this->data);
        if($outData != NULL)
            return $outData;

        return FALSE;
    }
}