<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

/**
 * Adds fluent syntax to Backpack CRUD Columns.
 *
 * In addition to the existing:
 * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
 *
 * Developers can also do:
 * - CRUD::column('price')->type('number');
 *
 * And if the developer uses CrudColumn as Column in their CrudController:
 * - Column::name('price')->type('number');
 */
class CrudColumn
{
    protected $crud;
    protected $attributes;

    public function __construct(CrudPanel $crud, $name)
    {
        $this->crud = $crud;

        $column = $this->crud->firstColumnWhere('name', $name);

        // if column exists
        if ((bool) $column) {
            // use all existing attributes
            $this->setAllAttributeValues($column);
        } else {
            // it means we're creating the column now,
            // so at the very least set the name attribute
            $this->setAttributeValue('name', $name);
        }

        return $this->save();
    }

    /**
     * Create a CrudColumn object with the parameter as its name.
     *
     * @param  string $name Name of the column in the db, or model attribute.
     * @return CrudPanel
     */
    public static function name($name)
    {
        return new static(app()->make('crud'), $name);
    }

    /**
     * Set the value for a certain attribute on the CrudColumn object.
     *
     * @param string $attribute Name of the attribute.
     * @param string $value     Value of that attribute.
     */
    private function setAttributeValue($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Replace all column attributes on the CrudColumn object
     * with the given array of attribute-value pairs.
     *
     * @param array $array Array of attributes and their values.
     */
    private function setAllAttributeValues($array)
    {
        $this->attributes = $array;
    }

    /**
     * Update the global CrudPanel object with the current column attributes.
     *
     * @return CrudColumn
     */
    private function save()
    {
        $key = $this->attributes['key'] ?? $this->attributes['name'];

        if ($this->crud->hasColumnWhere('key', $key)) {
            $this->crud->setColumnDetails($key, $this->attributes);
        } else {
            $this->crud->addColumn($this->attributes);
        }

        return $this;
    }

    /**
     * If a developer calls a method that doesn't exist, assume they want:
     * - the CrudColumn object to have an attribute with that value;
     * - that column be updated inside the global CrudPanel object;.
     *
     * Eg: type('number') will set the "type" attribute to "number"
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return CrudColumn
     */
    private function __call($method, $parameters)
    {
        $this->setAttributeValue($method, $parameters[0]);

        return $this->save();
    }
}
