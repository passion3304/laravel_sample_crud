<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Str;

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
     * Return the nested query columns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return array
     */
    private function getNestedQueryColumns($query)
    {
        return $this->getQueryColumnsFromWheres($query, true);
    }

    /**
     * We want to select the minimum possible columns respecting the clauses in the query, so that the count is accurate.
     * for that to happen we will traverse the query `wheres` (Basic, Exists, Nested or Column) to get the correct
     * column that we need to select in the main model for that "sub query" to work.
     *
     * For example a base query of: `SELECT * FROM table WHERE (someColumn, smtValue)`, we would return only the `someColumn`
     * with the objective of replacing the `*` for the specific columns needed, avoiding the selection of
     * columns that would not have impact in the counting process.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool  $nested  used to prevent multiple level nesting as we only need the first level columns
     * @return array
     */
    private function getQueryColumnsFromWheres($query, $nested = false)
    {
        // if there is a raw where we are better not touching the columns, otherwise we
        // would need to parse the raw sql wheres and that can get messy very quick.
        if(in_array('raw',array_column($query->wheres, 'type'))) {
            return $query->columns;
        }

        $wheresColumns = [];
        foreach ($query->wheres as $where) {
            switch ($where['type']) {
                // case it's a basic where, we just want to select that column.
                case 'Basic':
                    $wheresColumns[] = $where['column'];

                break;
                // when it's a nested query we will get the columns that link
                // to the main table from the nested query wheres.
                case 'Nested':
                    $wheresColumns = $nested ?: array_merge($wheresColumns, $this->getNestedQueryColumns($where['query']));
                break;
                // when Column get the "first" key that represent the base table column to link with
                case 'Column':
                    $wheresColumns[] = $where['first'];

                break;
                // in case of Exists, we will find in the subquery the query type Column where it links to the main table
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
     * Return the unfiltered query count first from the request, then operation setting or count it in query.
     * This represents the crud panel query without any filters or search applied.
     *
     * @return int
     */
    public function getUnfilteredQueryCount()
    {
        if (! $this->getOperationSetting('showEntryCount')) {
            return 0;
        }

        return  $this->getRequest()->input('unfilteredQueryCount') ??
                $this->getOperationSetting('unfilteredQueryCount') ??
                $this->getCurrentQueryCount();
    }

    /**
     * Return the current query count.
     *
     * @return int
     */
    public function getCurrentQueryCount()
    {
        return $this->getQueryCount();
    }

    /**
     * Do a separate query to get the total number of entries, in an optimized way.
     *
     * @return int
     */
    private function getQueryCount()
    {
        $crudQuery = $this->query->toBase()->clone();
        $crudQueryColumns = $this->getQueryColumnsFromWheres($crudQuery);

        // merge the model key in the columns array if needed
        $crudQueryColumns = $this->addModelKeyToColumnsArray($crudQueryColumns);

        // remove table prefix from select columns
        $crudQueryColumns = array_map(function ($item) {
            return Str::afterLast($item, '.');
        }, $crudQueryColumns);

        // remove possible column name duplicates (when using the column name in combination with table.column name in some other constrain
        // for example `where('table.column', smt') and in other place where('column', 'smt').
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

    /**
     * Adds the model key into the selection columns array.
     * When using `*` as column selector it's assumed the model key would be selected.
     * 
     * @param  array  $columns
     * @return  array
     */
    private function addModelKeyToColumnsArray(array $columns) 
    {
        if(!in_array($this->model->getKeyName(), $columns) && !in_array('*', $columns)) {
            return array_merge($columns, [$this->model->getKeyName()]);
        }
        return $columns;
    }
}
