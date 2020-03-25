<?php

namespace Backpack\CRUD\app\Library;

/**
 * Adds fluent syntax to Backpack Widgets.
 */
class Widget
{
	protected $attributes;


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
     // public function __call($method, $parameters)
     // {
     //     $this->setAttributeValue($method, $parameters[0]);

     //      return $this->save();
     // }
}