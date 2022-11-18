<?php
namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Autofocus
 */
class CrudPanelAutofocusTest extends BaseCrudPanelTest
{
     public function testItCanEnableAndDisableAutofocus()
     {
          $this->crudPanel->setOperation('create');
          $this->crudPanel->enableAutoFocus();
          $this->assertTrue($this->crudPanel->getAutoFocusOnFirstField());
          $this->crudPanel->disableAutofocus();
          $this->assertFalse($this->crudPanel->getAutoFocusOnFirstField());
     }
}
