<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use Backpack\CRUD\app\Http\Controllers\CrudController;
// VALIDATION: change the requests to match your own file names if you need form validation
use Backpack\CRUD\app\Http\Requests\CrudRequest as StoreRequest;
use Backpack\CRUD\app\Http\Requests\CrudRequest as UpdateRequest;

class ExampleCrudController extends CrudController
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        |                                   API
        |--------------------------------------------------------------------------
        */

        // USAGE LEVEL 1 - ALWAYS	================================================== LEVEL 1
        $this->crud->setModel("App\Models\Example");
        $this->crud->setRoute('admin/example');
        // $this->crud->setRouteName("admin.example");
        $this->crud->setEntityNameStrings('example', 'examples');

        // $this->crud->setColumns(); // set the columns you want in the table view, either as array of column names, or multidimensional array with all columns detailed with their types
        // TODO: $this->crud->setFields($array_of_arrays, 'update/create/both'); // set fields for both create&update forms
        // $this->crud->setFromDb();  // automatically set fields and columns from your database columns  TODO: rephrase

        // USAGE LEVEL 2 - OFTEN	================================================== LEVEL 2

        // ------ FIELDS (the last parameter is always the form - create/update/both)
        // TODO: $this->crud->addField('name', $options, 'update/create/both');
        // TODO: $this->crud->addFields($array_of_arrays, 'update/create/both');
        // TODO: $this->crud->removeField('name', 'update/create/both');
        // TODO: $this->crud->removeFields($array_of_names, 'update/create/both');
        // TODO: $this->crud->replaceField('name', 'update/create/both');

        // TODO: $this->crud->setRequiredFields(['field_1', 'field_2'], 'update/create/both');
        // TODO: $this->crud->setRequiredField('field_1', 'update/create/both');
        // TODO: $this->crud->getRequiredFields();

        // TODO: $this->crud->setFieldOrder(['field_1', 'field_2', 'field_3'], 'update/create/both');

        // ------ COLUMNS
        // $this->crud->addColumn(); // add a single column, at the end of the stack
        // $this->crud->addColumns(); // add multiple columns, at the end of the stack
        // $this->crud->removeColumn('column_name'); // remove a column from the stack
        // $this->crud->removeColumns(['column_name_1', 'column_name_2']); // remove an array of columns from the stack
        // $this->crud->setColumnDetails('column_name', ['attribute' => 'value']);
        // $this->crud->setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']);
        // TODO: $this->crud->setColumnOrder(['column_1', 'column_2', 'column_3']);

        // ------ FIELDS AND COLUMNS
        // TODO: $this->crud->setLabel('column_name/field_name', 'New Label'); // changes label for columns, create&update fields

        // ------ ACCESS
        // $this->crud->allowAccess('list');
        // $this->crud->allowAccess(['list', 'create', 'delete']);
        // $this->crud->denyAccess('list');
        // $this->crud->denyAccess(['list', 'create', 'delete']);

        // $this->crud->hasAccess('add'); // returns true/false
        // $this->crud->hasAccessOrFail('add'); // throws 403 error

        // ------ REORDER
        // $this->crud->enableReorder('label_name', MAX_TREE_LEVEL);
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('reorder');

        // $this->crud->disableReorder();
        // $this->crud->isReorderEnabled(); // return true/false

        // ------ DETAILS ROW
        // $this->crud->enableDetailsRow();
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('details_row');
        // NOTE: you also need to do overwrite the showDetailsRow($id) method in your EntityCrudController to show whatever you'd like in the details row OR overwrite the views/backpack/crud/details_row.blade.php

        //  $this->crud->disableDetailsRow();

        // ------ ADVANCED QUERIES
        // $this->crud->addClause('active');
        // $this->crud->addClause('type', 'car');
        // $this->crud->addClause('where', 'name', '==', 'car');
        // $this->crud->addClause('whereName', 'car');
        // $this->crud->addClause('whereHas', 'posts', function($query) {
        //     $query->activePosts();
        // });
        // $this->crud->orderBy();
        // $this->crud->groupBy();
        // $this->crud->limit();

        // USAGE LEVEL 3 - SOMETIMES	==============================================  LEVEL 3

        // TODO: $this->crud->setButtons(); // default includes edit and delete, with their name, icon, permission, link and class (btn-default)
        // TODO: $this->crud->addButton();
        // TODO: $this->crud->removeButton();
        // TODO: $this->crud->replaceButton();

        // USAGE LEVEL 4 - RARELY	==================================================  LEVEL 4

        // $this->crud->getEntry($entry_id);
        // $this->crud->getEntries();

        // $this->crud->getFields('create/update/both');

        // $this->crud->create($entry_request);
        // $this->crud->update($entry_id, $entry_request);
        // $this->crud->delete($entry_id);

        // USAGE LEVEL 5 - ALMOST NEVER	==============================================  LEVEL 5

        // $this->crud->updateTreeOrder($all_entries);

        // ------------------------
        // MEANWHILE THIS WILL WORK
        // ------------------------

        $this->crud->reorder = true;
        $this->crud->reorder_label = 'name';
        $this->crud->reorder_max_level = 3;
        $this->crud->details_row = true;
        // $this->crud->permissions = ['add', 'list', 'edit', 'delete', 'show'];

        $this->crud->columns = [
                                    [
                                        'name'  => 'name',
                                        'label' => 'Example item text',
                                    ],
                                    [
                                        'label'     => 'Parent',
                                        'type'      => 'select',
                                        'name'      => 'parent_id',
                                        'entity'    => 'parent',
                                        'attribute' => 'name',
                                        'model'     => "App\Models\Example",
                                    ],
                                ];
        $this->crud->fields = [
                                [
                                    'name'  => 'name',
                                    'label' => 'Example item text',
                                ],
                                [
                                    'label'     => 'Parent',
                                    'type'      => 'select',
                                    'name'      => 'parent_id',
                                    'entity'    => 'parent',
                                    'attribute' => 'name',
                                    'model'     => "App\Models\Example",
                                ],
                                [
                                    'name'  => 'type',
                                    'label' => 'Type',
                                    'type'  => 'page_or_link',
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
