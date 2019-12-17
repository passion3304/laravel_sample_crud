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

//dd($this);
        if ($this->crud->hasOperationSetting('ajaxEntities')) {
            $ajaxEntities = $this->crud->getOperationSetting('ajaxEntities');
            foreach ($ajaxEntities as $relatedSegment => $entiyToFetch) {
                $routeSegment = isset($entiyToFetch['routeSegment']) ? $entiyToFetch['routeSegment'] : $relatedSegment;

                Route::get($segment.'/fetch/'.$routeSegment, [
                    'uses'      => $controller.'@fetchMultipleItems',
                    'operation' => 'FetchAjaxOperation',
                ]);

                Route::get($segment.'/fetch/'.$routeSegment.'/{id}', [
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

    //fetch items from database based on search term
    public function fetchMultipleItems(Request $request)
    {
        $entityRoutes = $this->crud->getOperationSetting('ajaxEntities');
        $routeSegment = $this->getRouteSegment($request->route()->uri);

        $model = $entityRoutes[$routeSegment]['model'];
        $instance = new $model;

        $itemsPerPage = $entityRoutes[$routeSegment]['itemsPerPage'] ? $entityRoutes[$routeSegment]['itemsPerPage'] : 10;

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

        $query = $entityRoutes[$routeSegment]['query'] ? $entityRoutes[$routeSegment]['query'] : $model;

        if (is_callable($query)) {
            $instance = $query($instance);
        }

        if (isset($search_term)) {
            if ($search_term == false) {
                return $instance->latest()->orderByDesc($instanceKey)->first();
            }
            foreach ($whereToSearch as $searchColumn) {
                $columnType = $conn->getSchemaBuilder()->getColumnType($table, $searchColumn);

                if (! isset($isFirst)) {
                    if ($columnType == 'string') {
                        $instance->where($searchColumn, 'LIKE', '%'.$search_term.'%');
                    } else {
                        $instance->where($searchColumn, $search_term);
                    }
                } else {
                    if ($columnType == 'string') {
                        $instance->orWhere($searchColumn, 'LIKE', '%'.$search_term.'%');
                    } else {
                        $instance->orWhere($searchColumn, $search_term);
                    }
                }
                $isFirst = true;
            }
            $results = $instance->paginate($itemsPerPage);
        } else {
            $results = $instance->paginate($itemsPerPage);
        }

        return $results;
    }

    public function fetchSingleItem($id)
    {
        $request = request()->instance();
        $routeSegment = $this->getRouteSegment($request->route()->uri);
        $model = $this->fetch[$routeSegment]['model'];

        return $model::findOrFail($id);
    }

    public function getRouteSegment($uri)
    {
        $routeSegments = explode('/', $uri);

        return end($routeSegments);
    }

    public function getAjaxEntityRoutes()
    {
        if (method_exists($this, 'ajaxEntityRoutes')) {
            return $this->ajaxEntityRoutes();
        }

        return [];
    }
}
