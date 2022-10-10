<?php

namespace Backpack\CRUD\app\Models\Traits;

use Backpack\CRUD\app\Library\Database\TableSchema;
use DB;

/*
|--------------------------------------------------------------------------
| Methods for working with relationships inside select/relationship fields.
|--------------------------------------------------------------------------
*/
trait HasRelationshipFields
{
    /**
     * Register aditional types in doctrine schema manager for the current connection.
     *
     * @return DB
     */
    public function getConnectionWithExtraTypeMappings()
    {
        $conn = DB::connection($this->getConnectionName());

        // register the enum, and jsonb types
        $conn->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $conn->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('jsonb', 'json');

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
        return self::getDbTableSchema()->getColumnType($columnName);
    }

    /**
     * Checks if the given column name is nullable.
     *
     * @param  string  $column_name  The name of the db column.
     * @return bool
     */
    public static function isColumnNullable($columnName)
    {
        return self::getDbTableSchema()->columnIsNullable($columnName);
    }

    /**
     * Checks if the given column name has default value set.
     *
     * @param  string  $columnName  The name of the db column.
     * @return bool
     */
    public static function dbColumnHasDefault($columnName)
    {
        return self::getDbTableSchema()->columnHasDefault($columnName);
    }

    /**
     * Return the db column default value.
     *
     * @param  string  $column_name  The name of the db column.
     * @return bool
     */
    public static function getDbColumnDefault($columnName)
    {
        return self::getDbTableSchema()->getColumnDefault($columnName);
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
        [$connection, $table] = self::getConnectionAndTable();

        return new TableSchema($connection->getName(), $table);
    }
}
