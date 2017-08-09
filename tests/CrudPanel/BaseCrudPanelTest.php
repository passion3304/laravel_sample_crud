<?php


namespace CrudPanel;


use Backpack\CRUD\CrudPanel;
use Orchestra\Testbench\TestCase;

abstract class BaseCrudPanelTest extends TestCase
{

    /**
     * @var CrudPanel
     */
    protected $crudPanel;

    protected function setUp()
    {
        parent::setUp();

        $this->crudPanel = new CrudPanel();
    }
}