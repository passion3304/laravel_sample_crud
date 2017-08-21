<?php

namespace Backpack\CRUD\PanelTraits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Create
{
    /*
    |--------------------------------------------------------------------------
    |                                   CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * Insert a row in the database.
     *
     * @param  [Request] All input values to be inserted.
     *
     * @return [Eloquent Collection]
     */
    public function create($data)
    {
        $data = $this->decodeJsonCastedAttributes($data, 'create');
        $data = $this->compactFakeFields($data, 'create');

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = array_pluck($this->getRelationFieldsWithPivot('create'), 'name');
        $item = $this->model->create(array_except($data, $nn_relationships));

        // if there are any relationships available, also sync those
        $this->createRelations($item, $data);

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
     * Get all fields with relation set (model key set on field).
     *
     * @param [string: create/update/both]
     *
     * @return [array] The fields with model key set.
     */
    public function getRelationFields($form = 'create')
    {
        if ($form == 'create') {
            $fields = $this->create_fields;
        } else {
            $fields = $this->update_fields;
        }

        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model'])) {
                array_push($relationFields, $field);
            }

            if (isset($field['subfields']) &&
                is_array($field['subfields']) &&
                count($field['subfields'])) {
                foreach ($field['subfields'] as $subfield) {
                    array_push($relationFields, $subfield);
                }
            }
        }

        return $relationFields;
    }

    /**
     * Get all fields with n-n relation set (pivot table is true).
     *
     * @param [string: create/update/both]
     *
     * @return [array] The fields with n-n relationships.
     */
    public function getRelationFieldsWithPivot($form = 'create')
    {
        $all_relation_fields = $this->getRelationFields($form);

        return array_where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && $value['pivot'];
        });
    }

    /**
     * Create the relations for the current model.
     *
     * @param mixed $item The current CRUD model.
     * @param array $data The form data.
     * @param string $form Optional form type. Can be either 'create', 'update' or 'both'. Default is 'create'.
     */
    public function createRelations($item, $data, $form = 'create')
    {
        $this->syncPivot($item, $data, $form);
        $this->createOneToOneRelations($item, $data, $form);
    }

    /**
     * Sync the declared many-to-many associations through the pivot field.
     *
     * @param Model $model The current CRUD model.
     * @param array $data The form data.
     * @param string $form Optional form type. Can be either 'create', 'update' or 'both'. Default is 'create'.
     */
    public function syncPivot($model, $data, $form = 'create')
    {
        $fields_with_relationships = $this->getRelationFields($form);

        foreach ($fields_with_relationships as $key => $field) {
            if (isset($field['pivot']) && $field['pivot']) {
                $values = isset($data[$field['name']]) ? $data[$field['name']] : [];
                $model->{$field['name']}()->sync($values);

                if (isset($field['pivotFields'])) {
                    foreach ($field['pivotFields'] as $pivotField) {
                        foreach ($data[$pivotField] as $pivot_id => $field) {
                            $model->{$field['name']}()->updateExistingPivot($pivot_id, [$pivotField => $field]);
                        }
                    }
                }
            }

            if (isset($field['morph']) && $field['morph']) {
                $values = isset($data[$field['name']]) ? $data[$field['name']] : [];
                if ($model->{$field['name']}) {
                    $model->{$field['name']}()->update($values);
                } else {
                    $model->{$field['name']}()->create($values);
                }
            }
        }
    }


    /**
     * Create any existing one to one relations for the current model from the form data.
     *
     * @param Model $item The current CRUD model.
     * @param array $data The form data.
     * @param string $form Optional form type. Can be either 'create', 'update' or 'both'. Default is 'create'.
     */
    private function createOneToOneRelations($item, $data, $form = 'create')
    {
        $relationData = $this->formatData($data, $form);
        $this->createRelationsForItem($item, $relationData);
    }


    /**
     * Create any existing one to one relations for the current model from the relation data.
     *
     * @param Model $item The current CRUD model.
     * @param array $formattedData The form data.
     */
    private function createRelationsForItem($item, $formattedData)
    {
        foreach ($formattedData['relations'] as $relationMethod => $relationData) {
            $parent = $item;
            $model = $relationData['model'];
            $relation = $parent->{$relationMethod}();

            if ($relation instanceof BelongsTo) {
                $modelInstance = $model::find($relationData['values'])->first();
                if ($modelInstance != null) {
                    $relation->associate($modelInstance)->save();
                } else {
                    $relation->dissociate()->save();
                }
            } elseif ($relation instanceof HasOne) {
                if ($parent->{$relationMethod} != null) {
                    $parent->{$relationMethod}->update($relationData['values']);
                    $modelInstance = $parent->{$relationMethod};
                } else {
                    $relationModel = new $model();
                    $modelInstance = $relationModel->create($relationData['values']);
                    $relation->save($modelInstance);
                }
            } else {
                $relationModel = new $model();
                $modelInstance = $relationModel->create($relationData['values']);
                $relation->save($modelInstance);
            }

            if (isset($relationData['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationData['relations']]);
            }
        }
    }

    /**
     * Create a model relation data array from the form data.
     *
     * @param array $data The form data.
     * @param string $form Optional form type. Can be either 'create', 'update' or 'both'. Default is 'create'.
     *
     * @return array The formatted relation data.
     */
    private function formatData($data, $form = 'create')
    {
        $fieldsWithOneToOneRelations = collect($this->getRelationFields($form))
            ->sortBy(function ($value) {
                return substr_count($value['entity'], '.');
            })
            ->groupBy('entity')
            ->filter(function ($value) use ($data) {
                return array_filter(array_only($data, $value->pluck('name')->toArray()))
                    && (! isset($value['pivot']) || (0 === strpos($value['type'], 'select')));
            })
            ->map(function ($value) use ($data) {
                $relationArray['model'] = $value->pluck('model')->first();
                $relationArray['parent'] = $this->getRelationModel($relationArray['model'], -1);
                $relationArray['values'] = array_only($data, $value->pluck('name')->toArray());

                return $relationArray;
            })
            ->all();

        $relationData['relations'] = [];
        foreach ($fieldsWithOneToOneRelations as $itemKey => $itemValue) {
            $itemKeys = collect(explode('.', $itemKey));
            $lastItemKey = $itemKeys->pop();
            $path = 'relations.'.($itemKeys->count() ? implode('.', $itemKeys->toArray()).'.relations.'.$lastItemKey : $itemKey);
            array_set($relationData, $path, $itemValue);
        }

        return $relationData;
    }
}
