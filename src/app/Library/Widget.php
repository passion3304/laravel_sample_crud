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

    public static function add($attributes = null)
    {
        // use the widgets collection object from the Laravel Service Container
        $collection = app()->make('widgets');

        // make sure the widget has a name
        $attributes = is_string($attributes) ? ['name' => $attributes] : $attributes;
        $attributes['name'] = $attributes['name'] ?? 'widget_'.rand(1, 999999999);

        $existingItem = $collection->firstWhere('name', $attributes['name']);

        if ($existingItem) {
            $attributes = array_merge($existingItem->attributes, $attributes);
        }

        $attributes['group'] = $attributes['group'] ?? 'before_content';
        $attributes['type'] = $attributes['type'] ?? 'card';

        return new static($attributes, $collection);
    }

    // Aliases of add()
    public static function name($name = null)
    {
        return static::add($name);
    }

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
     * If a developer calls a method that doesn't exist, assume they want:
     * - the Widget array to have an attribute with that value;
     * - that field be updated inside the global Widgets object;.
     *
     * Eg: class('something') will set the "class" attribute to "something"
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return Widgets
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this->save();
    }
}
