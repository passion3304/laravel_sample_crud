<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
        // get all method names on the current model that start with "fetch" (ex: fetchCategory)
        // if a method that looks like that is present, it means we need to add the routes that fetch that entity
        preg_match_all('/(?<=^|;)fetch([^;]+?)(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                Route::post($segment.'/fetch/'.Str::kebab($methodName), [
                    'uses'      => $controller.'@fetch'.$methodName,
                    'operation' => 'FetchOperation',
                ]);
            }
        }
    }

    /**
     * Gets items from database and returns to selects.
     *
     * @param string|array $arg
     * @return void
     */
    private function fetch($arg)
    {
        // get the actual words that were used to search for an item (the search term / search string)
        $searchString = request()->input('q') ?? false;

        // if the Class was passed as the sole argument, use that as the configured Model
        // otherwise assume the arguments are actually the configuration array
        $config = [];

        if (! is_array($arg)) {
            if (! class_exists($arg)) {
                return response()->json(['error' => 'Class: '.$arg.' does not exists'], 500);
            }
            $config['model'] = $arg;
        } else {
            $config = $arg;
        }

        // set configuration defaults
        $config['itemsPerPage'] = $config['itemsPerPage'] ?? 10;
        $config['searchableAttributes'] = $config['searchableAttributes'] ?? array($config['model']::getIdentifiableName());
        $config['query'] = isset($config['query']) && is_callable($config['query']) ? $config['query']($config['model']) : new $config['model']; // if a closure that has been passed as "query", use the closure - otherwise use the model

        if ($searchString === false) {
            return $config['model']->first();
        }

        foreach ($config['searchableAttributes'] as $k => $searchColumn) {
            $operation = ($k == 0) ? 'where' : 'orWhere';
            $columnType = $config['query']->getColumnType($searchColumn);

            if ($columnType == 'string') {
                $config['query'] = $config['query']->{$operation}($searchColumn, 'LIKE', '%'.$searchString.'%');
            } else {
                $config['query'] = $config['query']->{$operation}($searchColumn, $searchString);
            }
        }

        return $config['query']->paginate($config['itemsPerPage']);
    }
}
