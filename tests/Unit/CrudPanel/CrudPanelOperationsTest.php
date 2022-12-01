<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Operations
 */
class CrudPanelOperationsTest extends BaseCrudPanelTest
{
    public function testItCanSetAndGetTheCurrentOperation()
    {
        $this->crudPanel->setOperation('create');
        $operation = $this->crudPanel->getOperation();
        $this->assertEquals('create', $operation);
    }

    public function testItCanConfigureOperations()
    {
        $this->crudPanel->operation(['create', 'update'], function () {
            $this->crudPanel->addField(['name' => 'test', 'type' => 'text']);
        });
        $this->crudPanel->applyConfigurationFromSettings('create');

        $this->assertEquals(count($this->crudPanel->fields()), 1);
    }
}
