<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

class CrudPanelFieldsTest extends BaseCrudPanelTest
{
    private $oneTextFieldArray = [
        'name' => 'field1',
        'label' => 'Field1',
        'type' => 'text',
    ];

    private $expectedOneTextFieldArray = [
        'field1' => [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
        ],
    ];

    private $twoTextFieldsArray = [
        [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
        ],
        [
            'name' => 'field2',
            'label' => 'Field2',
            'type' => 'text',
        ],
    ];

    private $threeTextFieldsArray = [
        [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
        ],
        [
            'name' => 'field2',
            'label' => 'Field2',
            'type' => 'text',
        ],
        [
            'name' => 'field3',
            'label' => 'Field3',
            'type' => 'text',
        ],
    ];
    private $expectedThreeTextFieldsArray = [
        'field1' => [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
        ],
        'field2' => [
            'name' => 'field2',
            'label' => 'Field2',
            'type' => 'text',
        ],
        'field3' => [
            'name' => 'field3',
            'label' => 'Field3',
            'type' => 'text',
        ],
    ];

    public function testAddFieldByName()
    {
        $this->crudPanel->addField('field1');

        $this->assertEquals(1, count($this->crudPanel->create_fields));
        $this->assertEquals(1, count($this->crudPanel->update_fields));
        $this->assertEquals($this->expectedOneTextFieldArray, $this->crudPanel->create_fields);
        $this->assertEquals($this->expectedOneTextFieldArray, $this->crudPanel->update_fields);
    }

    public function testAddFieldsByName()
    {
        $this->crudPanel->addFields(['field1', 'field2', 'field3']);

        $this->assertEquals(3, count($this->crudPanel->create_fields));
        $this->assertEquals(3, count($this->crudPanel->update_fields));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->create_fields);
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->update_fields);
    }

    public function testAddFieldsAsArray()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->assertEquals(3, count($this->crudPanel->create_fields));
        $this->assertEquals(3, count($this->crudPanel->update_fields));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->create_fields);
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->update_fields);
    }

    public function testAddFieldsDifferentTypes()
    {
        $this->markTestIncomplete();
    }

    public function testAddFieldsForCreateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray, 'create');

        $this->assertEquals(3, count($this->crudPanel->create_fields));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->create_fields);
        $this->assertEmpty($this->crudPanel->update_fields);
    }

    public function testAddFieldsForUpdateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray, 'update');

        $this->assertEquals(3, count($this->crudPanel->update_fields));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->update_fields);
        $this->assertEmpty($this->crudPanel->create_fields);
    }

    public function testAfterField()
    {
        $this->markTestIncomplete();
    }

    public function testAfterUnknownField()
    {
        $this->markTestIncomplete();
    }

    public function testBeforeField()
    {
        $this->markTestIncomplete();
    }

    public function testBeforeUnknownField()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveFields()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveFieldsFromCreateForm()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveFieldsFromUpdateForm()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveUnknownFields()
    {
        $this->markTestIncomplete();
    }

    public function testCheckIfFieldIsFirstOfItsType()
    {
        $this->markTestIncomplete();
    }

    public function testCheckIfUnknownFieldIsFirstOfItsType()
    {
        $this->markTestIncomplete();
    }

    public function testDecodeJsonCastedAttributes()
    {
        $this->markTestIncomplete();
    }
}
