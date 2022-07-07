<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait Query
{
    public $query;

    // ----------------
    // ADVANCED QUERIES
    // ----------------

    /**
     * Add another clause to the query (for ex, a WHERE clause).
     *
     * Examples:
     * $this->crud->addClause('active');
     * $this->crud->addClause('type', 'car');
     * $this->crud->addClause('where', 'name', '==', 'car');
     * $this->crud->addClause('whereName', 'car');
     * $this->crud->addClause('whereHas', 'posts', function($query) {
     *     $query->activePosts();
     * });
     *
     * @param  callable|string  $function
     * @return mixed
     */
    public function addClause($function)
    {
        return call_user_func_array([$this->query, $function], array_slice(func_get_args(), 1));
    }

    /**
     * Use eager loading to reduce the number of queries on the table view.
     *
     * @param  array|string  $entities
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with($entities)
    {
        return $this->query->with($entities);
    }

    /**
     * Order the results of the query in a certain way.
     *
     * @param  string  $field
     * @param  string  $order
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orderBy($field, $order = 'asc')
    {
        if ($this->getRequest()->has('order')) {
            return $this->query;
        }

        return $this->query->orderBy($field, $order);
    }

    /**
     * Order results of the query in a custom way.
     *
     * @param  array  $column  Column array with all attributes
     * @param  string  $column_direction  ASC or DESC
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function customOrderBy($column, $columnDirection = 'asc')
    {
        if (! isset($column['orderLogic'])) {
            return $this->query;
        }

        $orderLogic = $column['orderLogic'];

        if (is_callable($orderLogic)) {
            return $orderLogic($this->query, $column, $columnDirection);
        }

        return $this->query;
    }

    /**
     * Group the results of the query in a certain way.
     *
     * @param  string  $field
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function groupBy($field)
    {
        return $this->query->groupBy($field);
    }

    /**
     * Limit the number of results in the query.
     *
     * @param  int  $number
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function limit($number)
    {
        return $this->query->limit($number);
    }

    /**
     * Take a certain number of results from the query.
     *
     * @param  int  $number
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function take($number)
    {
        return $this->query->take($number);
    }

    /**
     * Start the result set from a certain number.
     *
     * @param  int  $number
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function skip($number)
    {
        return $this->query->skip($number);
    }

    /**
     * Count the number of results.
     *
     * @return int
     */
    public function count()
    {
        return $this->query->count();
    }

    /**
     * Apply table prefix in the order clause if the query contains JOINS clauses.
     *
     * @param  string  $column_name
     * @param  string  $column_direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orderByWithPrefix($column_name, $column_direction = 'ASC')
    {
        if ($this->query->getQuery()->joins !== null) {
            return $this->query->orderByRaw($this->model->getTableWithPrefix().'.'.$column_name.' '.$column_direction);
        }

        return $this->query->orderBy($column_name, $column_direction);
    }

    /**
     * navigates the current crud query where clauses to get he columns that should be selected.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return array
     */
    public function getQueryColumnsFromWheres($query)
    {
        $wheresColumns = [];
        foreach ($query->wheres as $where) {
            switch ($where['type']) {
                case 'Basic':
                    $wheresColumns[] = $where['column'];

                break;
                case 'Nested':
                    $wheres = $where['query']->wheres;
                    foreach ($wheres as $subWhere) {
                        if ($subWhere['type'] === 'Basic') {
                            $wheresColumns[] = $subWhere['column'];
                        }
                        if ($subWhere['type'] === 'Exists') {
                            foreach ($subWhere['query']->wheres as $existSubWhere) {
                                if ($existSubWhere['type'] === 'Column') {
                                    $wheresColumns[] = $existSubWhere['first'];
                                }
                            }
                        }
                    }

                break;
                case 'Column':
                    $wheresColumns[] = $where['first'];

                break;
                case 'Exists':
                    $wheres = $where['query']->wheres;
                    foreach ($wheres as $subWhere) {
                        if ($subWhere['type'] === 'Column') {
                            $wheresColumns[] = $subWhere['first'];
                        }
                    }

                break;
            }
        }

        return $wheresColumns;
    }

    /**
     * count of available entries from the current crud query.
     *
     * @return int
     */
    public function getQueryCount()
    {
        $crudQuery = $this->query->toBase()->clone();
        $crudQueryColumns = $this->getQueryColumnsFromWheres($crudQuery);

        // merge the model key in the columns array
        $crudQueryColumns = array_merge($crudQueryColumns, [$this->model->getKeyName()]);

        // remove table prefix from select columns
        $crudQueryColumns = array_map(function ($item) {
            return \Str::afterLast($item, '.');
        }, $crudQueryColumns);

        // remove possible column name duplicates (when using the column name in combination with table.column name `where('table.column', smt')->where('column', 'smt').
        $crudQueryColumns = array_unique($crudQueryColumns);

        // create an "outter" query, the one that is responsible to do the count of the "crud query".
        $outterQuery = $crudQuery->newQuery();

        // in this outter query we will select only one column to be counted.
        $outterQuery = $outterQuery->select($this->model->getKeyName());

        // add the count query in the "outter" query.
        $outterQuery = $outterQuery->selectRaw("count('".$this->model->getKeyName()."') as total_rows");

        // add the subquery from where the "outter query" will count the results.
        // this subquery is the "main crud query" without some properties: 
        // - columns : we manually select the "minimum" columns possible from database.
        // - orders/limit/offset because we want the "full query count" where orders don't matter and limit/offset would break the total count
        $subQuery = $crudQuery->cloneWithout(['columns', 'orders', 'limit', 'offset']);
        $outterQuery = $outterQuery->fromSub($subQuery->select($crudQueryColumns), $this->model->getTableWithPrefix());

        return $outterQuery->get()->first()->total_rows;
    }
}
