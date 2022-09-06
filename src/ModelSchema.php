<?php

namespace Backpack\CRUD;

class ModelSchema extends TableSchema
{
    public function __construct($model)
    {
        [$connection, $table] = $model::getConnectionAndTable();
        $this->schema = DatabaseSchema::getForTable($connection->getName(), $table);
    }
}
