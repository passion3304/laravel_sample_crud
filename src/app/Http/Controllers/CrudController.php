<?php namespace Backpack\CRUD\app\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Crypt;
use Illuminate\Support\Facades\Form as Form;
use Alert;

// VALIDATION: change the requests to match your own file names if you need form validation
use Backpack\CRUD\app\Http\Requests\CrudRequest as StoreRequest;
use Backpack\CRUD\app\Http\Requests\CrudRequest as UpdateRequest;

class CrudController extends BaseController {

	use DispatchesJobs, ValidatesRequests;

	public $data = array();
	public $crud = array(
						"model" => "\App\Models\Entity",
						"entity_name" => "entry",
						"entity_name_plural" => "entries",
						"view_table_permission" => true,
						"add_permission" => true,
						"edit_permission" => true,
						"delete_permission" => true,
						"reorder_permission" => true,
						"reorder_max_level" => 3,
						"details_row" => false,
						);

	public function __construct()
	{
		$this->data['crud'] = $this->crud;
	}

	/**
	 * Display all rows in the database for this entity.
	 *
	 * @return Response
	 */
	public function index()
	{
		// SECURITY:
		// if view_table_permission is false, abort
		if (isset($this->crud['view_table_permission']) && !$this->crud['view_table_permission']) {
			abort(403, 'Not allowed.');
		}

		// get all results for that entity
		$model = $this->crud['model'];
		if (property_exists($model, 'translatable'))
		{
			$this->data['entries'] = $model::where('translation_lang', \Lang::locale())->get();
		}
		else
		{
			$this->data['entries'] = $model::all();
		}

			// add the fake fields for each entry
			foreach ($this->data['entries'] as $key => $entry) {
				$entry->addFakes($this->getFakeColumnsAsArray());
			}

		$this->prepareColumns();
		$this->data['crud'] = $this->crud;
		$this->data['title'] = ucfirst($this->crud['entity_name_plural']);

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
		// SECURITY:
		// if add_permission is false, abort
		if (isset($this->crud['add_permission']) && !$this->crud['add_permission']) {
			abort(403, 'Not allowed.');
		}

		// get the fields you need to show
		if (isset($this->data['crud']['create_fields']))
		{
			$this->crud['fields'] = $this->data['crud']['create_fields'];
		}

		// prepare the fields you need to show
		$this->prepareFields();
		$this->data['crud'] = $this->crud;
		$this->data['title'] = trans('backpack::crud.add').' '.$this->crud['entity_name'];

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::create', $this->data);
	}


	/**
	 * Store a newly created resource in the database.
	 *
	 * @param  StoreRequest  $request - type injection used for validation using Requests
	 * @return Response
	 */
	public function storeCrud(StoreRequest $request = null)
	{
		// SECURITY:
		// if add_permission is false, abort
		if (isset($this->crud['add_permission']) && !$this->crud['add_permission']) {
			abort(403, 'Not allowed.');
		}

		// compress the fake fields into one field
		$model = $this->crud['model'];
		$values_to_store = $this->compactFakeFields(\Request::all());
		$item = $model::create($values_to_store);

		// if it's a relationship with a pivot table, also sync that
		$this->prepareFields();
		foreach ($this->crud['fields'] as $k => $field) {
			if (isset($field['pivot']) && $field['pivot']==true && \Request::has($field['name']))
			{
				$model::find($item->id)->$field['name']()->attach(\Request::input($field['name']));
			}
		}

		// show a success message
		\Alert::success(trans('backpack::crud.insert_success'))->flash();

		// redirect the user where he chose to be redirected
		switch (\Request::input('redirect_after_save')) {
			case 'current_item_edit':
				return \Redirect::to($this->crud['route'].'/'.$item->id.'/edit');

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
		// SECURITY:
		// if edit_permission is false, abort
		if (isset($this->crud['edit_permission']) && !$this->crud['edit_permission']) {
			abort(403, 'Not allowed.');
		}

		// get the info for that entry
		$model = $this->crud['model'];
		$this->data['entry'] = $model::find($id);
		$this->data['entry']->addFakes($this->getFakeColumnsAsArray());

		if (isset($this->data['crud']['update_fields']))
		{
			$this->crud['fields'] = $this->data['crud']['update_fields'];
		}

		// prepare the fields you need to show and prepopulate the values
		$this->prepareFields($this->data['entry']);
		$this->data['crud'] = $this->crud;
		$this->data['title'] = trans('backpack::crud.edit').' '.$this->crud['entity_name'];

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::edit', $this->data);
	}


	/**
	 * Update the specified resource in the database.
	 *
	 * @param  UpdateRequest  $request - type injection used for validation using Requests
	 * @return Response
	 */
	public function updateCrud(UpdateRequest $request = null)
	{
		// if edit_permission is false, abort
		if (isset($this->crud['edit_permission']) && !$this->crud['edit_permission']) {
			abort(403, 'Not allowed.');
		}

		$model = $this->crud['model'];
		$this->prepareFields($model::find(\Request::input('id')));

		$model::find(\Request::input('id'))->update($this->compactFakeFields(\Request::all()));

		// if it's a relationship with a pivot table, also sync that
		foreach ($this->crud['fields'] as $k => $field) {
			if (isset($field['pivot']) && $field['pivot']==true && \Request::has($field['name']))
			{
				$model::find(\Request::input('id'))->$field['name']()->sync(\Request::input($field['name']));
			}
		}

		// show a success message
		\Alert::success(trans('backpack::crud.update_success'))->flash();

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
		$this->data['entry']->addFakes($this->getFakeColumnsAsArray());
		$this->data['crud'] = $this->crud;
		$this->data['title'] = trans('backpack::crud.preview').' '.$this->crud['entity_name'];

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::show', $this->data);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		// SECURITY:
		// if delete_permission is false, abort
		if (isset($this->crud['delete_permission']) && !$this->crud['delete_permission']) {
			abort(403, trans('backpack::crud.unauthorized_access'));
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
	public function reorder($lang = false)
	{
		// if reorder_table_permission is false, abort
		if (isset($this->crud['reorder_permission']) && !$this->crud['reorder_permission']) {
			abort(403, 'Not allowed.');
		}

		if ($lang == false)
		{
			$lang = \Lang::locale();
		}

		// get all results for that entity
		$model = $this->crud['model'];
		if (property_exists($model, 'translatable'))
		{
			$this->data['entries'] = $model::where('translation_lang', $lang)->get();
			$this->data['languages'] = \Backpack\LangFileManager\app\Models\Language::all();
			$this->data['active_language'] = $lang;
		}
		else
		{
			$this->data['entries'] = $model::all();
		}
		$this->data['crud'] = $this->crud;
		$this->data['title'] = trans('backpack::crud.reorder').' '.$this->crud['entity_name'];

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
		// if reorder_table_permission is false, abort
		if (isset($this->crud['reorder_permission']) && !$this->crud['reorder_permission']) {
			abort(403, 'Not allowed.');
		}

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
	 * Used with AJAX in the list view (datatables) to show extra information about that row that didn't fit in the table.
	 * It defaults to showing all connected translations and their CRUD buttons.
	 *
	 * It's enabled by:
	 * - setting the $crud['details_row'] variable to true;
	 * - adding the details route for the entity; ex: Route::get('page/{id}/details', 'PageCrudController@showDetailsRow');
	 */
	public function showDetailsRow($id)
	{
		// get the info for that entry
		$model = $this->crud['model'];
		$this->data['entry'] = $model::find($id);
		$this->data['entry']->addFakes($this->getFakeColumnsAsArray());
		$this->data['original_entry'] = $this->data['entry'];
		$this->data['crud'] = $this->crud;

		if (property_exists($model, 'translatable'))
		{
			$this->data['translations'] = $this->data['entry']->translations();

			// create a list of languages the item is not translated in
			$this->data['languages'] = \Backpack\LangFileManager\app\Models\Language::all();
			$this->data['languages_already_translated_in'] = $this->data['entry']->translationLanguages();
			$this->data['languages_to_translate_in'] = $this->data['languages']->diff($this->data['languages_already_translated_in']);
			$this->data['languages_to_translate_in'] = $this->data['languages_to_translate_in']->reject(function ($item) {
			    return $item->abbr == \Lang::locale();
			});
		}

		// load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
		return view('crud::details_row', $this->data);
	}


	/**
	 * Duplicate an existing item into another language and open it for editing.
	 */
	public function translateItem($id, $lang)
	{
		$model = $this->crud['model'];
		$this->data['entry'] = $model::find($id);
		// check if there isn't a translation already
		$existing_translation = $this->data['entry']->translation($lang);

		if ($existing_translation)
		{
			$new_entry = $existing_translation;
		}
		else
		{
			// get the info for that entry
			$new_entry_attributes = $this->data['entry']->getAttributes();
			$new_entry_attributes['translation_lang'] = $lang;
			$new_entry_attributes['translation_of'] = $id;
			$new_entry_attributes = array_except($new_entry_attributes, 'id');

			$new_entry = $model::create($new_entry_attributes);
		}

		// redirect to the edit form for that translation
		return redirect(str_replace($id, $new_entry->id, str_replace('translate/'.$lang, 'edit', \Request::url())));
	}



	/**
	 * COMMODITY FUNCTIONS
	 */


	/**
	 * Refactor the request array to something that can be passed to the model's create or update function.
	 * The resulting array will only include the fields that are stored in the database and their values,
	 * plus the '_token' and 'redirect_after_save' variables.
	 *
	 * @param 	Request 	$request - everything that was sent from the form, usually \Request::all()
	 * @return 	array
	 */
	protected function compactFakeFields($request) {

		$this->prepareFields();

		$fake_field_columns_to_encode = [];

		// go through each defined field
		foreach ($this->crud['fields'] as $k => $field) {
			// if it's a fake field
			if (isset($this->crud['fields'][$k]['fake']) && $this->crud['fields'][$k]['fake']==true) {
				// add it to the request in its appropriate variable - the one defined, if defined
				if (isset($this->crud['fields'][$k]['store_in'])) {
					$request[$this->crud['fields'][$k]['store_in']][$this->crud['fields'][$k]['name']] = $request[$this->crud['fields'][$k]['name']];

					$remove_fake_field = array_pull($request, $this->crud['fields'][$k]['name']);
					if(!in_array($this->crud['fields'][$k]['store_in'], $fake_field_columns_to_encode, true)){
				        array_push($fake_field_columns_to_encode, $this->crud['fields'][$k]['store_in']);
				    }
				}
				else //otherwise in the one defined in the $crud variable
				{
					$request['extras'][$this->crud['fields'][$k]['name']] = $request[$this->crud['fields'][$k]['name']];

					$remove_fake_field = array_pull($request, $this->crud['fields'][$k]['name']);
					if(!in_array('extras', $fake_field_columns_to_encode, true)){
				        array_push($fake_field_columns_to_encode, 'extras');
				    }
				}
			}
		}

		// json_encode all fake_value columns in the database, so they can be properly stored and interpreted
		if (count($fake_field_columns_to_encode)) {
			foreach ($fake_field_columns_to_encode as $key => $value) {
				$request[$value] = json_encode($request[$value]);
			}
		}

		// if there are no fake fields defined, this will just return the original Request in full
		// since no modifications or additions have been made to $request
		return $request;
	}


	/**
	 * Returns an array of database columns names, that are used to store fake values.
	 * Returns ['extras'] if no columns have been found.
	 *
	 */
	protected function getFakeColumnsAsArray() {

		$this->prepareFields();

		$fake_field_columns_to_encode = [];

		foreach ($this->crud['fields'] as $k => $field) {
			// if it's a fake field
			if (isset($this->crud['fields'][$k]['fake']) && $this->crud['fields'][$k]['fake']==true) {
				// add it to the request in its appropriate variable - the one defined, if defined
				if (isset($this->crud['fields'][$k]['store_in'])) {
					if(!in_array($this->crud['fields'][$k]['store_in'], $fake_field_columns_to_encode, true)){
				        array_push($fake_field_columns_to_encode, $this->crud['fields'][$k]['store_in']);
				    }
				}
				else //otherwise in the one defined in the $crud variable
				{
					if(!in_array('extras', $fake_field_columns_to_encode, true)){
				        array_push($fake_field_columns_to_encode, 'extras');
				    }
				}
			}
		}

		if (!count($fake_field_columns_to_encode)) {
			return ['extras'];
		}

		return $fake_field_columns_to_encode;
	}


	// If it's not an array of array and it's a simple array, create a proper array of arrays for it
	protected function prepareColumns()
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

	/**
	 * Prepare the fields to be shown, stored, updated or created.
	 *
	 * Makes sure $this->crud['fields'] is in the proper format (array of arrays);
	 * Makes sure $this->crud['fields'] also contains the id of the current item;
	 * Makes sure $this->crud['fields'] also contains the values for each field;
	 *
	 */
	protected function prepareFields($entry = false)
	{
		// if the fields have been defined separately for create and update, use that
		if (!isset($this->crud['fields']))
		{
			if (isset($this->crud['create_fields']))
			{
				$this->crud['fields'] = $this->crud['create_fields'];
			}
			elseif (isset($this->crud['update_fields']))
			{
				$this->crud['fields'] = $this->crud['update_fields'];
			}
		}

		// PREREQUISITES CHECK:
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

		// if no field type is defined, assume the "text" field type
		foreach ($this->crud['fields'] as $k => $field) {
				if (!isset($this->crud['fields'][$k]['type']))
					$this->crud['fields'][$k]['type'] = 'text';
			}

		// if an entry was passed, we're preparing for the update form, not create
		if ($entry) {
			// put the values in the same 'fields' variable
			$fields = $this->crud['fields'];

			foreach ($fields as $k => $field) {
				// set the value
				if (!isset($this->crud['fields'][$k]['value']))
				{
					$this->crud['fields'][$k]['value'] = $entry->$field['name'];
				}
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
