<?php

namespace Backpack\CRUD;

class Crud
{
    private $fieldTypes;

    protected $model;
    protected $query;

    protected $route;
    protected $title;
    protected $subTitle;

    protected $fields = [];
    protected $createFields = [];
    protected $updateFields = [];
    protected $columns = [];
    protected $customButtons = [];
    protected $relations = [];
    protected $labels = [];
    protected $required = [];
    protected $sort = [];
    protected $state;

    protected $buttons = [];
    protected $permissions = [];
    protected $listActions = [];

    public $item;

    public function __construct()
    {
        $this->title = trans_choice('crud::crud.item', 2);
        $this->initPermisssions();
        $this->initListActions();
        $this->initState();
    }

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

    public function setRelation($field, $relationName, $relationModel, $relationField, $pivot = false, $where = [], $order = [], $pivotFields = [])
    {
        $this->relations[$field] = ['name' => $relationName, 'model' => new $relationModel(), 'field' => $relationField, 'pivot' => $pivot, 'where' => $where, 'order' => $order, 'values' => $this->getRelationValues(new $relationModel(), $relationField, $where, $order), 'pivotFields' => $pivotFields];
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;
    }

    public function getSubTitle()
    {
        return $this->subTitle;
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

    public function removePermissions($permissions) // DONE
    {
        $this->removeButtons((array)$permissions);

        return $this->permissions = array_diff($this->permissions, (array)$permissions);
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
        $this->addMultiple('createFields', $fields);
    }

    public function addCreateField($field)
    {
       return $this->add('createFields', $field);
    }

     public function setUpdateFields($fields)
    {
        $this->addMultiple('updateFields', $fields);
    }

    public function addUpdateField($field)
    {
        return $this->add('updateFields', $field);
    }

    public function fields()
    {
        if (!$this->item && !empty($this->createFields))
        {
            $this->syncRelations('createFields');

            return $this->createFields;
        }

        if ($this->item && !empty($this->updateFields))
        {
            $this->syncRelations('updateFields');
            $this->addFieldsValue();

            return $this->updateFields;
        }

        $this->syncRelations('fields');
        $this->addFieldsValue();

        return $this->sort('fields');
    }

    public function orderFields($order)
    {
        $this->setSort('fields', (array)$order);
    }

    public function items()
    {
        return $this->query->get();
    }

    public function item($id)
    {
        return $this->item = $this->model->findOrFail($id);
    }

    public function save($data)
    {
        $model = $this->model->create($data);

        $this->syncPivot($model, $data);

        return $model;
    }

    public function update($id, $data)
    {
        $model = $this->model->findOrFail($id);
        $updated = $model->update($data);
        if ($updated) $this->syncPivot($model, $data);

        return $model;
    }

    public function delete($id) // DONE
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

    public function checkPermission($permission) // DONE
    {
        if (!in_array($permission, $this->permissions)) abort(403);
    }

    public function unsetListActions($actions)
    {
        return $this->listActions = array_diff($this->listActions, (array)$actions);
    }

    public function checkListAction($action)
    {
        return in_array($action, $this->listActions);
    }

    public function getState()
    {
        return trans("crud::crud.form.{$this->state}");
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

    public function state()
    {
        return $this->state;
    }

    private function initPermisssions()
    {
        $this->permissions = ['list', 'add', 'edit', 'delete', 'view'];
    }

    private function initListActions()
    {
        $this->listActions = ['ordering', 'pagination', 'search', 'save_state'];
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

    private function initState()
    {
        $segments = \Request::segments();

        $this->state = 'list';

        if (in_array('create', $segments)) $this->state = 'add';

        if (in_array('edit', $segments)) $this->state = 'edit';

        if (is_numeric(end($segments))) $this->state = 'view';
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
            $this->fieldTypes[$column->Field] = ['type' => trim(preg_replace('/\(\d+\)(.*)/i', '', $column->Type)), 'default' => $column->Default];
        }

        return $this->fieldTypes;
    }

    private function getType($field) // DONE
    {
        if (!array_key_exists($field, $this->fieldTypes)) return 'text';

        if ($field == 'password') return 'password';

        if ($field == 'email') return 'email';

        switch ($this->fieldTypes[$field]['type'])
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

            $this->fields[] = ['name' => $field, 'value' => '', 'default' => $this->fieldTypes[$field]['default'], 'type' => $this->getType($field), 'values' => [], 'attributes' => []];

            if (!in_array($field, $this->model->getHidden())) $this->columns[] = ['name' => $field, 'type' => $this->getType($field)];
        }, $this->getColumns());
    }

    private function addFieldsValue()
    {
        if ($this->item)
        {
            $fields = !empty($this->updateFields) ? 'updateFields' : 'fields';

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
}