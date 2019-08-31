<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request as StoreRequest;

trait CreateOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string $segment       Name of the current entity (singular). Used as first URL segment.
     * @param  string $routeName    Prefix of the route name.
     * @param  string $controller Name of the current CrudController.
     */
    protected function setupCreateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/create', [
            'as' => $routeName.'.create',
            'uses' => $controller.'@create',
            'operation' => 'create',
        ]);

        Route::put($segment.'/create', [
            'as' => $routeName.'store',
            'uses' => $controller.'@store',
            'operation' => 'create',
        ]);

        Route::post($segment, [
            'as' => $routeName.'store',
            'uses' => $controller.'@store',
            'operation' => 'create',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCreateDefaults()
    {
        $this->crud->allowAccess('create');

        $this->crud->operation('list', function() {
            $this->crud->addButton('top', 'create', 'view', 'crud::buttons.create');
        });
    }

    /**
     * Show the form for creating inserting a new row.
     *
     * @return Response
     */
    public function create()
    {
        $this->crud->hasAccessOrFail('create');
        $this->crud->applyConfigurationFromSettings('create');

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['fields'] = $this->crud->getCreateFields();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getCreateView(), $this->data);
    }

    /**
     * Store a newly created resource in the database.
     *
     * @param StoreRequest $request - type injection used for validation using Requests
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeEntry(StoreRequest $request = null)
    {
        $this->crud->hasAccessOrFail('create');
        $this->crud->applyConfigurationFromSettings('create');

        // fallback to global request instance
        if (is_null($request)) {
            $request = \Request::instance();
        }

        // insert item in the db
        $item = $this->crud->create($request->except(['save_action', '_token', '_method', 'current_tab', 'http_referrer']));
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
