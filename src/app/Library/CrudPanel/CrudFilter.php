<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

class CrudFilter
{
    public $name; // the name of the filtered variable (db column name)
    public $type = 'select'; // the name of the filter view that will be loaded
    public $label;
    public $placeholder;
    public $values;
    public $options;
    public $logic;
    public $fallbackLogic;
    public $currentValue;
    public $view;
    public $viewNamespace = 'crud::filters';

    public function __construct($options, $values, $logic, $fallbackLogic)
    {
        $this->checkOptionsIntegrity($options);

        $this->name = $options['name'];
        $this->type = $options['type'];
        $this->label = $options['label'];
        $this->viewNamespace = $options['view_namespace'] ?? $this->viewNamespace;
        $this->view = $this->viewNamespace.'.'.$this->type;
        $this->placeholder = $options['placeholder'] ?? '';

        $this->values = $values;
        $this->options = $options;
        $this->logic = $logic;
        $this->fallbackLogic = $fallbackLogic;

        if (\Request::has($this->name)) {
            $this->currentValue = \Request::input($this->name);
        }
    }

    public function checkOptionsIntegrity($options)
    {
        if (! isset($options['name'])) {
            abort(500, 'Please make sure all your filters have names.');
        }
        if (! isset($options['type'])) {
            abort(500, 'Please make sure all your filters have types.');
        }
        if (! \View::exists('crud::filters.'.$options['type'])) {
            abort(500, 'No filter view named "'.$options['type'].'.blade.php" was found.');
        }
        if (! isset($options['label'])) {
            abort(500, 'Please make sure all your filters have labels.');
        }
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if (\Request::has($this->name)) {
            return true;
        }

        return false;
    }
}