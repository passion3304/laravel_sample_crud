<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Illuminate\Support\Facades\Facade;

/**
 * This object allows developers to use CRUD::addField() instead of $this->crud->addField(),
 * by providing a Facade that leads to the CrudPanel object. That object is stored in Laravel's
 * service container as 'crud'.
 */
/**
 * @method static setModel($model)
 * @method static setRoute(string $route)
 * @method static setEntityNameStrings(string $singular, string $plural)
 *
 * @method static field(string $name)
 * @method static addField(array $field)
 * @method static addFields(array $fields)
 *
 * @method static column(string $name)
 * @method static addColumn(array $column)
 * @method static addColumns(array $columns)
 * @method static afterColumn(string $targetColumn)
 *
 * @method static setValidation($class)
 */
class CrudPanelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crud';
    }
}
