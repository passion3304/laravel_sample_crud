<?php

namespace Backpack\CRUD;

class TableSchema
{
    /** @var Doctrine\DBAL\Schema\Table */
    public $schema;

    public function __construct(string $connection, string $table) {
        $this->schema = DatabaseSchema::getForTable($connection, $table);
    }

    /**
     * Check if the column exists in the database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function hasColumn($columnName)
    {
        if (! $this->schemaExists()) {
            return false;
        }
        return $this->schema->hasColumn($columnName);
    }

    /**
     * Check if the column is nullable in database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function columnIsNullable($columnName)
    {
        if (! $this->columnExists($columnName)) {
            return true;
        }

        $column = $this->schema->getColumn($columnName);

        return $column->getNotnull() ? false : true;
    }

    /**
     * Check if the column has default value set on database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function columnHasDefault($columnName)
    {
        if (! $this->columnExists($columnName)) {
            return false;
        }

        $column = $this->schema->getColumn($columnName);

        return $column->getDefault() !== null ? true : false;
    }

    /**
     * Get the default value for a column on database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function getColumnDefault($columnName)
    {
        if (! $this->columnExists($columnName)) {
            return false;
        }

        $column = $this->schema->getColumn($columnName);

        return $column->getDefault();
    }

    /**
     * Make sure column exists or throw an exception.
     *
     * @param  string  $columnName
     * @return bool
     */
    private function columnExists($columnName)
    {
        if(! $this->schemaExists()) {
            return false;
        }
        return $this->schema->hasColumn($columnName);
    }

    /**
     * Make sure the schema for the connection is initialized.
     *
     * @return bool
     */
    private function schemaExists()
    {
        if (! empty($this->schema)) {
            return true;
        }
        return false;
    }
}
