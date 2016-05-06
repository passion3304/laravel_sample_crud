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

    public $permissions = ['list', 'add', 'edit', 'delete', 'reorder', 'show', 'details'];

    public $reorder = false;
    public $reorder_label = true;
    public $reorder_permission = true;
    public $reorder_max_level = 3;

    public $details_row = false;

    public $columns = []; // Define the columns for the table view as an array;
    public $create_fields = []; // Define the fields for the "Add new entry" view as an array;
    public $update_fields = []; // Define the fields for the "Edit entry" view as an array;
    public $fields = []; // Define both create_fields and update_fields in one array; will be overwritten by create_fields and update_fields;

    // TONE FIELDS - TODO: find out what he did with them, replicate or delete
    public $field_types = [];
    public $query;

    public $custom_buttons = [];
    public $relations = [];
    public $labels = [];
    public $required = [];
    public $sort = [];

    public $buttons = [''];
    public $list_actions = [];

    public $item;
    public $entry;


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


    public function setModel($model)
    {
        if (!class_exists($model)) throw new \Exception('This model does not exist.', 404);

        $this->model = new $model();
        $this->query = $this->model->select('*');

        $this->initEntities();
    }

    public function getModel()
    {
        return $this->model;
    }

    // TODO: make this work without having to specify "index"
    public function setRoute($route, $parameters = [])
    {
        if (!\Route::has($route)) throw new \Exception('This route does not exist.', 404);

        $this->route = route($route, $parameters);
        $this->initButtons();
    }

    public function getRoute()
    {
        return $this->route;
    }

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

    public function setColumns($columns)
    {
        $this->columns = array_filter(array_map([$this, 'syncColumn'], $columns));
    }

    // [name, label, type, callback => [$this, 'methodName']]
    public function addColumn($column)
    {
        return array_filter($this->columns[] = $this->syncColumn($column));
    }

    public function updateColumns($columns, $attributes)
    {
        $this->sync('columns', $columns, $attributes);
    }

    public function removeColumns($columns)
    {
        $this->columns = $this->remove('columns', $columns);
    }

    public function columns()
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

    public function item($id)
    {
        return $this->item = $this->model->findOrFail($id);
    }


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

    // public function clause(function|scope, $field|$value, $operand|$value, $value)
    public function clause($function)
    {
        return call_user_func_array([$this->query, $function], array_slice(func_get_args(), 1, 3));
    }

    public function orderBy($field, $order = 'asc')
    {
        return $this->query->orderBy($field, $order);
    }

    public function groupBy($field)
    {
        return $this->query->groupBy($field);
    }

    public function label($item, $label)
    {
        $this->labels[$item] = $label;
    }

    public function labels()
    {
        return $this->labels;
    }

    public function setRequired($fields)
    {
        $this->required = array_merge($this->required, (array)$fields);
    }

    public function required()
    {
        return $this->required;
    }

    private function initButtons()
    {
        $this->buttons = [
            'add' => ['route' => "{$this->route}/create", 'label' => trans('crud::crud.buttons.add'), 'class' => '', 'hide' => [], 'icon' => 'fa-plus-circle', 'extra' => []],
            'view' => ['route' => "{$this->route}/%d", 'label' => trans('crud::crud.buttons.view'), 'class' => '', 'hide' => [], 'icon' => 'fa-eye', 'extra' => []],
            'edit' => ['route' => "{$this->route}/%d/edit", 'label' => trans('crud::crud.buttons.edit'), 'class' => '', 'hide' => [], 'icon' => 'fa-edit', 'extra' => []],
            'delete' => ['route' => "{$this->route}/%d", 'label' => trans('crud::crud.buttons.delete'), 'class' => '', 'hide' => [], 'icon' => 'fa-trash', 'extra' => ['data-confirm' => trans('crud::crud.confirm.delete'), 'data-type' => 'delete']],
        ];
    }

    private function removeButtons($buttons)
    {
        foreach ($buttons as $button)
        {
            unset($this->buttons[$button]);
        }

        return $this->buttons;
    }

    private function getColumns() // DONE
    {
        $columns = \Schema::getColumnListing($this->model->getTable());
        $fillable = $this->model->getFillable();

        if (!empty($fillable)) $columns = array_intersect($columns, $fillable);

        return array_values(array_diff($columns, [$this->model->getKeyName(), 'updated_at', 'deleted_at']));
    }

    private function syncColumn($column)
    {
        if (array_key_exists('name', (array)$column)) return array_merge(['type' => $this->getType($column['name'])], $column);

        return false;
    }

    private function syncField($field)
    {
        if (array_key_exists('name', (array)$field)) return array_merge(['type' => $this->getType($field['name']), 'value' => '', 'default' => null, 'values' => [], 'attributes' => []], $field);

        return false;
    }

    private function getColumnTypes() // DONE
    {
        foreach (\DB::select(\DB::raw('SHOW COLUMNS FROM '.$this->model->getTable())) as $column)
        {
            $this->field_types[$column->Field] = ['type' => trim(preg_replace('/\(\d+\)(.*)/i', '', $column->Type)), 'default' => $column->Default];
        }

        return $this->field_types;
    }

    private function getType($field) // DONE
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

    private function initEntities() // DONE
    {
        $this->getColumnTypes();

        array_map(function($field) {
            $this->labels[$field] = $this->makeLabel($field);

            $this->fields[] = ['name' => $field, 'value' => '', 'default' => $this->field_types[$field]['default'], 'type' => $this->getType($field), 'values' => [], 'attributes' => []];

            if (!in_array($field, $this->model->getHidden())) $this->columns[] = ['name' => $field, 'type' => $this->getType($field)];
        }, $this->getColumns());
    }

    private function addFieldsValue()
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

    private function add($entity, $field)
    {
        return array_filter($this->{$entity}[] = $this->syncField($field));
    }

    private function addMultiple($entity, $field)
    {
        $this->{$entity} = array_filter(array_map([$this, 'syncField'], $fields));
    }

    private function sync($type, $fields, $attributes)
    {
        if (!empty($this->{$type}))
        {
            $this->{$type} = array_map(function($field) use ($fields, $attributes) {
                if (in_array($field['name'], (array)$fields)) $field = array_merge($field, $attributes);

                return $field;
            }, $this->{$type});
        }
    }

    private function remove($entity, $fields)
    {
        return array_values(array_filter($this->{$entity}, function($field) use ($fields) { return !in_array($field['name'], (array)$fields);}));
    }

    private function setSort($items, $order)
    {
        $this->sort[$items] = $order;
    }

    private function sort($items)
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

    private function syncPivot($model, $data)
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

    private function getRelationValues($model, $field, $where = [], $order = [])
    {
        $order = (array)$order;
        $values = $model->select('*');

        if (!empty($where)) call_user_func_array([$values, $where[0]], array_slice($where, 1));

        if (!empty($order)) call_user_func_array([$values, 'orderBy'], $order);

        return $values->get()->lists($field, $model->getKeyName())->toArray();
    }

    private function syncRelations($entity)
    {
        foreach ($this->relations as $field => $relation) {
            if ($relation['pivot']) $this->add($entity, ['name' => $field, 'type' => 'multiselect', 'value' => [], 'values' => $this->relations[$field]['values']]);
                else $this->sync($entity, $field, ['type' => 'select', 'values' => $this->relations[$field]['values']]);
        }
    }

    private function makeLabel($value) // DONE
    {
        return trim(preg_replace('/(id|at|\[\])$/i', '', ucfirst(str_replace('_', ' ', $value))));
    }




















    // NEW FUNCTIONS

    public function getCreateFields()
    {
        return $this->prepareFields(empty($this->create_fields)?$this->fields:$this->create_fields);
    }

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

    public function reorder($request) {
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



    // -----------------
    // Permission Functions
    // -----------------

    public function addPermissions($permissions)
    {
        // $this->addButtons((array)$permissions);
        return $this->permissions = array_merge(array_diff((array)$permissions, $this->permissions), $this->permissions);
    }

    public function removePermissions($permissions)
    {
        // $this->removeButtons((array)$permissions);
        return $this->permissions = array_diff($this->permissions, (array)$permissions);
    }

    /**
     * Check if a permission is enabled for a Crud Panel. Return false if not.
     *
     * @param  [string] Permission.
     * @return boolean
     */
    public function hasPermission($permission)
    {
        if (!in_array($permission, $this->permissions))
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
    public function hasPermissionOrFail($permission)
    {
        if (!in_array($permission, $this->permissions))
        {
            abort(403, trans('backpack::crud.unauthorized_access'));
        }
    }

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


}