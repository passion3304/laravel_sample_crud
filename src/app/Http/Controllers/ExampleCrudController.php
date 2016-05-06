<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;

// VALIDATION: change the requests to match your own file names if you need form validation
use Backpack\CRUD\app\Http\Requests\CrudRequest as StoreRequest;
use Backpack\CRUD\app\Http\Requests\CrudRequest as UpdateRequest;

class ExampleCrudController extends CrudController {

	public function __construct() {
        parent::__construct();

		/*
	    |--------------------------------------------------------------------------
	    |                                   API
	    |--------------------------------------------------------------------------
	    */


		// USAGE LEVEL 1 - ALWAYS	================================================== LEVEL 1
        $this->crud->setModel("App\Models\Example");
        $this->crud->setRoute("admin/example");
        $this->crud->setEntityNameStrings("example", "examples");

        // TODO: $this->crud->setColumns();
        // TODO: $this->crud->setFields();  // for both create and update



		// USAGE LEVEL 2 - OFTEN	================================================== LEVEL 2

        // TODO: $this->crud->setCreateFields(); // overwrite the create fields with this
	    // TODO: $this->crud->setUpdateFields(); // overwrite the update fields with this

	    // TODO: $this->crud->addColumn(); // add a single column, at the end of the stack
	    // TODO: $this->crud->removeColumn(); // remove a column from the stack
	    // TODO: $this->crud->replaceColumn(); // replace a column from the stack with another one

	    // TODO: $this->crud->addField();
	    // TODO: $this->crud->addCreateField();
	    // TODO: $this->crud->addUpdateField();
	    // TODO: $this->crud->removeField();
	    // TODO: $this->crud->removeCreateField();
	    // TODO: $this->crud->removeUpdateField();
	    // TODO: $this->crud->replaceField();
	    // TODO: $this->crud->replaceCreateField();
	    // TODO: $this->crud->replaceUpdateField();

        // TODO: $this->crud->setReorderMaxLevel();
	    // TODO: $this->crud->setDetailsRow();
        // TODO: $this->crud->setListEntries(); // in the list view by default it fetches all entries; this allows you to replace it with whatever you want, say with $model->where('smth', 1)->get()
        // TODO: $this->crud->setReorderEntries(); // same thing, for the reorder view

        // TODO: $this->crud->setListPermission(); // instead of view_table_permission
	    // TODO: $this->crud->setAddPermission();
	    // TODO: $this->crud->setDeletePermission();
	    // TODO: $this->crud->setReorderPermission();

        // TODO: $this->crud->query() / clause /smth like that
        // TODO: $this->crud->orderBy()
        // TODO: $this->crud->groupBy()



		// USAGE LEVEL 3 - SOMETIMES	==============================================  LEVEL 3

        // TODO: $this->crud->setButtons(); // default includes edit and delete, with their name, icon, permission, link and class (btn-default)
	    // TODO: $this->crud->addButton();
	    // TODO: $this->crud->removeButton();
	    // TODO: $this->crud->replaceButton();

		// $this->crud->getEntry($entry_id);
		// $this->crud->getEntries();

		// $this->crud->hasPermissionOrFail('add');
		// $this->crud->create($entry_request);
		// $this->crud->update($entry_id, $entry_request);
		// $this->crud->delete($entry_id);



		// USAGE LEVEL 4 - RARELY	==================================================  LEVEL 4

        // $this->crud->getCreateFields();
		// $this->crud->getUpdateFields();



		// USAGE LEVEL 5 - ALMOST NEVER	==============================================  LEVEL 5

		// $this->crud->updateTreeOrder($all_entries);




        // ------------------------
		// MEANWHILE THIS WILL WORK
		// ------------------------


        $this->crud->reorder = true;
		$this->crud->reorder_label = "name";
		$this->crud->reorder_max_level = 3;
		$this->crud->details_row = true;
		// $this->crud->permissions = ['add', 'list', 'edit', 'delete', 'show'];

        $this->crud->columns = [
									[
										'name' => 'name',
										'label' => "Example item text"
									],
									[
										'label' => "Parent",
										'type' => 'select',
										'name' => 'parent_id',
										'entity' => 'parent',
										'attribute' => 'name',
										'model' => "App\Models\Example"
									],
								];
		$this->crud->fields =  [
								[
									'name' => 'name',
									'label' => "Example item text"
								],
								[
									'label' => "Parent",
									'type' => 'select',
									'name' => 'parent_id',
									'entity' => 'parent',
									'attribute' => 'name',
									'model' => "App\Models\Example"
								],
								[
								    'name' => 'type',
								    'label' => "Type",
								    'type' => 'page_or_link'
								],
							];

    }

	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}

	public function update(UpdateRequest $request)
	{
		return parent::updateCrud();
	}

}
