<?php namespace Backpack\CRUD\app\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Crypt;
use Illuminate\Support\Facades\Form as Form;
use Alert;
use Backpack\CRUD\Crud;

// VALIDATION: change the requests to match your own file names if you need form validation
use Backpack\CRUD\app\Http\Requests\CrudRequest as StoreRequest;
use Backpack\CRUD\app\Http\Requests\CrudRequest as UpdateRequest;

class CrudController extends BaseController {

	use DispatchesJobs, ValidatesRequests;

	public $data = [];
	public $crud;

	public function __construct()
	{
		$this->crud = new Crud();
	}

	/**
	 * Display all rows in the database for this entity.
	 *
	 * @return Response
	 */
	public function index()
	{
		$this->crud->hasPermissionOrFail('list');

		$this->data['entries'] = $this->crud->getEntries();
		$this->data['crud'] = $this->crud;
		$this->data['title'] = ucfirst($this->crud->entity_name_plural);

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::list', $this->data);
	}


	/**
	 * Show the form for creating inserting a new row.
	 *
	 * @return Response
	 */
	public function create()
	{
		$this->crud->hasPermissionOrFail('add');

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
	 * @param  StoreRequest  $request - type injection used for validation using Requests
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function storeCrud(StoreRequest $request = null) // TODO: polish and move logic to Crud.php
	{
		$this->crud->hasPermissionOrFail('add');

		// compress the fake fields into one field
		$model = $this->crud->model;
		$values_to_store = $this->crud->compactFakeFields(\Request::all());
		$item = $model::create($values_to_store);

		// if it's a relationship with a pivot table, also sync that
		// $this->crud->prepareFields();
		foreach ($this->crud->fields as $k => $field) {
			if (isset($field['pivot']) && $field['pivot'] == true && \Request::has($field['name']))
			{
				$model::find($item->id)->$field['name']()->attach(\Request::input($field['name']));
			}
		}

		// show a success message
		\Alert::success(trans('backpack::crud.insert_success'))->flash();

		// redirect the user where he chose to be redirected
		switch (\Request::input('redirect_after_save')) {
			case 'current_item_edit':
				return \Redirect::to($this->crud->route.'/'.$item->id.'/edit');

			default:
				return \Redirect::to(\Request::input('redirect_after_save'));
		}
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$this->crud->hasPermissionOrFail('edit');

		// get the info for that entry
		$this->data['entry'] = $this->crud->getEntry($id);
		$this->data['crud'] = $this->crud;
		$this->data['fields'] = $this->crud->getUpdateFields($id);
		$this->data['title'] = trans('backpack::crud.edit').' '.$this->crud->entity_name;

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::edit', $this->data);
	}


	/**
	 * Update the specified resource in the database.
	 *
	 * @param  UpdateRequest  $request - type injection used for validation using Requests
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateCrud(UpdateRequest $request = null)  // TODO: polish and move logic to Crud.php
	{
		$this->crud->hasPermissionOrFail('edit');

		$model = $this->crud->model;
		// $this->crud->prepareFields($model::find(\Request::input('id')));

		$model::find(\Request::input('id'))->update($this->crud->compactFakeFields(\Request::all()));

		// if it's a relationship with a pivot table, also sync that
		foreach ($this->crud->fields as $k => $field) {
			if (isset($field['pivot']) && $field['pivot'] == true && \Request::has($field['name']))
			{
				$model::find(\Request::input('id'))->$field['name']()->sync(\Request::input($field['name']));
			}
		}

		// show a success message
		\Alert::success(trans('backpack::crud.update_success'))->flash();

		return \Redirect::to($this->crud->route);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)  // TODO: polish and move logic to Crud.php
	{
		$this->crud->hasPermissionOrFail('show');

		// get the info for that entry
		$model = $this->crud->model;
		$this->data['entry'] = $model::find($id);
		$this->data['entry']->addFakes($this->crud->getFakeColumnsAsArray());
		$this->data['crud'] = $this->crud;
		$this->data['title'] = trans('backpack::crud.preview').' '.$this->crud->entity_name;

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::show', $this->data);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return string
	 */
	public function destroy($id)
	{
		$this->crud->hasPermissionOrFail('delete');
		return $this->crud->delete($id);
	}


	/**
	 *  Reorder the items in the database using the Nested Set pattern.
	 *
	 *	Database columns needed: id, parent_id, lft, rgt, depth, name/title
	 *
	 *  @return Response
	 */
	public function reorder($lang = false)  // TODO: polish and move logic to Crud.php
	{
		$this->crud->hasPermissionOrFail('reorder');

		if ($lang == false)
		{
			$lang = \Lang::locale();
		}

		// get all results for that entity
		$model = $this->crud->model;
		$this->data['entries'] = $model::all();
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
	public function saveReorder()  // TODO: polish and move logic to Crud.php
	{
		$this->crud->hasPermissionOrFail('reorder');

		$model = $this->crud->model;
		$count = 0;
		$all_entries = \Request::input('tree');

		if (count($all_entries)) {
			foreach ($all_entries as $key => $entry) {
				if ($entry['item_id'] != "" && $entry['item_id'] != null) {
					$item = $model::find($entry['item_id']);
					$item->parent_id = $entry['parent_id'];
					$item->depth = $entry['depth'];
					$item->lft = $entry['left'];
					$item->rgt = $entry['right'];
					$item->save();

					$count++;
				}
			}
		} else
		{
			return false;
		}

		return 'success for '.$count." items";
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
	public function showDetailsRow($id)  // TODO: polish and move logic to Crud.php
	{
		$this->crud->hasPermissionOrFail('details');

		// get the info for that entry
		$model = $this->crud->model;
		$this->data['entry'] = $model::find($id);
		$this->data['entry']->addFakes($this->crud->getFakeColumnsAsArray());
		$this->data['original_entry'] = $this->data['entry'];
		$this->data['crud'] = $this->crud;

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::details_row', $this->data);
	}



}
