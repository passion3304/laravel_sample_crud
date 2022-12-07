<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\User;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Filters
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudFilter
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

    public function testItCanCheckIfFilterIsActiveFromRequest()
    {
        $this->crudPanel->setModel(User::class);
        $request = request()->create('/admin/users', 'GET', ['my_custom_filter' => 'foo']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('GET', 'admin/users', ['UserCrudController', 'index']))->bind($request);
        });
        $this->crudPanel->setRequest($request);

        $isActive = CrudFilter::name('my_custom_filter')->isActive();
        $this->assertTrue($isActive);
    }
}
