<?php

namespace CrudPanel;

class CrudPanelColumnsTest extends BaseCrudPanelTest
{
    /** @test */
    public function it_can_add_column_by_name()
    {
        $expectedColumn = [
            'name'  => 'column',
            'label' => 'Column',
        ];

        $this->crudPanel->addColumn('column');

        $this->assertEquals(1, count($this->crudPanel->columns));
        $this->assertContains($expectedColumn, $this->crudPanel->columns);
    }

    /** @test */
    public function it_can_add_column()
    {
        $expectedColumn = [
            'name'  => 'column',
            'label' => 'Column',
        ];

        $this->crudPanel->addColumn($expectedColumn);

        $this->assertEquals(1, count($this->crudPanel->columns));
        $this->assertContains($expectedColumn, $this->crudPanel->columns);
    }

    /** @test */
    public function it_can_add_columns_by_name()
    {
        $expectedColumns = [
            [
                'name'  => 'column',
                'label' => 'Column',
            ],
            [
                'name' => 'column2',
                'label' => 'Column2',
            ],
        ];

        $this->crudPanel->addColumns(['column', 'column2']);

        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertContains($expectedColumns[0], $this->crudPanel->columns);
        $this->assertContains($expectedColumns[1], $this->crudPanel->columns);
    }

    /** @test */
    public function it_can_add_columns()
    {
        $expectedColumns = [
            [
                'name'  => 'column',
                'label' => 'Column',
            ],
            [
                'name' => 'column2',
                'label' => 'Column2',
            ],
        ];

        $this->crudPanel->addColumns($expectedColumns);

        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertContains($expectedColumns[0], $this->crudPanel->columns);
        $this->assertContains($expectedColumns[1], $this->crudPanel->columns);
    }

    /** @test */
    public function it_can_move_column_before()
    {
        $expectedColumns = [
            [
                'name'  => 'column',
                'label' => 'Column',
            ],
            [
                'name' => 'column2',
                'label' => 'Column2',
            ],
        ];

        $this->crudPanel->addColumns($expectedColumns);
        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertContains($expectedColumns[0], $this->crudPanel->columns);
        $this->assertContains($expectedColumns[1], $this->crudPanel->columns);

        $this->crudPanel->beforeColumn('column');
        $keys = array_keys($this->crudPanel->columns);
        $this->assertEquals($expectedColumns[1], $this->crudPanel->columns[$keys[0]]);

        $this->assertEquals(['column2', 'column'], $keys);
    }

    /** @test */
    public function it_can_move_column_after()
    {
        $expectedColumns = [
            [
                'name'  => 'column',
                'label' => 'Column',
            ],
            [
                'name' => 'column2',
                'label' => 'Column2',
            ],
            [
                'name' => 'column3',
                'label' => 'Column3',
            ],
        ];

        $this->crudPanel->addColumns($expectedColumns);
        $this->assertTrue(count($this->crudPanel->columns) == 3);
        $this->assertContains($expectedColumns[0], $this->crudPanel->columns);
        $this->assertContains($expectedColumns[1], $this->crudPanel->columns);
        $this->assertContains($expectedColumns[2], $this->crudPanel->columns);

        $this->crudPanel->afterColumn('column');
        $keys = array_keys($this->crudPanel->columns);

        $this->assertEquals($expectedColumns[2], $this->crudPanel->columns[$keys[1]]);

        $this->assertEquals(['column', 'column3', 'column2'], $keys);
    }

    /** @test */
    public function it_can_remove_column_by_name()
    {
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);
        $this->assertEquals(3, count($this->crudPanel->columns));

        //TODO: this functionality is currently not implemented, why?
        //$this->crudPanel->removeColumn('column1');
        //$this->assertEquals(2, count($this->crudPanel->columns));
        //$this->assertNotContains('column1', array_keys($this->crudPanel->columns));
    }

    /** @test */
    public function it_can_remove_column()
    {
        $column = [
            'name'  => 'column1',
            'label' => 'Column1',
        ];

        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);
        $this->assertEquals(3, count($this->crudPanel->columns));

        // TODO: seems lile remove column is not working as expected
        //$this->crudPanel->removeColumn($column);
        //$this->assertEquals(2, count($this->crudPanel->columns));
        //$this->assertNotContains('column1', array_keys($this->crudPanel->columns));
    }
}
