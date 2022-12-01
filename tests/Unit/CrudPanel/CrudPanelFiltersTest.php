<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Filters
 */
class CrudPanelFiltersTest extends BaseCrudPanelTest
{
    protected $testFilter = [[
        'name'  => 'my_filter',
        'label' => 'filter label',
    ], false, false, false];

    public function testItEnablesTheFiltersButConsiderThemDisableIfEmpty()
    {
        $this->crudPanel->enableFilters();
        $this->assertFalse($this->crudPanel->filtersEnabled());
    }

    public function testItCanAddFiltersToCrudPanel()
    {
        $this->crudPanel->addFilter(...$this->testFilter);

        $this->assertCount(1, $this->crudPanel->filters());
    }

    public function testItCanClearFilters()
    {
        $this->crudPanel->addFilter(...$this->testFilter);

        $this->crudPanel->clearFilters();
        $this->assertCount(0, $this->crudPanel->filters());
    }
}
