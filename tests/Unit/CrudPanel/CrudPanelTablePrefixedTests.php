<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\User;

class CrudPanelTablePrefixedTests extends BasePrefixedDBCrudPanelTest
{
    public function testGetColumnTypeFromColumnNameWithPrefixedDatabase() {
        $model = new User();

        $this->assertEquals('string', $model->getColumnType('name'));
    }

    public function testGetTableNamePrefixed() {
        $model = new User();
        $this->assertEquals('test_users', $model->getTableWithPrefix());
        $this->assertEquals('users', $model->getTable());
    }
}
