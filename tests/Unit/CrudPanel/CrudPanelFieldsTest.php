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
        ],
    ];

    private $expectedTwoTextFieldsArray = [
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
        ],
        [
            'name' => 'field3',
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

    public function testBeforeField()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->addFields($this->threeTextFieldsArray);

        // TODO: fix the before field method not preserving field keys
        $this->crudPanel->beforeField('field1');

        $createKeys = array_keys($this->crudPanel->create_fields);
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->create_fields[$createKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $createKeys);

        $updateKeys = array_keys($this->crudPanel->update_fields);
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->update_fields[$updateKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $updateKeys);
    }

    public function testBeforeFieldCreateForm()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->addFields($this->threeTextFieldsArray, 'create');

        // TODO: fix the before field method not preserving field keys
        $this->crudPanel->beforeField('field1');

        $createKeys = array_keys($this->crudPanel->create_fields);
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->create_fields[$createKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $createKeys);

        $this->assertEmpty($this->crudPanel->update_fields);
    }

    public function testBeforeFieldUpdateForm()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->addFields($this->threeTextFieldsArray, 'update');

        // TODO: fix the before field method not preserving field keys
        $this->crudPanel->beforeField('field1');

        $updateKeys = array_keys($this->crudPanel->update_fields);
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->update_fields[$updateKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $updateKeys);

        $this->assertEmpty($this->crudPanel->create_fields);
    }

    public function testBeforeFieldForDifferentFieldsInCreateAndUpdate()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->addFields($this->threeTextFieldsArray, 'create');
        $this->crudPanel->addFields($this->twoTextFieldsArray, 'update');

        // TODO: fix the before field method not preserving field keys
        // TODO: in the case of the create form, this will move the 'field3' field before the 'field1' field, but for
        //       the update form, it will move the 'field2' field before the 'field1' field. should it work like this?
        $this->crudPanel->beforeField('field1');

        $createKeys = array_keys($this->crudPanel->create_fields);
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->create_fields[$createKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $createKeys);

        $updateKeys = array_keys($this->crudPanel->update_fields);
        $this->assertEquals($this->expectedTwoTextFieldsArray['field2'], $this->crudPanel->update_fields[$updateKeys[0]]);
        $this->assertEquals(['field2', 'field1'], $updateKeys);
    }

    public function testBeforeUnknownField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->beforeField('field4');

        $this->assertEquals(array_keys($this->expectedThreeTextFieldsArray), array_keys($this->crudPanel->create_fields));
        $this->assertEquals(array_keys($this->expectedThreeTextFieldsArray), array_keys($this->crudPanel->update_fields));
    }

    public function testAfterField()
    {
        $this->markTestIncomplete();
    }

    public function testAfterUnknownField()
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

        // TODO: the decode JSON method should not be in fields trait and should not be exposed in the public API.
    }
}
