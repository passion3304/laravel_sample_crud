<?php

namespace Backpack\CRUD;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class DatabaseSchema
{
    private static $schema;

    /**
     * Return the schema for the table.
     *
     * @param  string  $connection
     * @param  string  $table
     * @return array
     */
    public static function getForTable(string $connection, string $table)
    {
        self::generateDatabaseSchema($connection);

        return self::$schema[$connection][$table] ?? [];
    }

    /**
     * Return the schema for the connection.
     *
     * @param  string  $connection
     * @return array
     */
    public static function getForConnection(string $connection)
    {
        self::generateDatabaseSchema($connection);

        return self::$schema[$connection];
    }

    /**
     * Generates and store the database schema.
     *
     * @param  string  $connection
     * @return void
     */
    private static function generateDatabaseSchema(string $connection)
    {
        if (! isset(self::$schema[$connection])) {
            $rawColumns = DB::connection($connection)->getDoctrineSchemaManager()->createSchema();
            self::$schema[$connection] = self::mapColumns($rawColumns);
        }
    }

    /**
     * Maps the columns from raw db values into an usable array.
     *
     * @param  Doctrine\DBAL\Schema\Schema  $rawColumns
     * @return array
     */
    protected static function mapColumns($rawColumns)
    {
        return LazyCollection::make($rawColumns->getTables())->mapWithKeys(function ($table, $key) {
            return [$table->getName() => $table];
        })->toArray();
    }
}
