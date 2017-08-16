<?php

namespace Backpack\CRUD\PanelTraits;

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
     * @param mixed $item current model
     * @param array $data form data
     */
    public function createRelations($item, $data) {
        $this->syncPivot($item, $data);
        $this->createOneToOneRelations($item, $data);
    }

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

    private function createOneToOneRelations($item, $data, $form = 'create')
    {
        $fieldWithOneToOneRelations = collect($this->getRelationFields($form))
            ->sortBy(function ($value) {
                return substr_count($value['entity'], ".");
            })
            ->groupBy('entity')
            ->filter(function ($value) {
                return (!isset($value['pivot']) || (0 === strpos($value['type'], 'select')));
            })
            ->map(function ($value) {
                $relationArray['model'] = $value->pluck('model')->first();
                $relationArray['attributes'] = $value->pluck('name');
                return $relationArray;
            })
            ->all();

        $currentItem = $item;
        foreach ($fieldWithOneToOneRelations as $relationString => $relation) {
            $modelRelations = explode(".", $relationString);
            $currentRelation = $currentItem->{end($modelRelations)}();

            if ($currentRelation instanceof BelongsTo) {
                $associatedModelName = $this->getRelationModel($relationString);
                $associatedModel = $associatedModelName::find($data[$relation['attributes']->first()]);
                $currentRelation->associate($associatedModel)->save();

                $currentItem = $associatedModel;
            } else {
                $relationModel = new $relation['model']();
                $modelInstance = $relationModel->create(array_only($data, $relation['attributes']->toArray()));
                $currentRelation->save($modelInstance);

                $currentItem = $modelInstance;
            }
        }
    }
}
