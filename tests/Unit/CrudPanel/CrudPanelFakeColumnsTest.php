<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

class CrudPanelFakeColumnsTest extends BaseCrudPanelTest
{
    private $emptyFakeColumnsArray = ['extras'];

    private $fakeFieldsArray = [
        [
            'name' => 'field',
            'label' => 'Normal Field',
        ],
        [
            'name' => 'meta_title',
            'label' => "Meta Title",
            'fake' => true,
            'store_in' => 'metas'
        ],
        [
            'name' => 'meta_description',
            'label' => "Meta Description",
            'fake' => true,
            'store_in' => 'metas'
        ],
        [
            'name' => 'meta_keywords',
            'label' => "Meta Keywords",
            'fake' => true,
            'store_in' => 'metas'
        ],
        [
            'name' => 'tags',
            'label' => "Tags",
            'fake' => true,
            'store_in' => 'tags'
        ],
        [
            'name' => 'extra_details',
            'label' => "Extra Details",
            'fake' => true,
        ]
    ];

    private $expectedFakeFieldsColumnNames = ['metas', 'tags', 'extras'];

    public function testGetFakeColumnsAsArrayFromCreateForm()
    {
        $this->markTestIncomplete("Fails because of DB connection");

        $this->crudPanel->addFields($this->fakeFieldsArray);

        $createFakeColumnsArray = $this->crudPanel->getFakeColumnsAsArray();
        $updateFakeColumnsArray = $this->crudPanel->getFakeColumnsAsArray('update');

        $this->assertEquals($this->expectedFakeFieldsColumnNames, $createFakeColumnsArray);
        $this->assertEquals($this->emptyFakeColumnsArray, $updateFakeColumnsArray);
    }

    public function testGetFakeColumnsAsArrayFromUpdateForm()
    {
        $this->markTestIncomplete();

        $this->crudPanel->addFields($this->fakeFieldsArray, 'update');

        $createFakeColumnsArray = $this->crudPanel->getFakeColumnsAsArray();
        $updateFakeColumnsArray = $this->crudPanel->getFakeColumnsAsArray('update');

        $this->assertEquals($this->emptyFakeColumnsArray, $createFakeColumnsArray);
        $this->assertEquals($this->expectedFakeFieldsColumnNames, $updateFakeColumnsArray);
    }

    public function testGetFakeColumnsAsArrayEmpty()
    {
        $fakeColumnsArray = $this->crudPanel->getFakeColumnsAsArray();

        $this->assertEquals($this->emptyFakeColumnsArray, $fakeColumnsArray);
    }

    public function testGetFakeColumnsAsArrayFromUnknownForm()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->setExpectedException(\InvalidArgumentException::class);

        // TODO: this should throw an invalid argument exception but doesn't because of the getFields method in the
        //       read trait, which returns the create fields in case of an unknown form type.
        //       also, the getFields method should probably be renamed, as it also populates the update fields values
        //       from the database
        $this->crudPanel->getFakeColumnsAsArray('unknownForm');
    }
}
