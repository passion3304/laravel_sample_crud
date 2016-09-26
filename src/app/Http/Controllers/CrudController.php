<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Form as Form;
use Backpack\CRUD\app\Http\Requests\CrudRequest as StoreRequest;
use Backpack\CRUD\app\Http\Requests\CrudRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
// CRUD Traits for non-core features
use Backpack\CRUD\app\Http\Controllers\CrudFeatures\AjaxTable;
use Backpack\CRUD\app\Http\Controllers\CrudFeatures\Reorder;
use Backpack\CRUD\app\Http\Controllers\CrudFeatures\Revisions;
use Backpack\CRUD\app\Http\Controllers\CrudFeatures\ShowDetailsRow;

class CrudController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
    use AjaxTable, Reorder, Revisions, ShowDetailsRow;

    public $data = [];
    public $crud;

    public function __construct()
    {
        $this->crud = new CrudPanel();

        // call the setup function inside this closure to also have the request there
        // this way, developers can use things stored in session (auth variables, etc)
        $this->middleware(function ($request, $next) {
            $this->setup();

            return $next($request);
        });
    }

    /**
     * Allow developers to set their configuration options for a CrudPanel.
     */
    public function setup()
    {
    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return Response
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = ucfirst($this->crud->entity_name_plural);

        // get all entries if AJAX is not enabled
        if (! $this->data['crud']->ajaxTable()) {
            $this->data['entries'] = $this->data['crud']->getEntries();
        }

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        // $this->crud->getListView() returns 'list' by default, or 'list_ajax' if ajax was enabled
        return view('crud::list', $this->data);
    }

    /**
     * Show the form for creating inserting a new row.
     *
     * @return Response
     */
    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getCreateFields();
        $this->data['title'] = trans('backpack::crud.add').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::create', $this->data);
    }

    /**
     * Store a newly created resource in the database.
     *
     * @param StoreRequest $request - type injection used for validation using Requests
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCrud(StoreRequest $request = null)
    {
        $this->crud->hasAccessOrFail('create');

        // fallback to global request instance
        if (is_null($request)) {
            $request = \Request::instance();
        }

        // replace empty values with NULL, so that it will work with MySQL strict mode on
        foreach ($request->input() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $request->request->set($key, null);
            }
        }

        // insert item in the db
        $item = $this->crud->create($request->except(['redirect_after_save', '_token']));

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        //save the redirect choice for next time
        $redirect_after_save = $request->input('redirect_after_save');
        session(['redirect_after_save' => $redirect_after_save]);

        // redirect the user where he chose to be redirected
        switch ($redirect_after_save) {
            case 'current_item_edit':
                return \Redirect::to($this->crud->route.'/'.$item->getKey().'/edit');

            default:
                return \Redirect::to($redirect_after_save);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['title'] = trans('backpack::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::edit', $this->data);
    }

    /**
     * Update the specified resource in the database.
     *
     * @param UpdateRequest $request - type injection used for validation using Requests
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCrud(UpdateRequest $request = null)
    {
        $this->crud->hasAccessOrFail('update');

        // fallback to global request instance
        if (is_null($request)) {
            $request = \Request::instance();
        }

        // replace empty values with NULL, so that it will work with MySQL strict mode on
        foreach ($request->input() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $request->request->set($key, null);
            }
        }

        // update the row in the db
        $this->crud->update($request->get($this->crud->model->getKeyName()),
                            $request->except('redirect_after_save', '_token'));

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        //save the redirect choice for next time
        $redirect_after_update = $request->input('redirect_after_update');
        session(['redirect_after_update' => $redirect_after_update]);

        // redirect the user where he chose to be redirected
        switch ($redirect_after_update) {
            case 'current_item_edit':
                return \Redirect::to($this->crud->route.'/'.$request->input('id').'/edit');

            default:
                return \Redirect::to($redirect_after_update);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::show', $this->data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return string
     */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        return $this->crud->delete($id);
    }
}
