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

    public $access = ['list', 'create', 'update', 'delete', /* 'reorder', 'show', 'details_row' */];

    public $reorder = false;
    public $reorder_label = false;
    public $reorder_max_level = 3;

    public $details_row = false;

    public $columns = []; // Define the columns for the table view as an array;
    public $create_fields = []; // Define the fields for the "Add new entry" view as an array;
    public $update_fields = []; // Define the fields for the "Edit entry" view as an array;

    public $query;
    public $entry;

    // TONE FIELDS - TODO: find out what he did with them, replicate or delete
    public $field_types = [];

    public $custom_buttons = [];
    public $relations = [];
    public $sort = [];

    public $buttons = [''];



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
        $values_to_store = $this->compactFakeFields($data, 'create');
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
        return $this->create_fields;
    }

    /**
     * Get all fields with relation set (model key set on field)
     *
     * @param [string: create/update/both]
     * @return [array] The fields with model key set.
     */
    public function getRelationFields($form = 'create')
    {
        if ($form == 'create')
        {
            $fields = $this->create_fields;
        }
        else
        {
            $fields = $this->update_fields;
        }

        $relationFields = [];

        foreach($fields as $field){
            if (isset($field['model']))
            {
                array_push($relationFields, $field);
            }

            if (isset($field['subfields']) &&
                is_array($field['subfields']) &&
                count($field['subfields']))
            {
                foreach ($field['subfields'] as $subfield)
                {
                    array_push($relationFields, $subfield);
                }
            }
        }

        return $relationFields;
    }


    public function syncPivot($model, $data, $form = 'create')
    {

        $fields_with_relationships = $this->getRelationFields($form);

        foreach ($fields_with_relationships as $key => $field)
        {
            if (isset($field['pivot']) && $field['pivot'] )
            {
                $values = isset($data[$field['name']])?$data[$field['name']]:[];
                $model->{$field['name']}()->sync($values);

                if( isset($field['pivotFields']) ){
                    foreach($field['pivotFields'] as $pivotField){
                       foreach($data[$pivotField] as $pivot_id =>  $field){
                         $model->{$field['name']}()->updateExistingPivot($pivot_id, [$pivotField => $field]);
                       }
                    }
                }
            }
        }
    }



    /**
     * Adds a required => true attribute to each field, so that the required asterisc will show up in the create/update forms.
     * TODO: make this work, by editing the $this->fields variable and all fields.
     *
     * @param [string or array of strings]
     */
    public function setRequiredFields($fields, $form = 'both')
    {
        // TODO
    }

    /**
     * Adds a required => true attribute to this field, so that the required asteris will show up in the create/update forms.
     *
     * @param [string]
     */
    public function setRequiredField($field, $form = 'both')
    {
        // TODO
    }

    /**
     * Get all fields that have the required attribute.
     * TODO: make this work after making setRequiredFields() work.
     *
     * @return [array]
     */
    public function getRequiredFields($form = 'both')
    {
        // TODO
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
        switch (strtolower($form)) {
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

    /**
     * Enable the DETAILS ROW functionality:
     *
     * In the table view, show a plus sign next to each entry.
     * When clicking that plus sign, an AJAX call will bring whatever content you want from the EntityCrudController::showDetailsRow($id) and show it to the user.
     */
    public function enableDetailsRow()
    {
        $this->details_row = true;
    }

    /**
     * Disable the DETAILS ROW functionality:
     */
    public function disableDetailsRow()
    {
        $this->details_row = false;
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
        $updated = $item->update($this->compactFakeFields($data, 'update'));

        /*if ($updated) */$this->syncPivot($item, $data, 'update');

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
        $fields = $this->update_fields;
        $entry = $this->getEntry($id);

        foreach ($fields as $k => $field) {
            // set the value
            if (!isset($fields[$k]['value']))
            {
                if (isset($field['subfields']))
                    {
                    $fields[$k]['value'] = [];
                    foreach($field['subfields'] as $key => $subfield){
                        $fields[$k]['value'][] = $entry->{$subfield['name']};
                    }

                }else{
                    $fields[$k]['value'] = $entry->{$field['name']};
                }
            }
        }

        // always have a hidden input for the entry id
        $fields['id'] = array(
                        'name' => 'id',
                        'value' => $entry->id,
                        'type' => 'hidden'
                    );

        return $fields;
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
    |                                   REORDER
    |--------------------------------------------------------------------------
    */


    /**
     * Change the order and parents of the given elements, according to the NestedSortable AJAX call.
     *
     * @param  [Request] The entire request from the NestedSortable AJAX Call.
     * @return [integer] The number of items whose position in the tree has been changed.
     */
    public function updateTreeOrder($request)
    {
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


    /**
     * Enable the Reorder functionality in the CRUD Panel for users that have the been given access to 'reorder' using:
     * $this->crud->allowAccess('reorder');
     *
     * @param  [string] Column name that will be shown on the labels.
     * @param  [integer] Maximum hierarchy level to which the elements can be nested (1 = no nesting, just reordering).
     */
    public function enableReorder($label = 'name', $max_level = 1)
    {
        $this->reorder = true;
        $this->reorder_label = $label;
        $this->reorder_max_level = $max_level;
    }

    /**
     * Disable the Reorder functionality in the CRUD Panel for all users.
     *
     */
    public function disableReorder()
    {
        $this->reorder = false;
    }

    /**
     * Check if the Reorder functionality is enabled or not.
     *
     * @return boolean
     */
    public function isReorderEnabled()
    {
        return $this->reorder;
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


    /**
     * Order the columns in a certain way.
     *
     * @param [string] Column name.
     * @param [attributes and values array]
     */
    public function setColumnOrder($columns)
    {
        // TODO
    }

    // ALIAS of setColumnOrder($columns)
    public function setColumnsOrder($columns) { $this->setColumnOrder($columns); }


    // ------------
    // FIELDS
    // ------------

    /**
     * Add a field to the create/update form or both.
     * @param [string] $name    Field name (the column name in the db in most cases)
     * @param [array] $options Field-type-specific information.
     * @param string $form    The form to add the field to (create/update/both)
     */
    public function addField($field, $form='both')
    {
        // if the field_defition_array array is a string, it means the programmer was lazy and has only passed the name
        // set some default values, so the field will still work
        if (is_string($field))
        {
            $complete_field_array['name'] = $field;
        }
        else
        {
            $complete_field_array = $field;
        }

        // if the label is missing, we should set it
        if (!isset($complete_field_array['label']))
            $complete_field_array['label'] = ucfirst($complete_field_array['name']);

        // if the field type is missing, we should set it
        if (!isset($complete_field_array['type']))
            $complete_field_array['type'] = $this->getFieldTypeFromDbColumnType($complete_field_array['name']);

        // store the field information into the correct variable on the CRUD object
        switch (strtolower($form)) {
            case 'create':
                $this->create_fields[$complete_field_array['name']] = $complete_field_array;
                break;

            case 'update':
                $this->update_fields[$complete_field_array['name']] = $complete_field_array;
                break;

            default:
                $this->create_fields[$complete_field_array['name']] = $complete_field_array;
                $this->update_fields[$complete_field_array['name']] = $complete_field_array;
                break;
        }
    }

    public function addFields($fields, $form='both')
    {
        if (count($fields)) {
            foreach ($fields as $field) {
                $this->addField($field, $form);
            }
        }
    }

    /**
     * Remove a certain field from the create/update/both forms by its name.
     * @param  string $name Field name (as defined with the addField() procedure)
     * @param  string $form update/create/both
     */
    public function removeField($name, $form='both')
    {
        switch (strtolower($form)) {
            case 'create':
                array_forget($this->create_fields, $name);
                break;

            case 'update':
                array_forget($this->update_fields, $name);
                break;

            default:
                array_forget($this->create_fields, $name);
                array_forget($this->update_fields, $name);
                break;
        }
    }

    /**
     * Remove many fields from the create/update/both forms by their name.
     * @param  array $array_of_names A simple array of the names of the fields to be removed.
     * @param  string $form          update/create/both
     */
    public function removeFields($array_of_names, $form='both')
    {
        if (!empty($array_of_names)) {
            foreach ($array_of_names as $name) {
                $this->removeField($name, $form);
            }
        }
    }

    // TODO: $this->crud->replaceField('name', 'update/create/both');

    // TODO: $this->crud->setRequiredFields(['field_1', 'field_2'], 'update/create/both');
    // TODO: $this->crud->setRequiredField('field_1', 'update/create/both');
    // TODO: $this->crud->getRequiredFields();

    // TODO: $this->crud->setFieldOrder(['field_1', 'field_2', 'field_3'], 'update/create/both');


    /**
     * Check if field is the first of its type in the given fields array.
     * It's used in each field_type.blade.php to determine wether to push the css and js content or not (we only need to push the js and css for a field the first time it's loaded in the form, not any subsequent times).
     *
     * @param  array $field        The current field being tested if it's the first of its type.
     * @param  array $fields_array All the fields in that particular form.
     * @return bool  true/false
     */
    public function checkIfFieldIsFirstOfItsType($field, $fields_array) {
        if ($field['name'] == $this->getFirstOfItsTypeInArray($field['type'], $fields_array)['name'])
            return true;

        return false;
    }


    /**
     * Order the fields in a certain way.
     *
     * @param [string] Column name.
     * @param [attributes and values array]
     */
    public function setFieldOrder($fields)
    {
        // TODO
    }

    // ALIAS of setFieldOrder($fields)
    public function setFieldsOrder($fields) { $this->setFieldOrder($fields); }


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
            // $this->labels[$field] = $this->makeLabel($field);

            $new_field =  [
                                'name' => $field,
                                'label' => ucfirst($field),
                                'value' => '', 'default' => $this->field_types[$field]['default'],
                                'type' => $this->getFieldTypeFromDbColumnType($field),
                                'values' => [],
                                'attributes' => []
                                ];
            $this->create_fields[] = $new_field;
            $this->update_fields[] = $new_field;

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


    /**
     * Turn a database column name or PHP variable into a pretty label to be shown to the user.
     *
     * @param  [string]
     * @return [string]
     */
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
     * Refactor the request array to something that can be passed to the model's create or update function.
     * The resulting array will only include the fields that are stored in the database and their values,
     * plus the '_token' and 'redirect_after_save' variables.
     *
     * @param   Request     $request - everything that was sent from the form, usually \Request::all()
     * @param   String      $form - create/update - to determine what fields should be compacted
     * @return  array
     */
    public function compactFakeFields($request, $form = 'create')
    {
        $fake_field_columns_to_encode = [];

        // get the right fields according to the form type (create/update)
        switch (strtolower($form)) {
            case 'update':
                $fields = $this->update_fields;
                break;

            default:
                $fields = $this->create_fields;
                break;
        }

        // go through each defined field
        foreach ($fields as $k => $field) {
            // if it's a fake field
            if (isset($fields[$k]['fake']) && $fields[$k]['fake'] == true) {
                // add it to the request in its appropriate variable - the one defined, if defined
                if (isset($fields[$k]['store_in'])) {
                    $request[$fields[$k]['store_in']][$fields[$k]['name']] = $request[$fields[$k]['name']];

                    $remove_fake_field = array_pull($request, $fields[$k]['name']);
                    if (!in_array($fields[$k]['store_in'], $fake_field_columns_to_encode, true)) {
                        array_push($fake_field_columns_to_encode, $fields[$k]['store_in']);
                    }
                } else //otherwise in the one defined in the $crud variable
                {
                    $request['extras'][$fields[$k]['name']] = $request[$fields[$k]['name']];

                    $remove_fake_field = array_pull($request, $fields[$k]['name']);
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
    public function getFakeColumnsAsArray($form = 'create')
    {
        $fake_field_columns_to_encode = [];

        // get the right fields according to the form type (create/update)
        switch (strtolower($form)) {
            case 'update':
                $fields = $this->update_fields;
                break;

            default:
                $fields = $this->create_fields;
                break;
        }


        foreach ($fields as $k => $field) {
            // if it's a fake field
            if (isset($fields[$k]['fake']) && $fields[$k]['fake'] == true) {
                // add it to the request in its appropriate variable - the one defined, if defined
                if (isset($fields[$k]['store_in'])) {
                    if (!in_array($fields[$k]['store_in'], $fake_field_columns_to_encode, true)) {
                        array_push($fake_field_columns_to_encode, $fields[$k]['store_in']);
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


    /**
     * Return the first element in an array that has the given 'type' attribute.
     * @param  string $type
     * @param  array $array
     * @return array
     */
    public function getFirstOfItsTypeInArray($type, $array)
    {
        return array_first($array, function($key, $item) use ($type) {
            return $item['type'] == $type;
        });
    }











    // ------------
    // TONE FUNCTIONS - UNDOCUMENTED, UNTESTED, SOME MAY BE USED IN THIS FILE
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
        array_unshift($this->custom_buttons, $button);
    }

    public function customButtons()
    {
        return $this->custom_buttons;
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
    // public function addField($field)
    // {
    //     return $this->add('fields', $field);
    // }

    public function updateFields($fields, $attributes)
    {
        $this->sync('fields', $fields, $attributes);
    }

    // public function removeFields($fields)
    // {
    //     $this->fields = $this->remove('fields', $fields);
    //     $this->removeColumns($fields);
    // }

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
        if (!$this->entry && !empty($this->create_fields))
        {
            $this->syncRelations('create_fields');

            return $this->create_fields;
        }

        if ($this->entry && !empty($this->update_fields))
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


    // public function syncField($field)
    // {
    //     if (array_key_exists('name', (array)$field))
    //         return array_merge(['type' => $this->getFieldTypeFromDbColumnType($field['name']), 'value' => '', 'default' => null, 'values' => [], 'attributes' => []], $field);

    //     return false;
    // }





    // iti pune valorile pe field-uri la EDIT
    public function addFieldsValue()
    {
        if ($this->entry)
        {
            $fields = !empty($this->update_fields) ? 'update_fields' : 'fields';

            foreach ($this->{$fields} as $key => $field)
            {
                if (array_key_exists($field['name'], $this->relations) && $this->relations[$field['name']]['pivot']) $this->{$fields}[$key]['value'] = $this->entry->{$this->relations[$field['name']]['name']}()->lists($this->relations[$field['name']]['model']->getKeyName())->toArray();
                    else $this->{$fields}[$key]['value'] = $this->entry->{$field['name']};
            }
        }
    }

    // public function add($entity, $field)
    // {
    //     return array_filter($this->{$entity}[] = $this->syncField($field));
    // }

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



    // public function remove($entity, $fields)
    // {
    //     return array_values(array_filter($this->{$entity}, function($field) use ($fields) { return !in_array($field['name'], (array)$fields);}));
    // }

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