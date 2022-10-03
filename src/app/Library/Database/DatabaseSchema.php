<?php

namespace Backpack\CRUD\app\Library\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

final class DatabaseSchema
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
        self::generateDatabaseSchema($connection, $table);

        return self::$schema[$connection][$table] ?? [];
    }

    /**
     * Generates and store the database schema.
     *
     * @param  string  $connection
     * @param  string  $table
     * @return void
     */
    private static function generateDatabaseSchema(string $connection, string $table)
    {
        if (! isset(self::$schema[$connection])) {
            $rawTables = DB::connection($connection)->getDoctrineSchemaManager()->createSchema();
            self::$schema[$connection] = self::mapTables($rawTables);
        } else {
            // check for a specific table in case it was created after schema had been generated.
            if (! isset(self::$schema[$connection][$table])) {
                self::$schema[$connection][$table] = DB::connection($connection)->getDoctrineSchemaManager()->listTableDetails($table);
            }
        }
    }

    /**
     * Map the tables from raw db values into an usable array.
     *
     * @param  Doctrine\DBAL\Schema\Schema  $rawTables
     * @return array
     */
    private static function mapTables($rawTables)
    {
        return LazyCollection::make($rawTables->getTables())->mapWithKeys(function ($table, $key) {
            return [$table->getName() => $table];
        })->toArray();
    }
}
