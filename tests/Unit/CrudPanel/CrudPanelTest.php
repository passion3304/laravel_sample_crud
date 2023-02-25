<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\TestModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudPanel
 */
class CrudPanelTest extends BaseCrudPanelTest
{
    public function testSetModelFromModelClass()
    {
        $this->crudPanel->setModel(TestModel::class);

        $this->assertEquals($this->model, $this->crudPanel->model);
        $this->assertInstanceOf(TestModel::class, $this->crudPanel->model);
        $this->assertInstanceOf(Builder::class, $this->crudPanel->query);
    }

    public function testSetModelFromModelClassName()
    {
        $modelClassName = '\Backpack\CRUD\Tests\Unit\Models\TestModel';

        $this->crudPanel->setModel($modelClassName);

        $this->assertEquals($this->model, $this->crudPanel->model);
        $this->assertInstanceOf($modelClassName, $this->crudPanel->model);
        $this->assertInstanceOf(Builder::class, $this->crudPanel->query);
    }

    public function testSetUnknownModel()
    {
        $this->expectException(\Exception::class);

        $this->crudPanel->setModel('\Foo\Bar');
    }

    public function testSetUnknownRouteName()
    {
        $this->expectException(\Exception::class);

        $this->crudPanel->setRouteName('unknown.route.name');
    }

    public function testItThrowsExceptionIfModelIsNotUsingCrudTrait()
    {
        try {
            $this->crudPanel->setModel('\Backpack\CRUD\Tests\Unit\Models\ModelWithoutCrudTrait');
        } catch (\Throwable $e) {
        }
        $this->assertEquals(
            new \Exception('Please use CrudTrait on the model.', 500),
            $e
        );
    }
}
