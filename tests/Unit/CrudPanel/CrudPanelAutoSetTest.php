<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\ColumnType;

class CrudPanelAutoSetTest extends BaseDBCrudPanelTest
{
    private $expectedUnknownFieldType = 'text';

    private $expectedFieldTypeFromColumnType = [
        'bigIntegerCol' => 'text',
        'binaryCol' => 'text',
        'booleanCol' => 'text',
        'charCol' => 'text',
        'dateCol' => 'date',
        'dateTimeCol' => 'datetime',
        'dateTimeTzCol' => 'datetime',
        'decimalCol' => 'text',
        'doubleCol' => 'text',
        'enumCol' => 'text',
        'floatCol' => 'text',
        'integerCol' => 'text',
        'ipAddressCol' => 'text',
        'jsonCol' => 'textarea',
        'jsonbCol' => 'textarea',
        'longTextCol' => 'textarea',
        'macAddressCol' => 'text',
        'mediumIntegerCol' => 'text',
        'mediumTextCol' => 'textarea',
        'smallIntegerCol' => 'text',
        'stringCol' => 'text',
        'textCol' => 'textarea',
        'timeCol' => 'time',
        'timeTzCol' => 'time',
        'tinyIntegerCol' => 'text',
        'timestampCol' => 'datetime',
        'timestampTzCol' => 'datetime',
        'uuidCol' => 'text',
    ];

    private $expectedColumnTypes = [
        'bigIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'binaryCol' => [
            'type' => 'blob',
            'default' => '',
        ],
        'booleanCol' => [
            'type' => 'boolean',
            'default' => '',
        ],
        'charCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'dateCol' => [
            'type' => 'date',
            'default' => '',
        ],
        'dateTimeCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'dateTimeTzCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'decimalCol' => [
            'type' => 'decimal',
            'default' => '',
        ],
        'doubleCol' => [
            'type' => 'float',
            'default' => '',
        ],
        'enumCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'floatCol' => [
            'type' => 'float',
            'default' => '',
        ],
        'integerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'ipAddressCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'jsonCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'jsonbCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'longTextCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'macAddressCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'mediumIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'mediumTextCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'smallIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'stringCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'textCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'timeCol' => [
            'type' => 'time',
            'default' => '',
        ],
        'timeTzCol' => [
            'type' => 'time',
            'default' => '',
        ],
        'tinyIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'timestampCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'timestampTzCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'uuidCol' => [
            'type' => 'string',
            'default' => '',
        ],
    ];

    private $expectedFieldsFromDb = [
        'bigIntegerCol' => [
            'name' => 'bigIntegerCol',
            'label' => 'BigIntegerCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'binaryCol' => [
            'name' => 'binaryCol',
            'label' => 'BinaryCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'booleanCol' => [
            'name' => 'booleanCol',
            'label' => 'BooleanCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'charCol' => [
            'name' => 'charCol',
            'label' => 'CharCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'dateCol' => [
            'name' => 'dateCol',
            'label' => 'DateCol',
            'value' => null,
            'type' => 'date',
            'values' => [],
            'attributes' => [],
        ],
        'dateTimeCol' => [
            'name' => 'dateTimeCol',
            'label' => 'DateTimeCol',
            'value' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
        ],
        'dateTimeTzCol' => [
            'name' => 'dateTimeTzCol',
            'label' => 'DateTimeTzCol',
            'value' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
        ],
        'decimalCol' => [
            'name' => 'decimalCol',
            'label' => 'DecimalCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'doubleCol' => [
            'name' => 'doubleCol',
            'label' => 'DoubleCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'enumCol' => [
            'name' => 'enumCol',
            'label' => 'EnumCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'floatCol' => [
            'name' => 'floatCol',
            'label' => 'FloatCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'integerCol' => [
            'name' => 'integerCol',
            'label' => 'IntegerCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'ipAddressCol' => [
            'name' => 'ipAddressCol',
            'label' => 'IpAddressCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'jsonCol' => [
            'name' => 'jsonCol',
            'label' => 'JsonCol',
            'value' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
        ],
        'jsonbCol' => [
            'name' => 'jsonbCol',
            'label' => 'JsonbCol',
            'value' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
        ],
        'longTextCol' => [
            'name' => 'longTextCol',
            'label' => 'LongTextCol',
            'value' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
        ],
        'macAddressCol' => [
            'name' => 'macAddressCol',
            'label' => 'MacAddressCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'mediumIntegerCol' => [
            'name' => 'mediumIntegerCol',
            'label' => 'MediumIntegerCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'mediumTextCol' => [
            'name' => 'mediumTextCol',
            'label' => 'MediumTextCol',
            'value' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
        ],
        'smallIntegerCol' => [
            'name' => 'smallIntegerCol',
            'label' => 'SmallIntegerCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'stringCol' => [
            'name' => 'stringCol',
            'label' => 'StringCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'textCol' => [
            'name' => 'textCol',
            'label' => 'TextCol',
            'value' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
        ],
        'timeCol' => [
            'name' => 'timeCol',
            'label' => 'TimeCol',
            'value' => null,
            'type' => 'time',
            'values' => [],
            'attributes' => [],
        ],
        'timeTzCol' => [
            'name' => 'timeTzCol',
            'label' => 'TimeTzCol',
            'value' => null,
            'type' => 'time',
            'values' => [],
            'attributes' => [],
        ],
        'tinyIntegerCol' => [
            'name' => 'tinyIntegerCol',
            'label' => 'TinyIntegerCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
        'timestampCol' => [
            'name' => 'timestampCol',
            'label' => 'TimestampCol',
            'value' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
        ],
        'timestampTzCol' => [
            'name' => 'timestampTzCol',
            'label' => 'TimestampTzCol',
            'value' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
        ],
        'uuidCol' => [
            'name' => 'uuidCol',
            'label' => 'UuidCol',
            'value' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
        ],
    ];

    public function testGetFieldTypeFromDbColumnType()
    {
        $this->crudPanel->setModel(ColumnType::class);
        $this->crudPanel->setFromDb();

        $fieldTypesFromColumnType = [];
        foreach ($this->crudPanel->create_fields as $field) {
            $fieldTypesFromColumnType[] = $this->crudPanel->getFieldTypeFromDbColumnType($field['name']);
        }

        $this->assertEquals(array_values($this->expectedFieldTypeFromColumnType), $fieldTypesFromColumnType);
    }

    public function testSetFromDb()
    {
        $this->crudPanel->setModel(ColumnType::class);

        $this->crudPanel->setFromDb();

        $this->assertEquals($this->expectedFieldsFromDb, $this->crudPanel->create_fields);
        $this->assertEquals($this->expectedFieldsFromDb, $this->crudPanel->update_fields);
    }

    public function testGetDbColumnTypes()
    {
        $this->crudPanel->setModel(ColumnType::class);

        $columnTypes = $this->crudPanel->getDbColumnTypes();

        $this->assertEquals($this->expectedColumnTypes, $columnTypes);
    }

    public function testGetFieldTypeFromDbColumnTypeUnknownField()
    {
        $fieldType = $this->crudPanel->getFieldTypeFromDbColumnType('someFieldName1');

        $this->assertEquals($this->expectedUnknownFieldType, $fieldType);
    }

    public function testMakeLabel()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $idLabel = $this->crudPanel->makeLabel('id');
        $snakeCaseFKLabel = $this->crudPanel->makeLabel('id_user');
        $camelCaseFKLabel = $this->crudPanel->makeLabel('idUser');
        $camelCaseFKLabelReversed = $this->crudPanel->makeLabel('userId');
        $dateLabel = $this->crudPanel->makeLabel('created_at');
        $camelCaseLabel = $this->crudPanel->makeLabel('camelCaseLabel');
        $camelCaseRandomLabel = $this->crudPanel->makeLabel('camelCaseLabelRANDOMCase');
        $simpleLabel = $this->crudPanel->makeLabel('label');
        $snakeCaseLabel = $this->crudPanel->makeLabel('snake_case_label');
        $snakeCaseRandomLabel = $this->crudPanel->makeLabel('snake_Case_random_CASE');
        $allCapsLabel = $this->crudPanel->makeLabel('ALLCAPSLABEL');

        // TODO: the id label gets removed. it should not be removed if it is not followed by anything.
        // TODO: improve method documentation to know what to expect.
        $this->assertEquals('Id', $idLabel);
        $this->assertEquals('Id user', $snakeCaseFKLabel);
        $this->assertEquals('IdUser', $camelCaseFKLabel);
        $this->assertEquals('User', $camelCaseFKLabelReversed);
        $this->assertEquals('Created', $dateLabel);
        $this->assertEquals('CamelCaseLabel', $camelCaseLabel);
        $this->assertEquals('CamelCaseLabelRANDOMCase', $camelCaseRandomLabel);
        $this->assertEquals('Label', $simpleLabel);
        $this->assertEquals('Snake case label', $snakeCaseLabel);
        $this->assertEquals('Snake Case random CASE', $snakeCaseRandomLabel);
        $this->assertEquals('ALLCAPSLABEL', $allCapsLabel);
    }

    public function testMakeLabelEmpty()
    {
        $label = $this->crudPanel->makeLabel('');

        $this->assertEmpty($label);
    }

    public function testGetDbColumnsNames()
    {
        $this->crudPanel->setModel(ColumnType::class);

        $columnNames = $this->crudPanel->getDbColumnsNames();

        $this->assertEquals(array_keys($this->expectedColumnTypes), $columnNames);
    }
}
