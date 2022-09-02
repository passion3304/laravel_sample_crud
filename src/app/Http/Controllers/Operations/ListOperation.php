<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait ListOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupListRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/', [
            'as'        => $routeName.'.index',
            'uses'      => $controller.'@index',
            'operation' => 'list',
        ]);

        Route::post($segment.'/search', [
            'as'        => $routeName.'.search',
            'uses'      => $controller.'@search',
            'operation' => 'list',
        ]);

        Route::get($segment.'/{id}/details', [
            'as'        => $routeName.'.showDetailsRow',
            'uses'      => $controller.'@showDetailsRow',
            'operation' => 'list',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupListDefaults()
    {
        $this->crud->allowAccess('list');

        $this->crud->operation('list', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });
    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getListView(), $this->data);
    }

    /**
     * The search function that is called by the data table.
     *
     * @return array JSON Array of cells in HTML form.
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        // how many total entries there is applying the controller/model constrains, before any filtering or search
        $this->crud->setOperationSetting('unfilteredQueryCount', $this->crud->getUnfilteredQueryCount());

        $this->crud->applyUnappliedFilters();

        $startIndex = (int) request()->input('start');
        $length = (int) request()->input('length');
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm(request()->input('search')['value']);
        }
        // start the results according to the datatables pagination
        if ($startIndex) {
            $this->crud->skip($startIndex);
        }
        // limit the number of results according to the datatables pagination
        if ($length) {
            $this->crud->take($length);
        }
        // overwrite any order set in the setup() method with the datatables order
        $this->crud->applyDatatableOrder();

        // after applying filters and search, we re-do the calculations of the entries count
        $filteredRows = $this->crud->getOperationSetting('showEntryCount') ? $this->crud->getCurrentQueryCount() : 0;

        $entries = $this->crud->getEntries();

        // if show entry count is disabled we use the "simplePagination" technique to move between pages.
        if (! $this->crud->getOperationSetting('showEntryCount')) {
            $this->crud->setOperationSetting('unfilteredQueryCount', $length);
            $filteredRows = $entries->count() < $length ? 0 : $length + $startIndex + 1;
        }

        return $this->crud->getEntriesAsJsonForDatatables($entries, $this->crud->getOperationSetting('unfilteredQueryCount'), $filteredRows, $startIndex);
    }

    /**
     * Used with AJAX in the list view (datatables) to show extra information about that row that didn't fit in the table.
     * It defaults to showing some dummy text.
     *
     * @return \Illuminate\View\View
     */
    public function showDetailsRow($id)
    {
        $this->crud->hasAccessOrFail('list');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getDetailsRowView(), $this->data);
    }
}
