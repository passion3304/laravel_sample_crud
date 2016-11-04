<?php

namespace Backpack\CRUD\PanelTraits;

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
        // if a closure was passed as "values"
        if (is_callable($values)) {
            // get its results
            $values = $values();
        }

        // add a new filter to the interface
        $filter = new CrudFilter($options, $values, $filter_logic);
        $this->filters->push($filter);

        // if a closure was passed as "filter_logic"
        if (is_callable($filter_logic)) {
            // apply it
            $filter_logic();
        } else {
            $this->addDefaultFilterLogic($filter->name, $filter_logic);
        }
    }

    public function addDefaultFilterLogic($name, $operator)
    {
        $input = \Request::all();

        // if this filter is active (the URL has it as a GET parameter)
        if (isset($input[$name])) {
            switch ($operator) {
                // if no operator was passed, just use the equals operator
                case false:
                    $this->addClause('where', $name, $input[$name]);
                    break;

                case 'scope':
                    $this->addClause($operator);
                    break;

                // TODO:
                // whereBetween
                // whereNotBetween
                // whereIn
                // whereNotIn
                // whereNull
                // whereNotNull
                // whereDate
                // whereMonth
                // whereDay
                // whereYear
                // whereColumn
                // like

                // sql comparison operators
                case '=':
                case '<=>':
                case '<>':
                case '!=':
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $this->addClause('where', $name, $operator, $input[$name]);
                    break;

                default:
                    abort(500, 'Unknown filter operator.');
                    break;
            }
        }
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
    public function stackItems($stack)
    {
    }

    public function stackCount($stack)
    {
        dd($this);
    }
}

class CrudFilter
{
    public $stack; // stacks: top, right, bottom, left
    public $name; // the name of the filtered variable (db column name)
    public $type = 'select'; // the name of the filter view that will be loaded
    public $values;
    public $currentValue;
    public $view;

    public function __construct($options, $values, $filter_logic)
    {
        $this->stack = isset($options['stack']) ? $options['stack'] : 'top';

        if (! isset($options['name'])) {
            abort(500, 'Please make sure all your filters have names.');
        }
        $this->name = $options['name'];

        if (! isset($options['name'])) {
            abort(500, 'Please make sure all your filters have types.');
        }
        $this->type = $options['type'];

        $this->values = $values;
        $this->view = 'crud::filters.'.$this->type;

        if (\Request::input($this->name)) {
            $this->currentValue = \Request::input($this->name);
        }
    }

    public function isActive()
    {
        if (\Request::input($this->name)) {
            return true;
        }

        return false;
    }
}
