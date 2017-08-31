<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Illuminate\Database\Eloquent\Builder;

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
        $this->crudPanel->setModel('\Backpack\CRUD\Tests\Unit\CrudPanel\TestModel');

        $this->assertEquals($this->model, $this->crudPanel->model);
        $this->assertInstanceOf('\Backpack\CRUD\Tests\Unit\CrudPanel\TestModel', $this->crudPanel->model);
        $this->assertInstanceOf(Builder::class, $this->crudPanel->query);
    }

    public function testSetUnknownModel()
    {
        $this->setExpectedException(\Exception::class);

        $this->crudPanel->setModel('\Foo\Bar');
    }

    public function testSetRouteName()
    {
        // TODO: check if we need an app instance to test this out
        $this->markTestIncomplete();
    }

    public function testSetUnknownRouteName()
    {
        $this->setExpectedException(\Exception::class);

        $this->crudPanel->setRouteName('unknown.route.name');
    }

    public function testSync()
    {
        // TODO: find out what sync method does
        $this->markTestIncomplete();
    }

    public function testSort()
    {
        // TODO: find out what sort method does
        $this->markTestIncomplete();
    }
}
