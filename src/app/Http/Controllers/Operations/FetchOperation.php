<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

trait FetchOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupFetchOperationRoutes($segment, $routeName, $controller)
    {
        preg_match_all('/(?<=^|;)fetch([^;]+?)(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                //De-capitalize first letter.
                lcfirst($methodName);
                //Replace capitals with lowers and add hifens. (WhatEver will return what-ever)
                $methodName = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $methodName));

                Route::get($segment.'/fetch/'.$methodName, [
                    'uses'      => $controller.'@operationFetch',
                    'operation' => 'FetchOperation',
                ]);

                Route::get($segment.'/fetch/{id}/'.$methodName, [
                    'uses'      => $controller.'@fetchSingleItem',
                    'operation' => 'FetchOperation',
                ]);
            }
        }
    }

    /**
     * This is the public endpoint that receives the request and parses which entity we would like to fetch.
     *
     * @return void
     */
    public function operationFetch()
    {
        $request = \Request::instance();
        $routeSegment = last(explode('/', $request->route()->uri));

        //rebuild function name from url segment
        $methodName = str_replace('-', '', ucwords($routeSegment, '-'));

        ucfirst($methodName);

        if (method_exists($this, 'fetch'.$methodName)) {
            return $this->{'fetch'.$methodName}();
        }

        return response()->json(['error' => 'You must define fetch'.$methodName.'() in your crud controller.'], 500);
    }

    /**
     * Gets items from database and returns to selects.
     *
     * @param string|array $arg
     * @return void
     */
    public function fetch($arg)
    {
        $request = \Request::instance();
        $fetchConfig = [];

        if (! is_array($arg)) {
            if (! class_exists($arg)) {
                return response()->json(['error' => 'Class: '.$arg.' does not exists'], 500);
            }
            $fetchConfig['model'] = $arg;
        } else {
            $fetchConfig = $arg;
        }

        $model = $fetchConfig['model'];

        $instance = new $model;

        $itemsPerPage = isset($fetchConfig['itemsPerPage']) ? $fetchConfig['itemsPerPage'] : 10;

        //get searchable attributes if defined otherwise get identifiable attributes from model
        $whereToSearch = isset($fetchConfig['searchableAttributes']) ?
        $fetchConfig['searchableAttributes'] : $model::getIdentifiableName();

        $table = Config::get('database.connections.'.$instance->getConnectionName().'.prefix').$instance->getTable();

        $conn = $model::getConnectionWithExtraTypeMappings($instance);

        if ($request->has('q')) {
            if (empty($request->input('q'))) {
                $search_term = false;
            } else {
                $search_term = $request->input('q');
            }
        }

        $query = isset($fetchConfig['query']) ? $fetchConfig['query'] : $model;

        if (is_callable($query)) {
            $instance = $query($instance);
        }

        if (isset($search_term)) {
            if ($search_term === false) {
                return $instance->first();
            }
            foreach ($whereToSearch as $searchColumn) {
                $columnType = $conn->getSchemaBuilder()->getColumnType($table, $searchColumn);

                $operation = ! isset($isFirst) ? 'where' : 'orWhere';

                if ($columnType == 'string') {
                    $instance->{$operation}($searchColumn, 'LIKE', '%'.$search_term.'%');
                } else {
                    $instance->{$operation}($searchColumn, $search_term);
                }
                $isFirst = true;
            }
        }

        $results = $instance->paginate($itemsPerPage);

        return $results;
    }
}
