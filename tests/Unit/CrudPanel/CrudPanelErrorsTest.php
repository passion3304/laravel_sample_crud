<?php
namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Errors
 */
class CrudPanelErrorsTest extends BaseCrudPanelTest
{
    public function testItCanEnableAndDisableInlineErrors()
    {
        $this->crudPanel->setOperation('create');
        $this->crudPanel->enableInlineErrors();
        $this->assertTrue($this->crudPanel->inlineErrorsEnabled());
        $this->crudPanel->disableInlineErrors();
        $this->assertFalse($this->crudPanel->inlineErrorsEnabled());      
    }

    public function testItCanEnableAndDisableGroupedErrors()
    {
         $this->crudPanel->setOperation('create');
         $this->crudPanel->enableGroupedErrors();
         $this->assertTrue($this->crudPanel->groupedErrorsEnabled());
         $this->crudPanel->disableGroupedErrors();
         $this->assertFalse($this->crudPanel->groupedErrorsEnabled());  
    }
}
