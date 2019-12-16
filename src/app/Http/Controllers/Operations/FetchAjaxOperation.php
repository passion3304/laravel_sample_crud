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
        if (isset($this->fetch)) {
            foreach ($this->fetch as $relatedSegment => $entiyToFetch) {
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
        if(isset($this->fetch)) {
            $this->crud->setOperationSetting('ajaxEntities', $this->fetch);
        }
    }

    //fetch items from database based on search term
    public function fetchMultipleItems(Request $request)
    {
        $routeSegment = $this->getRouteSegment($request->route()->uri);

        $model = $this->fetch[$routeSegment]['model'];
        $itemsPerPage = $this->fetch[$routeSegment]['itemsPerPage'] ? $this->fetch[$routeSegment]['itemsPerPage'] : 10;

        //get searchable attributes if defined otherwise get identifiable attributes from model
        $whereToSearch = isset($this->fetch[$routeSegment]['searchableAttributes']) ?
        $this->fetch[$routeSegment]['searchableAttributes'] : $model::getIdentifiableName();

        $search_term = $request->input('q');
        $instance = new $model;
        $page = $request->input('page');

        $table = Config::get('database.connections.'.Config::get('database.default').'.prefix').$instance->getTable();

        if ($search_term) {
            foreach ($whereToSearch as $searchColumn) {
                $conn = $model::getPreparedConnection($instance);
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
}
