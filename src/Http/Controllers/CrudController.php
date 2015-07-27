<?php namespace Dick\CRUD\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\CrudRequest as StoreRequest;
use App\Http\Requests\CrudRequest as UpdateRequest;

class CrudController extends Controller {

	public $crud = array(
						"model" => "\App\Models\Entity",
						"entity_name" => "entry",
						"entity_name_plural" => "entries",
						"view_table_permission" => true,
						"add_permission" => true,
						"edit_permission" => true,
						"delete_permission" => true,
						);

	public function __construct()
	{
		$this->data['crud'] = $this->crud;

		// Check for the right roles to access these pages
		if (!\Entrust::can('view-admin-panel')) {
	        abort(403, trans('crud.unauthorized_access'));
	    }
	}

	/**
	 * Display all rows in the database for this entity.
	 *
	 * @return Response
	 */
	public function index()
	{
		// if view_table_permission is false, abort
		if (isset($this->crud['view_table_permission']) && !$this->crud['view_table_permission']) {
			abort(403, 'Not allowed.');
		}

		// get all results for that entity
		$model = $this->crud['model'];
		$this->data['entries'] = $model::all();

		$this->_prepare_columns(); // checks that the columns are defined and makes sure the response is proper

		$this->data['crud'] = $this->crud;
		return view('crud/list', $this->data);
	}

	/**
	 * Show the form for creating inserting a new row.
	 *
	 * @return Response
	 */
	public function create()
	{
		// if add_permission is false, abort
		if (isset($this->crud['add_permission']) && !$this->crud['add_permission']) {
			abort(403, 'Not allowed.');
		}

		// get the fields you need to show
		if (isset($this->data['crud']['create_fields']))
		{
			$this->crud['fields'] = $this->data['crud']['create_fields'];
		}
		$this->_prepare_fields(); // TODO: prepare the fields you need to show

		$this->data['crud'] = $this->crud;
		return view('crud/create', $this->data);
	}


	/**
	 * Store a newly created resource in the database.
	 *
	 * @return Response
	 */
	public function store_crud(StoreRequest $request = null)
	{
		// if add_permission is false, abort
		if (isset($this->crud['add_permission']) && !$this->crud['add_permission']) {
			abort(403, 'Not allowed.');
		}

		$model = $this->crud['model'];
		$item = $model::create(\Request::all());

		// if it's a relationship with a pivot table, also sync that
		$this->_prepare_fields();
		foreach ($this->crud['fields'] as $k => $field) {
			if (isset($field['pivot']) && $field['pivot']==true)
			{
				$model::find($item->id)->$field['name']()->attach(\Request::input($field['name']));
			}
		}

		\Alert::success(trans('crud.insert_success'))->flash();

		// redirect the user where he chose to be redirected
		switch (\Request::input('redirect_after_save')) {
			case 'current_item_edit':
				return \Redirect::to($this->crud['route'].'/'.$item->id.'/edit');
				break;

			default:
				return \Redirect::to(\Request::input('redirect_after_save'));
				break;
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
		// if edit_permission is false, abort
		if (isset($this->crud['edit_permission']) && !$this->crud['edit_permission']) {
			abort(403, 'Not allowed.');
		}

		// get the info for that entry
		$model = $this->crud['model'];
		$this->data['entry'] = $model::find($id);
		if (isset($this->data['crud']['update_fields']))
		{
			$this->crud['fields'] = $this->data['crud']['update_fields'];
		}
		$this->_prepare_fields($this->data['entry']); // prepare the fields you need to show and prepopulate the values

		$this->data['crud'] = $this->crud;
		return view('crud/edit', $this->data);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update_crud(UpdateRequest $request = null)
	{
		// if edit_permission is false, abort
		if (isset($this->crud['edit_permission']) && !$this->crud['edit_permission']) {
			abort(403, 'Not allowed.');
		}

		$model = $this->crud['model'];
		$this->_prepare_fields($model::find(\Request::input('id')));

		$item = $model::find(\Request::input('id'))
						->update(\Request::all());

		// if it's a relationship with a pivot table, also sync that
		foreach ($this->crud['fields'] as $k => $field) {
			if (isset($field['pivot']) && $field['pivot']==true)
			{
				$model::find(\Request::input('id'))->$field['name']()->sync(\Request::input($field['name']));
			}
		}

		\Alert::success(trans('crud.update_success'))->flash();
		return \Redirect::to($this->crud['route']);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		// get the info for that entry
		$model = $this->crud['model'];
		$this->data['entry'] = $model::find($id);

		$this->data['crud'] = $this->crud;
		return view('crud/show', $this->data);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		// if delete_permission is false, abort
		if (isset($this->crud['delete_permission']) && !$this->crud['delete_permission']) {
			abort(403, trans('crud.unauthorized_access'));
		}

		$model = $this->crud['model'];
		$item = $model::find($id);
		$item->delete();

		return 'true';
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
		// if reorder_table_permission is false, abort
		if (isset($this->crud['reorder_table_permission']) && !$this->crud['reorder_table_permission']) {
			abort(403, 'Not allowed.');
		}

		// get all results for that entity
		$model = $this->crud['model'];
		$this->data['entries'] = $model::all();
		$this->data['crud'] = $this->crud;

		return view('crud/reorder', $this->data);
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
		$model = $this->crud['model'];
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
		}
		else
		{
			return false;
		}

		return 'success for '.$count." items";
	}



	/**
	 * COMMODITY FUNCTIONS
	 */

	// If it's not an array of array and it's a simple array, create a proper array of arrays for it
	public function _prepare_columns()
	{
		// if the columns aren't set, we can't show this page
		// TODO: instead of dying, show the columns defined as visible on the model
		if (!isset($this->crud['columns']))
		{
			abort(500, "CRUD columns are not defined.");
		}

		// if the columns are defined as a string, transform it to a proper array
		if (!is_array($this->crud['columns']))
		{
			$current_columns_array = explode(",", $this->crud['columns']);
			$proper_columns_array = array();

			foreach ($current_columns_array as $key => $col) {
				$proper_columns_array[] = [
								'name' => $col,
								'label' => ucfirst($col) //TODO: also replace _ with space
							];
			}

			$this->crud['columns'] = $proper_columns_array;
		}
	}

	public function _prepare_fields($entry = false)
	{
		// if the fields aren't set, trigger error
		if (!isset($this->crud['fields']))
		{
			abort(500, "The CRUD fields are not defined.");
		}

		// if the fields are defined as a string, transform it to a proper array
		if (!is_array($this->crud['fields']))
		{
			$current_fields_array = explode(",", $this->crud['fields']);
			$proper_fields_array = array();

			foreach ($current_fields_array as $key => $field) {
				$proper_fields_array[] = [
								'name' => $field,
								'label' => ucfirst($field), // TODO: also replace _ with space
								'type' => 'text' // TODO: choose different types of fields depending on the MySQL column type
							];
			}

			$this->crud['fields'] = $proper_fields_array;
		}

		// if an entry was passed, we're preparing for the update form, not create
		if ($entry) {
			// put the values in the save 'fields' variable
			$fields = $this->crud['fields'];
			foreach ($fields as $k => $field) {
				$this->crud['fields'][$k]['value'] = $entry->$field['name'];
			}

			// always have a hidden input for the entry id
			$this->crud['fields'][] = array(
												'name' => 'id',
												'value' => $entry->id,
												'type' => 'hidden'
											);
		}
	}

}
