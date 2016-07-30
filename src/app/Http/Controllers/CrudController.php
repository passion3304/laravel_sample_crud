<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Backpack\CRUD\app\Http\Requests\CrudRequest as StoreRequest;
use Backpack\CRUD\app\Http\Requests\CrudRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
// VALIDATION: change the requests to match your own file names if you need form validation
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Form as Form;

class CrudController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    public $data = [];
    public $crud;

    public function __construct()
    {
        $this->crud = new CrudPanel();
    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return Response
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['entries'] = $this->crud->getEntries();
        $this->data['crud'] = $this->crud;
        $this->data['title'] = ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        // $this->crud->getListView() returns 'list' by default, or 'list_ajax' if ajax was enabled
        return view('crud::'.$this->crud->getListView(), $this->data);
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

        // insert item in the db
        $item = $this->crud->create($request->except(['redirect_after_save', 'password']));

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // redirect the user where he chose to be redirected
        switch ($request->input('redirect_after_save')) {
            case 'current_item_edit':
                return \Redirect::to($this->crud->route.'/'.$item->getKey().'/edit');

            default:
                return \Redirect::to($request->input('redirect_after_save'));
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

        // update the row in the db
        $this->crud->update($request->get($this->crud->model->getKeyName()),
                            $request->except('redirect_after_save'));

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        return \Redirect::to($this->crud->route);
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

    /**
     *  Reorder the items in the database using the Nested Set pattern.
     *
     *	Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     *  @return Response
     */
    public function reorder()
    {
        $this->crud->hasAccessOrFail('reorder');

        if (! $this->crud->isReorderEnabled()) {
            abort(403, 'Reorder is disabled.');
        }

        // get all results for that entity
        $this->data['entries'] = $this->crud->getEntries();
        $this->data['crud'] = $this->crud;
        $this->data['title'] = trans('backpack::crud.reorder').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::reorder', $this->data);
    }

    /**
     * Save the new order, using the Nested Set pattern.
     *
     * Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     * @return
     */
    public function saveReorder()
    {
        $this->crud->hasAccessOrFail('reorder');

        $all_entries = \Request::input('tree');

        if (count($all_entries)) {
            $count = $this->crud->updateTreeOrder($all_entries);
        } else {
            return false;
        }

        return 'success for '.$count.' items';
    }

    /**
     * Used with AJAX in the list view (datatables) to show extra information about that row that didn't fit in the table.
     * It defaults to showing some dummy text.
     *
     * It's enabled by:
     * - setting: $crud->details_row = true;
     * - adding the details route for the entity; ex: Route::get('page/{id}/details', 'PageCrudController@showDetailsRow');
     * - adding a view with the following name to change what the row actually contains: app/resources/views/vendor/backpack/crud/details_row.blade.php
     */
    public function showDetailsRow($id)
    {
        $this->crud->hasAccessOrFail('details_row');

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::details_row', $this->data);
    }

    public function search() {
        dd(\Request::all());
        // Data received through GET parameters
        // draw
        // columns
        // order
        // start
        // length
        // search
        // token
        return [
                  "draw" => 3,
                  "recordsTotal" => 57,
                  "recordsFiltered" => 57,
                  "columns" => [
                    "data" => "date",
                    "data" => "status",
                    "data" => "title",
                    "data" => "featured",
                    "data" => "category"
                  ],
                  "data" => [
                    [
                      "Airi",
                      "Satou",
                      "Accountant",
                      "Tokyo",
                      "28th Nov 08",
                      "$162,700"
                    ],
                    [
                      "Angelica",
                      "Ramos",
                      "Chief Executive Officer (CEO)",
                      "London",
                      "9th Oct 09",
                      "$1,200,000"
                    ],
                    [
                      "Ashton",
                      "Cox",
                      "Junior Technical Author",
                      "San Francisco",
                      "12th Jan 09",
                      "$86,000"
                    ],
                    [
                      "Bradley",
                      "Greer",
                      "Software Engineer",
                      "London",
                      "13th Oct 12",
                      "$132,000"
                    ],
                    [
                      "Brenden",
                      "Wagner",
                      "Software Engineer",
                      "San Francisco",
                      "7th Jun 11",
                      "$206,850"
                    ],
                    [
                      "Brielle",
                      "Williamson",
                      "Integration Specialist",
                      "New York",
                      "2nd Dec 12",
                      "$372,000"
                    ],
                    [
                      "Bruno",
                      "Nash",
                      "Software Engineer",
                      "London",
                      "3rd May 11",
                      "$163,500"
                    ],
                    [
                      "Caesar",
                      "Vance",
                      "Pre-Sales Support",
                      "New York",
                      "12th Dec 11",
                      "$106,450"
                    ],
                    [
                      "Cara",
                      "Stevens",
                      "Sales Assistant",
                      "New York",
                      "6th Dec 11",
                      "$145,600"
                    ],
                    [
                      "Cedric",
                      "Kelly",
                      "Senior Javascript Developer",
                      "Edinburgh",
                      "29th Mar 12",
                      "$433,060"
                    ]
                  ]
                ];
    }
}
