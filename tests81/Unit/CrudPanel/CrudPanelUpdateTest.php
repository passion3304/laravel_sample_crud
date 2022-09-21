<?php

namespace Backpack\CRUD\Tests81\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\CrudPanel\BaseDBCrudPanelTest;
use Faker\Factory;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Update
 */
class CrudPanelUpdateTest extends BaseDBCrudPanelTest
{
    public function testGetUpdateFieldsWithEnum()
    {
        $this->crudPanel->setModel(\Backpack\CRUD\Tests81\Unit\Models\ArticleWithEnum::class);
        $this->crudPanel->addFields([[
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'content',
        ], [
            'name' => 'tags',
        ], [
            'label'     => 'Author',
            'type'      => 'select',
            'name'      => 'user_id',
            'entity'    => 'user',
            'attribute' => 'name',
        ], [
            'name' => 'status',
        ]]);
        $faker = Factory::create();
        $inputData = [
            'content'     => $faker->text(),
            'tags'        => $faker->words(3, true),
            'user_id'     => 1,
            'metas'       => null,
            'extras'      => null,
            'status'      => 'PUBLISHED',
            'cast_metas'  => null,
            'cast_tags'   => null,
            'cast_extras' => null,
        ];
        $article = $this->crudPanel->create($inputData);

        $updateFields = $this->crudPanel->getUpdateFields(2);

        $this->assertTrue($updateFields['status']['value'] === 'PUBLISHED');
    }
}
