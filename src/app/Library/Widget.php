<?php

namespace Backpack\CRUD\app\Library;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

/**
 * Adds fluent syntax to Backpack Widgets.
 */
class Widget extends Fluent
{
    protected $collection;
    protected $attributes = [];

    public function __construct($attributes, Collection $collection)
    {
        $this->attributes = $attributes;
        $this->collection = $collection;

        $this->save();
    }

    /**
     * Add a new widget to the widgets collection in the Laravel Service Container.
     * If a widget with the same name exists, it will update the attributes of that one
     * instead of creating a new one.
     *
     * @param string|array $attributes Either the name of the widget, or an array with the attributes the new widget should hold, including the name attribute.
     *
     * @return Widget
     */
    public static function add($attributes = null)
    {
        // use the widgets collection object from the Laravel Service Container
        $collection = app()->make('widgets');

        // make sure the widget has a name
        $attributes = is_string($attributes) ? ['name' => $attributes] : $attributes;
        $attributes['name'] = $attributes['name'] ?? 'widget_'.rand(1, 999999999);

        // if that widget name already exists in the widgets collection
        // then pick up all widget attributes from that entry
        // and overwrite them with the ones passed in $attributes
        if ($existingItem = $collection->firstWhere('name', $attributes['name'])) {
            $attributes = array_merge($existingItem->attributes, $attributes);
        }

        // set defaults for other mandatory attributes
        $attributes['group'] = $attributes['group'] ?? 'before_content';
        $attributes['type'] = $attributes['type'] ?? 'card';

        return new static($attributes, $collection);
    }

    // Alias of add()
    public static function name($name = null)
    {
        return static::add($name);
    }

    // Alias of add()
    public static function make($name = null)
    {
        return static::add($name);
    }

    /**
     * Update the global CrudPanel object with the current widget attributes.
     *
     * @return Widget
     */
    private function save()
    {
        $itemExists = $this->collection->contains('name', $this->attributes['name']);

        if (! $itemExists) {
            $this->collection->put($this->attributes['name'], $this);
        } else {
            $this->collection[$this->name] = $this;
        }

        return $this;
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * Any call to a non-existing method on this class will be assumed to be
     * an attribute that the developer wants to add to that particular widget.
     *
     * Eg: class('something') will set the "class" attribute to "something"
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return Widget
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this->save();
    }
}
