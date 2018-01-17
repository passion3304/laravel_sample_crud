<?php

namespace Backpack\CRUD\PanelTraits;

trait Query
{
    // ----------------
    // ADVANCED QUERIES
    // ----------------

    /**
     * Add another clause to the query (for ex, a WHERE clause).
     *
     * Examples:
     * // $this->crud->addClause('active');
     * $this->crud->addClause('type', 'car');
     * $this->crud->addClause('where', 'name', '==', 'car');
     * $this->crud->addClause('whereName', 'car');
     * $this->crud->addClause('whereHas', 'posts', function($query) {
     *     $query->activePosts();
     *     });
     *
     * @param [type]
     */
    public function addClause($function)
    {
        return call_user_func_array([$this->query, $function], array_slice(func_get_args(), 1, 3));
    }

    /**
     * Use eager loading to reduce the number of queries on the table view.
     *
     * @param  [type]
     * @param  string
     *
     * @return [type]
     */
    public function with($entities)
    {
        return $this->query->with($entities);
    }

    /**
     * Order the results of the query in a certain way.
     *
     * @param  [type]
     * @param  string
     *
     * @return [type]
     */
    public function orderBy($field, $order = 'asc')
    {
        return $this->query->orderBy($field, $order);
    }

    /**
     * Order the results of the query in a custom way.
     *
     * @param  [type]
     * @param  string
     *
     * @return [type]
     */
    public function customOrderBy($column, $column_direction = 'asc')
    {
        if (! isset($column['orderLogic'])) {
            return $this->query;
        }

        $this->query->getQuery()->orders = null;

        $orderLogic = $column['orderLogic'];

        if (is_callable($orderLogic)) {
            return $orderLogic($this->query, $column, $column_direction);
        }

        return $this->query;
    }

    /**
     * Group the results of the query in a certain way.
     *
     * @param  [type]
     * @return [type]
     */
    public function groupBy($field)
    {
        return $this->query->groupBy($field);
    }

    /**
     * Limit the number of results in the query.
     *
     * @param  [number]
     * @return [type]
     */
    public function limit($number)
    {
        return $this->query->limit($number);
    }

    /**
     * Take a certain number of results from the query.
     *
     * @param  [number]
     * @return [type]
     */
    public function take($number)
    {
        return $this->query->take($number);
    }

    /**
     * Start the result set from a certain number.
     *
     * @param  int $number [description]
     * @return [type]         [description]
     */
    public function skip($number)
    {
        return $this->query->skip($number);
    }

    /**
     * Count the number of results.
     */
    public function count()
    {
        return $this->query->count();
    }
}
