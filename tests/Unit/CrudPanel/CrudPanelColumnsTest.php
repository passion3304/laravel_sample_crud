<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

class CrudPanelColumnsTest extends BaseCrudPanelTest
{
    private $oneColumnArray = [
        'name' => 'column1',
        'label' => 'Column1',
    ];

    private $otherOneColumnArray = [
        'name' => 'column4',
        'label' => 'Column4',
    ];

    private $twoColumnsArray = [
        [
            'name' => 'column1',
            'label' => 'Column1',
        ],
        [
            'name' => 'column2',
            'label' => 'Column2',
        ],
    ];

    private $expectedTwoColumnsArray = [
        'column1' => [
            'name' => 'column1',
            'label' => 'Column1',
        ],
        'column2' => [
            'name' => 'column2',
            'label' => 'Column2',
        ],
    ];

    private $threeColumnsArray = [
        [
            'name' => 'column1',
            'label' => 'Column1',
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

    private $expectedThreeColumnsArray = [
        'column1' => [
            'name' => 'column1',
            'label' => 'Column1',
        ],
        'column2' => [
            'name' => 'column2',
            'label' => 'Column2',
        ],
        'column3' => [
            'name' => 'column3',
            'label' => 'Column3',
        ],
    ];

    public function testAddColumnByName()
    {
        $this->crudPanel->addColumn('column1');

        $this->assertEquals(1, count($this->crudPanel->columns));
        $this->assertContains($this->oneColumnArray, $this->crudPanel->columns);
    }

    public function testAddColumnsByName()
    {
        $this->crudPanel->addColumns(['column1', 'column2']);

        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertEquals($this->expectedTwoColumnsArray, $this->crudPanel->columns);
    }

    public function testAddColumnAsArray()
    {
        $this->crudPanel->addColumn($this->oneColumnArray);

        $this->assertEquals(1, count($this->crudPanel->columns));
        $this->assertContains($this->oneColumnArray, $this->crudPanel->columns);
    }

    public function testAddColumnsAsArray()
    {
        $this->crudPanel->addColumns($this->twoColumnsArray);

        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertEquals($this->expectedTwoColumnsArray, $this->crudPanel->columns);
    }

    public function testAddColumnNotArray()
    {
        $this->setExpectedException(\ErrorException::class);

        $this->crudPanel->addColumns('column1');
    }

    public function testMoveColumnBefore()
    {
        $this->crudPanel->addColumns($this->twoColumnsArray);

        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertEquals($this->expectedTwoColumnsArray, $this->crudPanel->columns);

        $this->crudPanel->beforeColumn('column1');
        $keys = array_keys($this->crudPanel->columns);
        $this->assertEquals($this->expectedTwoColumnsArray['column2'], $this->crudPanel->columns[$keys[0]]);

        $this->assertEquals(['column2', 'column1'], $keys);
    }

    public function testMoveColumnBeforeUnknownColumnName()
    {
        $this->markTestIncomplete();
    }

    public function testMoveColumnAfter()
    {
        $this->crudPanel->addColumns($this->threeColumnsArray);
        $this->assertEquals(3, count($this->crudPanel->columns));
        $this->assertEquals($this->expectedThreeColumnsArray, $this->crudPanel->columns);

        $this->crudPanel->afterColumn('column1');
        $keys = array_keys($this->crudPanel->columns);

        $this->assertEquals($this->expectedThreeColumnsArray['column3'], $this->crudPanel->columns[$keys[1]]);

        $this->assertEquals(['column1', 'column3', 'column2'], $keys);
    }

    public function testMoveColumnAfterUnknownColumnName()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveColumnByName()
    {
        $this->markTestIncomplete('Not correctly implemented');

        //TODO: fix the remove column functionality
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);
        $this->crudPanel->removeColumn('column1');

        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertNotContains('column1', $this->crudPanel->columns);
        $this->assertNotContains($this->oneColumnArray, $this->crudPanel->columns);
    }

    public function testRemoveUnknownColumnName()
    {
        $this->markTestIncomplete('Not correctly implemented');

        // TODO: fix the remove column functionality
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);

        // TODO: should this fail with an exception or just log as warning?
        $this->crudPanel->removeColumn('column4');

        $this->assertEquals(3, count($this->crudPanel->columns));
        $this->assertNotContains('column4', $this->crudPanel->columns);
        $this->assertNotContains($this->otherOneColumnArray, $this->crudPanel->columns);
    }

    public function testRemoveColumns()
    {
        $this->markTestIncomplete('Not correctly implemented');

        // TODO: fix the remove column functionality
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);
        $this->crudPanel->removeColumns($this->twoColumnArray);

        $this->assertEquals(2, count($this->crudPanel->columns));
        $this->assertNotContains(['column1', 'column2'], $this->crudPanel->columns);
        $this->assertNotEquals($this->expectedThreeColumnsArray, $this->crudPanel->columns);
    }

    public function testRemoveUnknownColumns()
    {
        $this->markTestIncomplete('Not correctly implemented');

        // TODO: fix the remove column functionality
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);

        // TODO: should this fail with an exception or just log as warning?
        $this->crudPanel->removeColumn($this->otherOneColumnArray);

        $this->assertEquals(3, count($this->crudPanel->columns));
        $this->assertNotContains('column4', $this->crudPanel->columns);
        $this->assertNotContains($this->otherOneColumnArray, $this->crudPanel->columns);
    }
}
