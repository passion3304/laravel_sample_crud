<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Macroable
 */
class CrudPanelMacroTest extends BaseCrudPanelTest
{
    public function testItCanRegisterMacro()
    {
        $this->crudPanel::macro('validMacro', function () {
            return true;
        });

        $this->assertTrue($this->crudPanel->validMacro());
    }

    public function testThrowsErrorIfMacroExists()
    {
        try {
            $this->crudPanel::macro('setModel', function () {
                return true;
            });
        } catch (\Throwable $e) {
        }
        $this->assertEquals(
            new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Cannot register \'setModel\' macro. \'setModel()\' already exists on Backpack\CRUD\app\Library\CrudPanel\CrudPanel'),
            $e
        );
    }
}
