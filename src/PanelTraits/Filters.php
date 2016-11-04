<?php

namespace Backpack\CRUD\PanelTraits;

use Illuminate\Support\Collection;

trait Filters
{
    // ------------
    // FILTERS
    // ------------

    public $filters = [];

    public function __construct()
    {
        $this->filters = new FiltersCollection;
    }

    // TODO: $this->crud->reorderFilters('stack_name', ['one', 'two']);

    /**
     * Add a filter to the CRUD table view.
     *
     * @param array         $options        Name, type, label, stack, etc.
     * @param array/closure $values         The HTML for the filter.
     * @param closure       $filter_logic   Query modification (filtering) logic.
     */
    public function addFilter($options, $values = false, $filter_logic = false)
    {
        $this->filters->push(new CrudFilter($options, $values, $filter_logic));
    }

    public function filters()
    {
        return $this->filters;
    }

    // TODO
    public function removeFilter($name)
    {
        // $this->filters = $this->filters->reject(function ($filter) use ($name) {
        //     return $filter->name == $name;
        // });
    }

    // TODO
    public function removeAllFilters()
    {
        $this->filters = collect([]);
    }

    // TODO
    public function removeAllFiltersFromStack($stack)
    {
        $this->filters = $this->filters->reject(function ($filter) use ($stack) {
            return $filter->stack == $stack;
        });
    }

    // TODO
    public function removeFilterFromStack($name, $stack)
    {
        $this->filters = $this->filters->reject(function ($filter) use ($name, $stack) {
            return $filter->name == $name && $filter->stack == $stack;
        });
    }
}

class FiltersCollection extends \Illuminate\Support\Collection
{
    public function stackItems($stack) {}
    public function stackCount($stack) {
        dd($this);
    }
}

class CrudFilter
{
    public $stack; // stacks: top, right, bottom, left
    public $name; // the name of the filtered variable (db column name)
    public $type = 'select'; // the name of the filter view that will be loaded
    public $view;

    public function __construct($options, $values, $filter_logic)
    {
        $this->stack = isset($options['stack'])?$options['stack']:'top';

        if (!isset($options['name'])) {
            abort(500, "Please make sure all your filters have names.");
        }
        $this->name = $options['name'];

        if (!isset($options['name'])) {
            abort(500, "Please make sure all your filters have types.");
        }
        $this->type = $options['type'];

        $this->view = 'crud::filters.'.$this->type;
    }
}