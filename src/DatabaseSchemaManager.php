<?php

namespace Backpack\CRUD;

use Exception;
use Illuminate\Support\Facades\DB;

class DatabaseSchemaManager
{
    private array $schema;

    public function __construct()
    {
        $this->generateDatabaseSchema(config('database.default'));
    }

    /**
     * Generates the database schema for different engines.
     *
     * @param  string  $connection
     * @return void
     */
    private function generateDatabaseSchema(string $connection)
    {
        if (! isset($this->schema[$connection])) {
            switch (DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
                case 'sqlite':
                    $rawColumns = DB::select("SELECT 
                            m.name AS TABLE_NAME, 
                            p.cid AS col_id,
                            p.name AS COLUMN_NAME,
                            p.type AS COLUMN_TYPE,
                            p.pk AS COLUMN_PK,
                            p.dflt_value AS COLUMN_DEFAULT,
                            p.[notnull] AS IS_NULLABLE
                        FROM sqlite_master m
                        LEFT OUTER JOIN pragma_table_info((m.name)) p
                        ON m.name <> p.name
                        WHERE m.type = 'table'
                        ORDER BY table_name, col_id");
                    break;

                default:
                    $rawColumns = DB::select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '".config('database.connections.'.$connection.'.database')."' ORDER BY table_name, ordinal_position");

            }

            $this->schema[$connection] = self::mapColumns($rawColumns);
        }
    }

    /**
     * Maps the columns from raw db values into an usable array.
     *
     * @param  mixed  $rawColumns
     * @return array
     */
    protected static function mapColumns($rawColumns)
    {
        switch (DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'sqlite' :
                $mappedColumns = collect($rawColumns)->mapToGroups(function ($item, $key) {
                    return [$item->TABLE_NAME => [
                        'name' => $item->COLUMN_NAME,
                        'is_nullable' => (bool) $item->IS_NULLABLE === true ? false : true, // sqlite uses "is not nullable" instead of "is nullable"
                        'is_primary' => $item->COLUMN_PK >= 1 ? true : false,
                        'default' => $item->COLUMN_DEFAULT ?? null,
                    ]];
                });
                break;

            default:
                $mappedColumns = collect($rawColumns)->mapToGroups(function ($item, $key) {
                    return [$item->TABLE_NAME => [
                        'name' => $item->COLUMN_NAME,
                        'is_nullable' => $item->IS_NULLABLE === 'YES' ? true : false,
                        'is_primary' => $item->COLUMN_KEY === 'PRI' ? true : false,
                        'default' => $item->COLUMN_DEFAULT ?? null,
                    ]];
                });

        }

        return $mappedColumns->toArray();
    }

    /**
     * Check if the column exists in the schema.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @param  string  $columnName
     * @return bool
     *
     * @throws Exception
     */
    public function hasColumn($connectionName, $tableName, $columnName)
    {
        $this->ensureSchemaExistence($connectionName);
        $this->ensureTableExistence($connectionName, $tableName);

        return in_array($columnName, array_column($this->schema[$connectionName][$tableName], 'name'));
    }

    /**
     * Check if the column is nullable in database.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @param  string  $columnName
     * @return bool
     */
    public function isColumnNullable($connectionName, $tableName, $columnName)
    {
        $this->validateInputs($connectionName, $tableName, $columnName);

        $column = current(array_filter($this->schema[$connectionName][$tableName], function ($column) use ($columnName) {
            return $column['name'] === $columnName;
        }));

        return $column['is_nullable'];
    }

    /**
     * Check if the provided data is valid.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @param  string  $columnName
     * @return void
     *
     * @throws Exception
     */
    private function validateInputs($connectionName, $tableName, $columnName = null)
    {
        $this->ensureSchemaExistence($connectionName);
        $this->ensureTableExistence($connectionName, $tableName);

        if ($columnName) {
            $this->ensureColumnExistence($connectionName, $tableName, $columnName);
        }
    }

    /**
     * Check if the column has default value set on database.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @param  string  $columnName
     * @return bool
     */
    public function columnHasDefault($connectionName, $tableName, $columnName)
    {
        $this->validateInputs($connectionName, $tableName, $columnName);

        $column = current(array_filter($this->schema[$connectionName][$tableName], function ($column) use ($columnName) {
            return$column['name'] === $columnName;
        }));

        return $column['default'] !== null ? true : false;
    }

    /**
     * Get the default value for a column on database.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @param  string  $columnName
     * @return bool
     */
    public function getColumnDefault($connectionName, $tableName, $columnName)
    {
        if ($this->columnHasDefault($connectionName, $tableName, $columnName)) {
            $column = current(array_filter($this->schema[$connectionName][$tableName], function ($column) use ($columnName) {
                return $column['name'] === $columnName;
            }));

            return $column['default'];
        }

        return null;
    }

    /**
     * Check if the table exists in database.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @return bool
     */
    public function hasTable($connectionName, $tableName)
    {
        $this->ensureSchemaExistence($connectionName);

        return isset($this->schema[$connectionName][$tableName]);
    }

    /**
     * Make sure table exists or throw an exception.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @return void
     *
     * @throws Exception
     */
    private function ensureTableExistence($connectionName, $tableName)
    {
        if (! $this->hasTable($connectionName, $tableName)) {
            throw new Exception('Table «'.$tableName.'» not found for connection:'.$connectionName);
        }
    }

    /**
     * Make sure column exists or throw an exception.
     *
     * @param  string  $connetionName
     * @param  string  $tableName
     * @param  string  $columnName
     * @return void
     *
     * @throws Exception
     */
    private function ensureColumnExistence($connectionName, $tableName, $columnName)
    {
        if (! $this->hasColumn($connectionName, $tableName, $columnName)) {
            throw new Exception('Column «'.$columnName.'» not found in «'.$tableName.'» table for connection:'.$connectionName);
        }
    }

    /**
     * Make sure the schema for the connection is initialized.
     *
     * @param  string  $connetionName
     * @return void
     */
    private function ensureSchemaExistence($connectionName)
    {
        $this->generateDatabaseSchema($connectionName);
    }
}
