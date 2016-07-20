<?php

namespace Backpack\CRUD\PanelTraits;

trait Buttons
{
    // ------------
    // BUTTONS
    // ------------

    // TODO: $this->crud->reorderButtons('stack_name', ['one', 'two']);


    public function addButton($stack, $name, $type, $content)
    {
        $this->buttons->push(new CrudButton($stack, $name, $type, $content));
    }

    public function addButtonFromModelFunction($stack, $name, $model_function_name)
    {
        $this->buttons->push(new CrudButton($stack, $name, 'model_function', $model_function_name));
    }

    public function addButtonFromView($stack, $name, $view)
    {
        $this->buttons->push(new CrudButton($stack, $name, 'view', $view));
    }

    public function buttons()
    {
        return $this->buttons;
    }

    public function initButtons()
    {
        $this->buttons = collect();

        // line stack
        $this->buttons->push(new CrudButton('line', 'preview', 'view', 'crud::buttons.preview'));
        $this->buttons->push(new CrudButton('line', 'update', 'view', 'crud::buttons.update'));
        $this->buttons->push(new CrudButton('line', 'delete', 'view', 'crud::buttons.delete'));

        // top stack
        $this->buttons->push(new CrudButton('top', 'create', 'view', 'crud::buttons.create'));
        $this->buttons->push(new CrudButton('top', 'reorder', 'view', 'crud::buttons.reorder'));
    }

    public function removeButton($name)
    {
        $this->buttons->reject(function ($button) {
            return $button->name==$name;
        });
    }

    public function removeButtonFromStack($name, $stack)
    {
        $this->buttons->reject(function ($button) {
            return ($button->name==$name && $button->stack==$stack);
        });
    }
}

class CrudButton {
    public $stack;
    public $name;
    public $type = 'view';
    public $content;

    public function __construct($stack, $name, $type, $content) {
        $this->stack = $stack;
        $this->name = $name;
        $this->type = $type;
        $this->content = $content;
    }
}
