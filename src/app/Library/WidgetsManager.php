<?php

namespace Backpack\CRUD\app\Library;

use Backpack\CRUD\app\Library\Widget;

/**
 * Adds fluent syntax to Backpack Widgets.
 */
class WidgetsManager
{
	protected $widgets;

     /**
      * Create and return a CrudColumn object for that column name.
      *
      * Enables developers to use a fluent syntax to declare their columns,
      * in addition to the existing options:
      * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
      * - CRUD::column('price')->type('number');
      *
      * And if the developer uses the CrudColumn object as Column in his CrudController:
      * - Column::name('price')->type('number');
      *
      * @param  string $name The name of the column in the db, or model attribute.
      * @return CrudColumn
      */
	public static function add($name) {
		return new Widget($this, $name);
	}

     /**
      * Check if a column exists, by any given attribute.
      *
      * @param  string  $attribute   Attribute name on that column definition array.
      * @param  string  $value       Value of that attribute on that column definition array.
      * @return bool
      */
    public function hasColumnWhere($attribute, $value)
    {
        $match = Arr::first($this->columns(), function ($column, $columnKey) use ($attribute, $value) {
            return isset($column[$attribute]) && $column[$attribute] == $value;
        });

        return (bool) $match;
     }

      /**
      * Get the first column where a given attribute has the given value.
      *
      * @param  string  $attribute   Attribute name on that column definition array.
      * @param  string  $value       Value of that attribute on that column definition array.
      * @return bool
      */
    public function firstColumnWhere($attribute, $value)
    {
		return Arr::first($this->columns(), function ($column, $columnKey) use ($attribute, $value) {
			return isset($column[$attribute]) && $column[$attribute] == $value;
		});
    }

}