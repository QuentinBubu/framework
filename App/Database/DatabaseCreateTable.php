<?php

namespace App\Database;

class DatabaseCreateTable
{

    /**
     * @var bool $ifNotExists Create table if not exists condition
     * @var string $name Table name
     * @var array $allColumn Contain all columns request
     * @var array $allIndex Contain all index request
     * @var string $collate Collate of table
     * @var string $comments Comments of table
     * @var string $engine Engine of table
     */
    private $ifNotExists = false;
    private $name;
    private $allColumn = [];
    private $allIndex = [];
    private $collate = 'utf8_general_ci';
    private $comments;
    private $engine = 'InnoDB';
    private static $required = ['name'];

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function debug(): DatabaseCreateTable
    {
        var_dump(
            '<pre>',
            $this,
            '</pre>'
        );
        return $this;
    }

    /**
     * @return DatabaseCreateTable
     * @throws DatabaseException
     */
    public function __call($name, $arguments): DatabaseCreateTable
    {
        if (array_key_exists($name, get_class_vars(get_class($this)))) {
            if (count($arguments) === 0) {
                $this->{$name} = true;
            } elseif (count($arguments) === 1) {
                $this->{$name} = $arguments[0];
            } else {
                $this->{$name} = $arguments;
            }
            return $this;
        } else {
            throw new DatabaseException('Property not found.');
        }
    }

    public function column($arguments): DatabaseCreateTable
    {
        $this->allColumn[] = $arguments[0];
        return $this;
    }

    public function addIndex($arguments)
    {
        $this->allIndex[] = 
            strtoupper($arguments['type'])
            . (strtoupper($arguments['type']) === 'PRIMARY' ? ' KEY ' : ' INDEX ')
            . "`{$arguments['name']}`"
            . ' (`'
            . implode('`,`', $arguments['column'])
            . '`)';
        return $this;
    }

    private function make(): string
    {
        foreach (self::$required as $require) {
            if (is_null($this->{$require})) {
                throw new DatabaseException('A variable required is null');
            }
        }

        $request =
            ($this->ifNotExists ? '' : "DROP TABLE IF EXISTS `{$this->name}`; ")
            .'CREATE TABLE'
            . ($this->ifNotExists ? ' IF NOT EXISTS' : '')
            ." `{$this->name}` ("
            . implode(',', $this->allColumn)
            . (!is_null($this->allIndex) ? ',' . implode(',', $this->allIndex) : '')
            . ')'
            . " COLLATE='{$this->collate}'"
            . (!is_null($this->comments) ? " COMMENT '{$this->comments}'" : '')
            . " ENGINE={$this->engine}";

            return $request;
    }

    public function simulate(): DatabaseCreateTable
    {
        $request = $this->make();
        echo $request;
        return $this;
    }

    public function build()
    {
        $request = $this->make();
        Database::request($request, [], '');
    }
}