<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

class CrudPanelFakeFieldsTest extends BaseCrudPanelTest
{
    private $fakeFieldsArray = [
        [
            'name' => 'field',
            'label' => 'Normal Field',
        ],
        [
            'name' => 'meta_title',
            'label' => 'Meta Title',
            'fake' => true,
            'store_in' => 'metas',
        ],
        [
            'name' => 'meta_description',
            'label' => 'Meta Description',
            'fake' => true,
            'store_in' => 'metas',
        ],
        [
            'name' => 'meta_keywords',
            'label' => 'Meta Keywords',
            'fake' => true,
            'store_in' => 'metas',
        ],
        [
            'name' => 'tags',
            'label' => 'Tags',
            'fake' => true,
            'store_in' => 'tagsz',
        ],
        [
            'name' => 'extra_details',
            'label' => 'Extra Details',
            'fake' => true,
        ],
    ];

    private $noFakeFieldsInputData = [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
    ];

    private $fakeFieldsInputData = [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
        'meta_title' => 'Meta Title Value',
        'meta_description' => 'Meta Description Value',
        'tags' => ['tag1', 'tag2', 'tag3'],
        'extra_details' => ['detail1', 'detail2', 'detail3'],
    ];

    public function testCompactFakeFieldsFromCreateForm()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->addFields($this->fakeFieldsArray);

        $compactedFakeFields = $this->crudPanel->compactFakeFields($this->fakeFieldsInputData);

        // TODO: check error when fake name is same as database table name
    }

    public function testCompactFakeFieldsFromUpdateForm()
    {
        $this->markTestIncomplete();
    }

    public function testCompactFakeFieldsFromUpdateFormWithId()
    {
        $this->markTestIncomplete();
    }

    public function testCompactFakeFieldsFromUpdateFormWithUnknownId()
    {
        $this->markTestIncomplete();
    }

    public function testCompactFakeFieldsFromEmptyRequest()
    {
        $compactedFakeFields = $this->crudPanel->compactFakeFields([]);

        $this->assertEmpty($compactedFakeFields);
    }

    public function testCompactFakeFieldsFromRequestWithNoFakes()
    {
        $compactedFakeFields = $this->crudPanel->compactFakeFields($this->noFakeFieldsInputData);

        $this->assertEquals($this->noFakeFieldsInputData, $compactedFakeFields);
    }

    public function testCompactFakeFieldsFromUnknownForm()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->setExpectedException(\InvalidArgumentException::class);

        // TODO: this should throw an invalid argument exception but doesn't because of the getFields method in the
        //       read trait, which returns the create fields in case of an unknown form type.
        $this->crudPanel->getFakeColumnsAsArray('unknownForm');
    }
}
