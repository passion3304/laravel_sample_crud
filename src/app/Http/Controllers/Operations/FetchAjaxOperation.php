<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

trait FetchAjaxOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupFetchAjaxOperationRoutes($segment, $routeName, $controller)
    {
        if ($this->crud->hasOperationSetting('ajaxEntities')) {
            $ajaxEntities = $this->crud->getOperationSetting('ajaxEntities');
            foreach ($ajaxEntities as $relatedSegment => $entiyToFetch) {
                $routeSegment = isset($entiyToFetch['routeSegment']) ? $entiyToFetch['routeSegment'] : $relatedSegment;

                Route::get($segment.'/fetch/'.$routeSegment, [
                    'uses'      => $controller.'@fetchMultipleItems',
                    'operation' => 'FetchAjaxOperation',
                ]);

                Route::get($segment.'/fetch/{id}/'.$routeSegment, [
                    'uses'      => $controller.'@fetchSingleItem',
                    'operation' => 'FetchAjaxOperation',
                ]);
            }
        }
    }

    public function setupFetchAjaxOperationDefaults()
    {
        $this->setupBareCrud();
    }

    /*
        Setting up a bare CRUD. No session variables available here.
    */

    public function setupBareCrud()
    {
        $entityRoutes = $this->getAjaxEntityRoutes();
        $this->crud->setOperationSetting('ajaxEntities', $entityRoutes);
    }

    /**
     * Gets items from database and returns to selects.
     *
     * @param Request $request
     * @return void
     */
    public function fetchMultipleItems(Request $request)
    {
        $entityRoutes = $this->crud->getOperationSetting('ajaxEntities');
        $routeSegment = $this->getRouteSegment($request->route()->uri);

        $model = $entityRoutes[$routeSegment]['model'];

        $instance = new $model;

        $itemsPerPage = isset($entityRoutes[$routeSegment]['itemsPerPage']) ? $entityRoutes[$routeSegment]['itemsPerPage'] : 10;

        //get searchable attributes if defined otherwise get identifiable attributes from model
        $whereToSearch = isset($entityRoutes[$routeSegment]['searchableAttributes']) ?
        $entityRoutes[$routeSegment]['searchableAttributes'] : $model::getIdentifiableName();

        $table = Config::get('database.connections.'.Config::get('database.default').'.prefix').$instance->getTable();

        $instanceKey = $instance->getKeyName();

        $conn = $model::getPreparedConnection($instance);

        if ($request->has('q')) {
            if (empty($request->input('q'))) {
                $search_term = false;
            } else {
                $search_term = $request->input('q');
            }
        }

        $query = isset($entityRoutes[$routeSegment]['query']) ? $entityRoutes[$routeSegment]['query'] : $model;

        if (is_callable($query)) {
            $instance = $query($instance);
        }

        if (isset($search_term)) {
            if ($search_term === false) {
                return $instance->latest()->orderByDesc($instanceKey)->first();
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

    /**
     * Fetches a single item from database.
     *
     * @param int $id
     * @return void
     */
    public function fetchSingleItem($id)
    {
        $request = request()->instance();
        $routeSegment = $this->getRouteSegment($request->route()->uri);
        $entityRoutes = $this->crud->getOperationSetting('ajaxEntities');
        $model = $entityRoutes[$routeSegment]['model'];

        return $model::findOrFail($id);
    }

    /**
     * Get url segment from uri.
     *
     * @param string $uri
     * @return string
     */
    public function getRouteSegment($uri)
    {
        $routeSegments = explode('/', $uri);

        return end($routeSegments);
    }

    /**
     * Gets developer defined endpoints.
     *
     * @return array
     */
    public function getAjaxEntityRoutes()
    {
        if (method_exists($this, 'ajaxEntityRoutes')) {
            return $this->ajaxEntityRoutes();
        }

        return [];
    }
}
