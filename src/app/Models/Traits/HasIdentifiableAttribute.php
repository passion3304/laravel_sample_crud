<?php

namespace Backpack\CRUD\app\Models\Traits;

trait HasIdentifiableAttribute
{
    public static function getIdentifiableName()
    {
        $model = (new self);
        if (method_exists($model, 'identifiableName')) {
            return $model->identifiableName();
        }

        return static::getIdentifiableNameFromDatabase();
    }

    public static function getIdentifiableNameFromDatabase()
    {
        $instance = new static();

        $conn = self::getConnectionWithExtraTypeMappings($instance);

        $table = config()->get('database.connections.'.$instance->getConnectionName().'.prefix').$instance->getTable();

        $columns = $conn->getDoctrineSchemaManager()->listTableColumns($table);
        $indexes = $conn->getDoctrineSchemaManager()->listTableIndexes($table);

        // this column names are sensible defaults for lots of use cases.
        $sensibleDefaultNames = ['name', 'title', 'description', 'label'];

        $columnsNames = array_keys($columns);

        //we check if any of the sensible defaults exists in columns.
        foreach ($sensibleDefaultNames as $defaultName) {
            if (in_array($defaultName, $columnsNames)) {
                return [$defaultName];
            }
        }

        //get indexed columns in database table
        $indexedColumns = [];
        foreach ($indexes as $index) {
            $indexColumns = $index->getColumns();
            foreach ($indexColumns as $ic) {
                array_push($indexedColumns, $ic);
            }
        }

        //if non of the sensible defaults exists we get the first column from database that is not indexed (usually primary, foreign keys).
        foreach ($columns as $columnName => $columnProperties) {
            if (! in_array($columnName, $indexedColumns)) {

                //check for convention "field<_id>" in case developer didn't add foreign key constraints.
                if (strpos($columnName, '_id') !== false) {
                    continue;
                }

                return [$columnName];
            }
        }
        //in case everything fails we just return the first column in database
        return array_first($columnsNames);
    }
}
