<?php

namespace Backpack\CRUD;

class Crud
{
    // --------------
    // CRUD variables
    // --------------
    // These variables are passed to the CRUD views, inside the $crud variable.
    // All variables are public, so they can be modified from your EntityCrudController.
    // All functions and methods are also public, so they can be used in your EntityCrudController to modify these variables.

    // TODO: translate $entity_name and $entity_name_plural by default, with english fallback
    // TODO: code logic for using either Laravel Authorization or Entrust (whatever one chooses) for access

    public $model = "\App\Models\Entity"; // what's the namespace for your entity's model
    public $route; // what route have you defined for your entity? used for links.
    public $entity_name = "entry"; // what name will show up on the buttons, in singural (ex: Add entity)
    public $entity_name_plural = "entries"; // what name will show up on the buttons, in plural (ex: Delete 5 entities)

    public $access = ['list', 'create', 'update', 'delete', /* 'reorder', 'show', 'details' */];

    public $reorder = false;
    public $reorder_label = true;
    public $reorder_permission = true;
    public $reorder_max_level = 3;

    public $details_row = false;

    public $columns = []; // Define the columns for the table view as an array;
    public $create_fields = []; // Define the fields for the "Add new entry" view as an array;
    public $update_fields = []; // Define the fields for the "Edit entry" view as an array;
    public $fields = []; // Define both create_fields and update_fields in one array; will be overwritten by create_fields and update_fields;

    public $query;

    // TONE FIELDS - TODO: find out what he did with them, replicate or delete
    public $field_types = [];

    public $custom_buttons = [];
    public $relations = [];
    public $labels = [];
    public $required = [];
    public $sort = [];

    public $buttons = [''];
    public $list_actions = [];

    public $item;
    public $entry;


    // The following methods are used in CrudController or your EntityCrudController to manipulate the variables above.


    /*
    |--------------------------------------------------------------------------
    |                                   CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * Insert a row in the database.
     *
     * @param  [Request] All input values to be inserted.
     * @return [Eloquent Collection]
     */
    public function create($data)
    {
        $values_to_store = $this->compactFakeFields(\Request::all());
        $item = $this->model->create($values_to_store);

        // if there are any relationships available, also sync those
        $this->syncPivot($item, $data);

        return $item;
    }


    /**
     * Get all fields needed for the ADD NEW ENTRY form.
     *
     * @return [array] The fields with attributes and fake attributes.
     */
    public function getCreateFields()
    {
        return $this->prepareFields(empty($this->create_fields)?$this->fields:$this->create_fields);
    }




    public function syncPivot($model, $data)
    {
        foreach ($this->relations as $key => $relation)
        {
            if ($relation['pivot']){
                $model->{$relation['name']}()->sync($data[$key]);

                foreach($relation['pivotFields'] as $pivotField){
                   foreach($data[$pivotField] as $pivot_id =>  $field){
                     $model->{$relation['name']}()->updateExistingPivot($pivot_id, [$pivotField => $field]);
                   }
                }
            }
        }
    }


   /*
    |--------------------------------------------------------------------------
    |                                   READ
    |--------------------------------------------------------------------------
    */

    /**
     * Find and retrieve an entry in the database or fail.
     *
     * @param  [int] The id of the row in the db to fetch.
     * @return [Eloquent Collection] The row in the db.
     */
    public function getEntry($id)
    {
        $entry = $this->model->findOrFail($id);
        return $entry->withFakes();
    }


    /**
     * Get all entries from the database.
     *
     * @return [Collection of your model]
     */
    public function getEntries()
    {
        $entries = $this->query->get();

        // add the fake columns for each entry
        foreach ($entries as $key => $entry) {
            $entry->addFakes($this->getFakeColumnsAsArray());
        }

        return $entries;
    }


    /**
     * Get the fields for the create or update forms.
     *
     * @param  [form] create / update / both - defaults to 'both'
     * @param  [integer] the ID of the entity to be edited in the Update form
     * @return [array] all the fields that need to be shown and their information
     */
    public function getFields($form, $id = false)
    {
        switch ($form) {
            case 'create':
                return $this->getCreateFields();
                break;

            case 'update':
                return $this->getUpdateFields($id);
                break;

            default:
                return $this->getCreateFields();
                break;
        }
    }



   /*
    |--------------------------------------------------------------------------
    |                                   UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Update a row in the database.
     *
     * @param  [Int] The entity's id
     * @param  [Request] All inputs to be updated.
     * @return [Eloquent Collection]
     */
    public function update($id, $data)
    {
        $item = $this->model->findOrFail($id);
        $updated = $item->update($this->compactFakeFields($data));

        if ($updated) $this->syncPivot($item, $data);

        return $item;
    }


    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  [integer] The id of the entry that is being edited.
     * @return [array] The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id)
    {
        $fields = $this->prepareFields(empty($this->update_fields)?$this->fields:$this->update_fields);
        $entry = $this->getEntry($id);

        foreach ($fields as $k => $field) {
            // set the value
            if (!isset($fields[$k]['value']))
            {
                $fields[$k]['value'] = $entry->$field['name'];
            }
        }

        // always have a hidden input for the entry id
        $fields[] = array(
                        'name' => 'id',
                        'value' => $entry->id,
                        'type' => 'hidden'
                    );

        return $fields;
    }


    /**
     * Change the order and parents of the given elements, according to the NestedSortable AJAX call.
     *
     * @param  [Request] The entire request from the NestedSortable AJAX Call.
     * @return [integer] The number of items whose position in the tree has been changed.
     */
    public function updateTreeOrder($request) {
        $count = 0;

        foreach ($request as $key => $entry) {
            if ($entry['item_id'] != "" && $entry['item_id'] != null) {
                $item = $this->model->find($entry['item_id']);
                $item->parent_id = $entry['parent_id'];
                $item->depth = $entry['depth'];
                $item->lft = $entry['left'];
                $item->rgt = $entry['right'];
                $item->save();

                $count++;
            }
        }

        return $count;
    }



   /*
    |--------------------------------------------------------------------------
    |                                   DELETE
    |--------------------------------------------------------------------------
    */

    /**
     * Delete a row from the database.
     *
     * @param  [int] The id of the item to be deleted.
     * @return [bool] Deletion confirmation.
     *
     * TODO: should this delete items with relations to it too?
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }





   /*
    |--------------------------------------------------------------------------
    |                                   CRUD ACCESS
    |--------------------------------------------------------------------------
    */

    public function allowAccess($access)
    {
        // $this->addButtons((array)$access);
        return $this->access = array_merge(array_diff((array)$access, $this->access), $this->access);
    }

    public function denyAccess($access)
    {
        // $this->removeButtons((array)$access);
        return $this->access = array_diff($this->access, (array)$access);
    }

    /**
     * Check if a permission is enabled for a Crud Panel. Return false if not.
     *
     * @param  [string] Permission.
     * @return boolean
     */
    public function hasAccess($permission)
    {
        if (!in_array($permission, $this->access))
        {
            return false;
        }
        return true;
    }

    /**
     * Check if a permission is enabled for a Crud Panel. Fail if not.
     *
     * @param  [string] Permission.
     * @return boolean
     */
    public function hasAccessOrFail($permission)
    {
        if (!in_array($permission, $this->access))
        {
            abort(403, trans('backpack::crud.unauthorized_access'));
        }
    }



    /*
    |--------------------------------------------------------------------------
    |                               CRUD MANIPULATION
    |--------------------------------------------------------------------------
    */



    // ------------------------------------------------------
    // BASICS - model, route, entity_name, entity_name_plural
    // ------------------------------------------------------

    /**
     * This function binds the CRUD to its corresponding Model (which extends Eloquent).
     * All Create-Read-Update-Delete operations are done using that Eloquent Collection.
     *
     * @param [string] Full model namespace. Ex: App\Models\Article
     */
    public function setModel($model_namespace)
    {
        if (!class_exists($model_namespace)) throw new \Exception('This model does not exist.', 404);

        $this->model = new $model_namespace();
        $this->query = $this->model->select('*');

        // $this->setFromDb(); // i think that, by default, the auto-fields functionality should be disabled; otherwise, the workflow changes from "set the fields i need" to "update this crud with whatever i need"; which i personally don't like, because it's more hacky and it assumes you should see what the default offers you, then adapt; I propose we set wether the auto-fields functionality is run for panels with a config variable; the config file should be backpack/crud.php and the variable name should be "autoSetFromDb".
    }

    /**
     * Get the corresponding Eloquent Model for the CrudController, as defined with the setModel() function;
     *
     * @return [Eloquent Collection]
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the route for this CRUD.
     * Ex: admin/article
     *
     * @param [string] Route name.
     * @param [array] Parameters.
     */
    public function setRoute($route)
    {
        $this->route = $route;
        $this->initButtons();
    }

    /**
     * Set the route for this CRUD using the route name.
     * Ex: admin.article
     *
     * @param [string] Route name.
     * @param [array] Parameters.
     */
    public function setRouteName($route, $parameters = [])
    {
        $complete_route = $route.'.index';

        if (!\Route::has($complete_route)) throw new \Exception('There are no routes for this route name.', 404);

        $this->route = route($complete_route, $parameters);
        $this->initButtons();
    }

    /**
     * Get the current CrudController route.
     *
     * Can be defined in the CrudController with:
     * - $this->crud->setRoute('admin/article')
     * - $this->crud->setRouteName('admin.article')
     * - $this->crud->route = "admin/article"
     *
     * @return [string]
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the entity name in singular and plural.
     * Used all over the CRUD interface (header, add button, reorder button, breadcrumbs).
     *
     * @param [string] Entity name, in singular. Ex: article
     * @param [string] Entity name, in plural. Ex: articles
     */
    public function setEntityNameStrings($singular, $plural) {
        $this->entity_name = $singular;
        $this->entity_name_plural = $plural;
    }




    // ------------
    // COLUMNS
    // ------------

    /**
     * Add a bunch of column names and their details to the CRUD object.
     *
     * @param [array or multi-dimensional array]
     */
    public function setColumns($columns)
    {
        // clear any columns already set
        $this->columns = [];

        // if array, add a column for each of the items
        if (is_array($columns) && count($columns)) {
            foreach ($columns as $key => $column) {
                // if label and other details have been defined in the array
                if (is_array($columns[0])) {
                    $this->addColumn($column);
                }
                else
                {
                    $this->addColumn([
                                    'name' => $column,
                                    'label' => ucfirst($column),
                                    'type' => 'text'
                                ]);
                }
            }
        }

        if (is_string($columns)) {
            $this->addColumn([
                                'name' => $columns,
                                'label' => ucfirst($columns),
                                'type' => 'text'
                                ]);
        }

        // This was the old setColumns() function, and it did not work:
        // $this->columns = array_filter(array_map([$this, 'addDefaultTypeToColumn'], $columns));
    }

    /**
     * Add a column at the end of to the CRUD object's "columns" array.
     *
     * @param [string or array]
     */
    public function addColumn($column)
    {
        // make sure the column has a type
        $column_with_details = $this->addDefaultTypeToColumn($column);

        // make sure the column has a label
        $column_with_details = $this->addDefaultLabel($column);

        return array_filter($this->columns[] = $column_with_details);
    }

    /**
     * Add multiple columns at the end of the CRUD object's "columns" array.
     *
     * @param [array of columns]
     */
    public function addColumns($columns)
    {
        if (count($columns)) {
            foreach ($columns as $key => $column) {
                $this->addColumn($column);
            }
        }
    }

    /**
     * Add the default column type to the given Column, inferring the type from the database column type.
     *
     * @param [column array]
     */
    public function addDefaultTypeToColumn($column)
    {
        if (array_key_exists('name', (array)$column))
        {
            $default_type = $this->getFieldTypeFromDbColumnType($column['name']);
            return array_merge(['type' => $default_type], $column);
        }

        return false;
    }

    /**
     * If a field or column array is missing the "label" attribute, an ugly error would be show.
     * So we add the field Name as a label - it's better than nothing.
     *
     * @param [field or column]
     */
    public function addDefaultLabel($array) {
        if (!array_key_exists('label', (array)$array) && array_key_exists('name', (array)$array)) {
            $array = array_merge(['label' => ucfirst($this->makeLabel($array['name']))], $array);
            return $array;
        }

        return $array;
    }

    /**
     * Remove multiple columns from the CRUD object using their names.
     *
     * @param  [column array]
     */
    public function removeColumns($columns)
    {
        $this->columns = $this->remove('columns', $columns);
    }

    /**
     * Remove a column from the CRUD object using its name.
     *
     * @param  [column array]
     */
    public function removeColumn($column)
    {
        return $this->removeColumns([$column]);
    }

    /**
     * Change attributes for multiple columns.
     *
     * @param [columns arrays]
     * @param [attributes and values array]
     */
    public function setColumnsDetails($columns, $attributes)
    {
        $this->sync('columns', $columns, $attributes);
    }

    /**
     * Change attributes for a certain column.
     *
     * @param [string] Column name.
     * @param [attributes and values array]
     */
    public function setColumnDetails($column, $attributes)
    {
        $this->setColumnsDetails([$column], $attributes);
    }




    // ------------
    // FIELDS
    // ------------

    // TODO: $this->crud->setFields();  // for both create and update
    // TODO: $this->crud->setCreateFields(); // overwrite the create fields with this
    // TODO: $this->crud->setUpdateFields(); // overwrite the update fields with this

    // TODO: $this->crud->addField();
    // TODO: $this->crud->removeField();
    // TODO: $this->crud->replaceField();



    // ----------------
    // ADVANCED QUERIES
    // ----------------


    /**
     * Add another clause to the query (for ex, a WHERE clause).
     *
     * Examples:
     * // $this->crud->addClause('active');
     * $this->crud->addClause('type', 'car');
     * $this->crud->addClause('where', 'name', '==', 'car');
     * $this->crud->addClause('whereName', 'car');
     * $this->crud->addClause('whereHas', 'posts', function($query) {
     *     $query->activePosts();
     *     });
     *
     * @param [type]
     */
    public function addClause($function)
    {
        return call_user_func_array([$this->query, $function], array_slice(func_get_args(), 1, 3));
    }

    /**
     * Order the results of the query in a certain way.
     *
     * @param  [type]
     * @param  string
     * @return [type]
     */
    public function orderBy($field, $order = 'asc')
    {
        return $this->query->orderBy($field, $order);
    }

    /**
     * Group the results of the query in a certain way.
     *
     * @param  [type]
     * @return [type]
     */
    public function groupBy($field)
    {
        return $this->query->groupBy($field);
    }

    /**
     * Limit the number of results in the query.
     *
     * @param  [number]
     * @return [type]
     */
    public function limit($number)
    {
        return $this->query->limit($number);
    }



    // ------------
    // BUTTONS
    // ------------

    // TODO: $this->crud->setButtons(); // default includes edit and delete, with their name, icon, permission, link and class (btn-default)
    // TODO: $this->crud->addButton();
    // TODO: $this->crud->removeButton();
    // TODO: $this->crud->replaceButton();



    // ------------------------------------------------------
    // AUTO-SET-FIELDS-AND-COLUMNS FUNCTIONALITY
    // ------------------------------------------------------


    /**
     * For a simple CRUD Panel, there should be no need to add/define the fields.
     * The public columns in the database will be converted to be fields.
     *
     */
    public function setFromDb()
    {
        $this->getDbColumnTypes();

        array_map(function($field) {
            $this->labels[$field] = $this->makeLabel($field);

            $this->fields[] =  [
                                'name' => $field,
                                'label' => ucfirst($field),
                                'value' => '', 'default' => $this->field_types[$field]['default'],
                                'type' => $this->getFieldTypeFromDbColumnType($field),
                                'values' => [],
                                'attributes' => []
                                ];

            if (!in_array($field, $this->model->getHidden()))
            {
                 $this->columns[] = [
                                    'name' => $field,
                                    'label' => ucfirst($field),
                                    'type' => $this->getFieldTypeFromDbColumnType($field)
                                    ];
            }

        }, $this->getDbColumnsNames());
    }


    /**
     * Get all columns from the database for that table.
     *
     * @return [array]
     */
    public function getDbColumnTypes()
    {
        foreach (\DB::select(\DB::raw('SHOW COLUMNS FROM '.$this->model->getTable())) as $column)
        {
            $this->field_types[$column->Field] = ['type' => trim(preg_replace('/\(\d+\)(.*)/i', '', $column->Type)), 'default' => $column->Default];
        }

        return $this->field_types;
    }


    /**
     * Intuit a field type, judging from the database column type.
     *
     * @param  [string] Field name.
     * @return [string] Fielt type.
     */
    public function getFieldTypeFromDbColumnType($field)
    {
        if (!array_key_exists($field, $this->field_types)) return 'text';

        if ($field == 'password') return 'password';

        if ($field == 'email') return 'email';

        switch ($this->field_types[$field]['type'])
        {
            case 'int':
            case 'smallint':
            case 'mediumint':
            case 'longint':
                return 'number';
            break;

            case 'string':
            case 'varchar':
            case 'set':
                return 'text';
            break;

            // case 'enum':
            //     return 'enum';
            // break;

            case 'tinyint':
                return 'active';
            break;

            case 'text':
            case 'mediumtext':
            case 'longtext':
                return 'textarea';
            break;

            case 'date':
                return 'date';
            break;

            case 'datetime':
            case 'timestamp':
                return 'datetime';
            break;
            case 'time':
                return 'time';
            break;

            default:
                return 'text';
            break;
        }
    }


    // TODO Tone: Please describe.
    public function makeLabel($value)
    {
        return trim(preg_replace('/(id|at|\[\])$/i', '', ucfirst(str_replace('_', ' ', $value))));
    }


    /**
     * Get the database column names, in order to figure out what fields/columns to show in the auto-fields-and-columns functionality.
     *
     * @return [array] Database column names as an array.
     */
    public function getDbColumnsNames()
    {
        // Automatically-set columns should be both in the database, and in the $fillable variable on the Eloquent Model
        $columns = \Schema::getColumnListing($this->model->getTable());
        $fillable = $this->model->getFillable();

        if (!empty($fillable)) $columns = array_intersect($columns, $fillable);

        // but not updated_at, deleted_at
        return array_values(array_diff($columns, [$this->model->getKeyName(), 'updated_at', 'deleted_at']));
    }







    // -----------------
    // Commodity methods
    // -----------------


    /**
     * Prepare the fields to be shown, stored, updated or created.
     *
     * Makes sure $this->crud->fields is in the proper format (array of arrays);
     * Makes sure $this->crud->fields also contains the id of the current item;
     * Makes sure $this->crud->fields also contains the values for each field;
     *
     */
    public function prepareFields($fields = false)
    {
        // if no field type is defined, assume the "text" field type
        foreach ($fields as $k => $field) {
                if (!isset($fields[$k]['type'])) {
                    $fields[$k]['type'] = 'text';
                }
            }

        return $fields;
    }



    /**
     * Refactor the request array to something that can be passed to the model's create or update function.
     * The resulting array will only include the fields that are stored in the database and their values,
     * plus the '_token' and 'redirect_after_save' variables.
     *
     * @param   Request     $request - everything that was sent from the form, usually \Request::all()
     * @return  array
     */
    public function compactFakeFields($request) {

        // $this->prepareFields();

        $fake_field_columns_to_encode = [];

        // go through each defined field
        foreach ($this->fields as $k => $field) {
            // if it's a fake field
            if (isset($this->fields[$k]['fake']) && $this->fields[$k]['fake'] == true) {
                // add it to the request in its appropriate variable - the one defined, if defined
                if (isset($this->fields[$k]['store_in'])) {
                    $request[$this->fields[$k]['store_in']][$this->fields[$k]['name']] = $request[$this->fields[$k]['name']];

                    $remove_fake_field = array_pull($request, $this->fields[$k]['name']);
                    if (!in_array($this->fields[$k]['store_in'], $fake_field_columns_to_encode, true)) {
                        array_push($fake_field_columns_to_encode, $this->fields[$k]['store_in']);
                    }
                } else //otherwise in the one defined in the $crud variable
                {
                    $request['extras'][$this->fields[$k]['name']] = $request[$this->fields[$k]['name']];

                    $remove_fake_field = array_pull($request, $this->fields[$k]['name']);
                    if (!in_array('extras', $fake_field_columns_to_encode, true)) {
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
    public function getFakeColumnsAsArray() {

        // $this->prepareFields();

        $fake_field_columns_to_encode = [];

        foreach ($this->fields as $k => $field) {
            // if it's a fake field
            if (isset($this->fields[$k]['fake']) && $this->fields[$k]['fake'] == true) {
                // add it to the request in its appropriate variable - the one defined, if defined
                if (isset($this->fields[$k]['store_in'])) {
                    if (!in_array($this->fields[$k]['store_in'], $fake_field_columns_to_encode, true)) {
                        array_push($fake_field_columns_to_encode, $this->fields[$k]['store_in']);
                    }
                } else //otherwise in the one defined in the $crud variable
                {
                    if (!in_array('extras', $fake_field_columns_to_encode, true)) {
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








    // ----------------------------------
    // Miscellaneous functions or methods
    // ----------------------------------













    // ------------
    // TONE FUNCTIONS - UNDOCUMENTED, UNTESTED, UNUSED IN CONTROLLERS/VIEWS
    // ------------
    //
    // TODO:
    // - figure out if they are really needed
    // - comments inside the function to explain how they work
    // - write docblock for them
    // - place in the correct section above (CREATE, READ, UPDATE, DELETE, ACCESS, MANIPULATION)



    public function addButton($button)
    {
        array_unshift($this->buttons, $button);
    }

    public function buttons()
    {
        return $this->buttons;
    }

    public function addCustomButton($button)
    {
        array_unshift($this->customButtons, $button);
    }

    public function customButtons()
    {
        return $this->customButtons;
    }

    public function showButtons()
    {
        return !empty($this->buttons) && !(count($this->buttons) == 1 && array_key_exists('add', $this->buttons));
    }

    public function initButtons()
    {
        $this->buttons = [
            'add' => ['route' => "{$this->route}/create", 'label' => trans('crud::crud.buttons.add'), 'class' => '', 'hide' => [], 'icon' => 'fa-plus-circle', 'extra' => []],
            'view' => ['route' => "{$this->route}/%d", 'label' => trans('crud::crud.buttons.view'), 'class' => '', 'hide' => [], 'icon' => 'fa-eye', 'extra' => []],
            'edit' => ['route' => "{$this->route}/%d/edit", 'label' => trans('crud::crud.buttons.edit'), 'class' => '', 'hide' => [], 'icon' => 'fa-edit', 'extra' => []],
            'delete' => ['route' => "{$this->route}/%d", 'label' => trans('crud::crud.buttons.delete'), 'class' => '', 'hide' => [], 'icon' => 'fa-trash', 'extra' => ['data-confirm' => trans('crud::crud.confirm.delete'), 'data-type' => 'delete']],
        ];
    }

    public function removeButtons($buttons)
    {
        foreach ($buttons as $button)
        {
            unset($this->buttons[$button]);
        }

        return $this->buttons;
    }








    public function getColumns()
    {
        return $this->sort('columns');
    }

    public function orderColumns($order)
    {
        $this->setSort('columns', (array)$order);
    }






    public function setFields($fields)
    {
        $this->addMultiple('fields', $fields);
    }

    // [name, label, value, default, type, required, hint, values[id => value], attributes[class, id, data-, for editor: data-config="basic|medium|full"], callback => [$this, 'methodName'], callback_create => [$this, 'methodName'], callback_edit => [$this, 'methodName'], callback_view => [$this, 'methodName']]
    public function addField($field)
    {
        return $this->add('fields', $field);
    }

    public function updateFields($fields, $attributes)
    {
        $this->sync('fields', $fields, $attributes);
    }

    public function removeFields($fields)
    {
        $this->fields = $this->remove('fields', $fields);
        $this->removeColumns($fields);
    }

    public function setCreateFields($fields)
    {
        $this->addMultiple('create_fields', $fields);
    }

    public function addCreateField($field)
    {
       return $this->add('create_fields', $field);
    }

     public function setUpdateFields($fields)
    {
        $this->addMultiple('update_fields', $fields);
    }

    public function addUpdateField($field)
    {
        return $this->add('update_fields', $field);
    }

    public function fields()
    {
        if (!$this->item && !empty($this->create_fields))
        {
            $this->syncRelations('create_fields');

            return $this->create_fields;
        }

        if ($this->item && !empty($this->update_fields))
        {
            $this->syncRelations('update_fields');
            $this->addFieldsValue();

            return $this->update_fields;
        }

        $this->syncRelations('fields');
        $this->addFieldsValue();

        return $this->sort('fields');
    }

    public function orderFields($order)
    {
        $this->setSort('fields', (array)$order);
    }


    public function syncField($field)
    {
        if (array_key_exists('name', (array)$field)) return array_merge(['type' => $this->getFieldTypeFromDbColumnType($field['name']), 'value' => '', 'default' => null, 'values' => [], 'attributes' => []], $field);

        return false;
    }






    public function label($item, $label)
    {
        $this->labels[$item] = $label;
    }

    public function labels()
    {
        return $this->labels;
    }

    /**
     * Adds a required => true attribute to each field, so that the required asterisc will show up in the create/update forms.
     * TODO: make this work, by editing the $this->fields variable.
     *
     * @param [string or array of strings]
     */
    public function setRequiredFields($fields)
    {
        $this->required = array_merge($this->required, (array)$fields);
    }

    /**
     * Adds a required => true attribute to this field, so that the required asteris will show up in the create/update forms.
     *
     * @param [string]
     */
    public function setRequiredField($field)
    {
        return $this->setRequiredFields($field);
    }

    /**
     * Get the required fields.
     * TODO: make this work after making setRequiredFields() work.
     *
     * @return [array]
     */
    public function getRequired()
    {
        return $this->required;
    }










    // iti pune valorile pe field-uri la EDIT
    public function addFieldsValue()
    {
        if ($this->item)
        {
            $fields = !empty($this->update_fields) ? 'update_fields' : 'fields';

            foreach ($this->{$fields} as $key => $field)
            {
                if (array_key_exists($field['name'], $this->relations) && $this->relations[$field['name']]['pivot']) $this->{$fields}[$key]['value'] = $this->item->{$this->relations[$field['name']]['name']}()->lists($this->relations[$field['name']]['model']->getKeyName())->toArray();
                    else $this->{$fields}[$key]['value'] = $this->item->{$field['name']};
            }
        }
    }

    public function add($entity, $field)
    {
        return array_filter($this->{$entity}[] = $this->syncField($field));
    }

    public function addMultiple($entity, $field)
    {
        $this->{$entity} = array_filter(array_map([$this, 'syncField'], $fields));
    }

    public function sync($type, $fields, $attributes)
    {
        if (!empty($this->{$type}))
        {
            $this->{$type} = array_map(function($field) use ($fields, $attributes) {
                if (in_array($field['name'], (array)$fields)) $field = array_merge($field, $attributes);

                return $field;
            }, $this->{$type});
        }
    }



    public function remove($entity, $fields)
    {
        return array_values(array_filter($this->{$entity}, function($field) use ($fields) { return !in_array($field['name'], (array)$fields);}));
    }

    public function setSort($items, $order)
    {
        $this->sort[$items] = $order;
    }

    public function sort($items)
    {
        if (array_key_exists($items, $this->sort))
        {
            $elements = [];

            foreach ($this->sort[$items] as $item)
            {
                if (is_numeric($key = array_search($item, array_column($this->{$items}, 'name')))) $elements[] = $this->{$items}[$key];
            }

            return $this->{$items} = array_merge($elements, array_filter($this->{$items}, function($item) use($items) {return !in_array($item['name'], $this->sort[$items]);}));
        }

        return $this->{$items};
    }





    // cred ca ia valorile din tabela de legatura ca sa ti le afiseze in select
    public function getRelationValues($model, $field, $where = [], $order = [])
    {
        $order = (array)$order;
        $values = $model->select('*');

        if (!empty($where)) call_user_func_array([$values, $where[0]], array_slice($where, 1));

        if (!empty($order)) call_user_func_array([$values, 'orderBy'], $order);

        return $values->get()->lists($field, $model->getKeyName())->toArray();
    }

    // face un fel de merge intre ce ii dai si ce e in CRUD
    public function syncRelations($entity)
    {
        foreach ($this->relations as $field => $relation) {
            if ($relation['pivot']) $this->add($entity, ['name' => $field, 'type' => 'multiselect', 'value' => [], 'values' => $this->relations[$field]['values']]);
                else $this->sync($entity, $field, ['type' => 'select', 'values' => $this->relations[$field]['values']]);
        }
    }



}