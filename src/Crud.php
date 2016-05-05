<?php

namespace Backpack\CRUD;

class Crud
{
    // --------------
    // CRUD variables
    // --------------
    // These variables are passed to the views.
    // All variables are public, so they can be easily modified from the Controller

    // TODO: translate $entity_name and $entity_name_plural by default, with english fallback
    // TODO: code logic for using either Laravel Authorization or Entrust (whatever one chooses) for permissions

    public $model = "\App\Models\Entity"; // what's the namespace for your entity's model
    public $entity_name = "entry"; // what name will show up on the buttons, in singural (ex: Add entity)
    public $entity_name_plural = "entries"; // what name will show up on the buttons, in plural (ex: Delete 5 entities)
    public $route; // what route have you defined for your entity? used for links.

    public $view_table_permission = true;
    public $add_permission = true;
    public $edit_permission = true;
    public $delete_permission = true;

    public $reorder = true;
    public $reorder_label = true;
    public $reorder_permission = true;
    public $reorder_max_level = 3;

    public $details_row = false;

    public $columns = []; // Define the columns for the table view as an array;
    public $create_fields = []; // Define the fields for the "Add new entry" view as an array;
    public $update_fields = []; // Define the fields for the "Edit entry" view as an array;
    public $fields = []; // Define both create_fields and update_fields in one array; will be overwritten by create_fields and update_fields;

    // TONE FIELDS - TODO: find out what he did with them, replicate or delete
    public $field_types;
    public $query;
    public $title;
    public $subTitle;


    public $custom_buttons = [];
    public $relations = [];
    public $labels = [];
    public $required = [];
    public $sort = [];
    public $state;

    public $buttons = [];
    public $permissions = [];
    public $list_actions = [];

    public $item;


    // ------------
    // CRUD methods
    // ------------
    // These methods are used in CrudController or your EntityCrudController to manipulate the variables above.


    // OTHER METHODS on that model, to replicate keep the same functionality.
    // $this->crud->setEntityName('tag', 'tags');
    // $this->crud->setEntityModel('\App\Models\Entity');
    // $this->crud->setListPermission(); // instead of view_table_permission
    // $this->crud->setAddPermission();
    // $this->crud->setDeletePermission();
    // $this->crud->setReorderPermission();
    // $this->crud->setReorderMaxLevel();
    // $this->crud->setDetailsRow();
    // $this->crud->setRoute();

    // $this->crud->setColumns();

    // $this->crud->setFields();  // for both create and update
    // $this->crud->setCreateFields(); // overwrite the create fields with this
    // $this->crud->setUpdateFields(); // overwrite the update fields with this

    // NEW METHODS

    // $this->crud->addColumn(); // add a single column, at the end of the stack
    // $this->crud->removeColumn(); // remove a column from the stack
    // $this->crud->replaceColumn(); // replace a column from the stack with another one

    // $this->crud->addField();
    // $this->crud->removeField();
    // $this->crud->replaceField();

    // $this->crud->setListEntries(); // in the list view by default it fetches all entries; this allows you to replace it with whatever you want, say with $model->where('smth', 1)->get()
    // $this->crud->setReorderEntries(); // same thing, for the reorder view

    // $this->crud->setButtons(); // default includes edit and delete, with their name, icon, permission, link and class (btn-default)
    // $this->crud->addButton();
    // $this->crud->removeButton();
    // $this->crud->replaceButton();


    // ----------------------------------
    // Miscellaneous functions or methods
    // ----------------------------------


}