<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\CrudPanel;
use Backpack\CRUD\Tests\BaseTest;

abstract class BaseCrudPanelTest extends BaseTest
{
    /**
     * @var CrudPanel
     */
    protected $crudPanel;

    protected $model;

    protected function setUp()
    {
        parent::setUp();

        $this->crudPanel = new CrudPanel();
        $this->crudPanel->setModel(TestModel::class);
        $this->model = $this->crudPanel->getModel();
    }
}
