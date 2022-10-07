<?php

namespace Backpack\CRUD\app\Models\Traits;

use Backpack\CRUD\app\Library\Database\TableSchema;
use DB;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/*
|--------------------------------------------------------------------------
| Methods for working with relationships inside select/relationship fields.
|--------------------------------------------------------------------------
*/
trait HasRelationshipFields
{
    protected static $schema;

    /**
     * Register aditional types in doctrine schema manager for the current connection.
     *
     * @return DB
     */
    public function getConnectionWithExtraTypeMappings()
    {
        $conn = DB::connection($this->getConnectionName());

        $types = [
            'enum' => 'string',
            'json' => 'json_array',
            'jsonb' => 'json_array'
        ];

        // only register the extra types in sql databases
        if ($this->isSqlConnection()) {
            $platform = $this->getSchema()->getConnection()->getDoctrineSchemaManager()->getDatabasePlatform();
            foreach ($types as $type_key => $type_value) {
                if (! $platform->hasDoctrineTypeMappingFor($type_key)) {
                    $platform->registerDoctrineTypeMapping($type_key, $type_value);
                }
            }
        }

        return $conn;
    }

    /**
     * Get the model's table name, with the prefix added from the configuration file.
     *
     * @return string Table name with prefix
     */
    public function getTableWithPrefix()
    {
        $prefix = $this->getConnection()->getTablePrefix();
        $tableName = $this->getTable();

        return $prefix.$tableName;
    }

    /**
     * Get the column type for a certain db column.
     *
     * @param  string  $columnName  Name of the column in the db table.
     * @return string Db column type.
     */
    public function getColumnType($columnName)
    {
        if ($this->isSqlConnection()) {
            return self::getDbTableSchema()->getColumnType($columnName);
        }
        return 'text';
    }

    /**
     * Checks if the given column name is nullable.
     *
     * @param  string  $column_name  The name of the db column.
     * @return bool
     */
    public static function isColumnNullable($columnName)
    {
        if ($this->isSqlConnection()) {
            return self::getDbTableSchema()->columnIsNullable($columnName);
        }
        return true; 
    }

    /**
     * Checks if the given column name has default value set.
     *
     * @param  string  $columnName  The name of the db column.
     * @return bool
     */
    public static function dbColumnHasDefault($columnName)
    {
        if ($this->isSqlConnection()) {
            return self::getDbTableSchema()->columnHasDefault($columnName);
        }
        return false;
    }

    /**
     * Return the db column default value.
     *
     * @param  string  $column_name  The name of the db column.
     * @return bool
     */
    public static function getDbColumnDefault($columnName)
    {
        if ($this->isSqlConnection()) {
            return self::getDbTableSchema()->getColumnDefault($columnName);
        }
        return false;
        
    }

    /**
     * Return the current model connection and table name.
     */
    private static function getConnectionAndTable()
    {
        $instance = new static();
        $conn = $instance->getConnectionWithExtraTypeMappings();
        $table = $instance->getTableWithPrefix();

        return [$conn, $table];
    }

    public static function getDbTableSchema()
    {
        if (self::$schema) {
            return self::$schema;
        }

        [$connection, $table] = self::getConnectionAndTable();
        self::$schema = new TableSchema($connection->getName(), $table);

        return self::$schema;
    }

    private function isSqlConnection()
    {
        return in_array($this->getConnection()->getConfig()['driver'], CRUD::getSqlDriverList());
    }
}
