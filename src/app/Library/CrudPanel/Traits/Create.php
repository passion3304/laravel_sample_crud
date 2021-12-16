<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;

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
     * @param  array  $data  All input values to be inserted.
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);
        $data = $this->changeBelongsToNamesFromRelationshipToForeignKey($data);

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = Arr::pluck($this->getRelationFieldsWithPivot(), 'name');

        $item = $this->model->create(Arr::except($data, $nn_relationships));

        // if there are any relationships available, also sync those
        $this->createRelations($item, $data);

        return $item;
    }

    /**
     * Get all fields needed for the ADD NEW ENTRY form.
     *
     * @return array The fields with attributes and fake attributes.
     */
    public function getCreateFields()
    {
        return $this->fields();
    }

    /**
     * Get all fields with relation set (model key set on field).
     *
     * @return array The fields with model key set.
     */
    public function getRelationFields()
    {
        $fields = $this->getCleanStateFields();
        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model']) && $field['model'] !== false) {
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
     * Create the relations for the current model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $data  The form data.
     */
    public function createRelations($item, $data)
    {
        $this->syncPivot($item, $data);
        $this->createOneToOneRelations($item, $data);
    }

    /**
     * Sync the declared many-to-many associations through the pivot field.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  The current CRUD model.
     * @param  array  $input  The form input.
     */
    public function syncPivot($model, $input)
    {
        $fields_with_relationships = $this->getRelationFields();
        foreach ($fields_with_relationships as $key => $field) {
            if (isset($field['pivot']) && $field['pivot']) {
                $values = isset($input[$field['name']]) ? $input[$field['name']] : [];

                // if a JSON was passed instead of an array, turn it into an array
                if (is_string($values)) {
                    $values = json_decode($values, true);
                }

                $relation_data = [];
                if (isset($field['pivotFields'])) {
                    foreach ($values as $pivot_row) {
                        $relation_data[$pivot_row[$field['name']]] = Arr::except($pivot_row, $field['name']);
                    }
                }

                // if there is no relation data, and the values array is single dimensional we have
                // an array of keys with no aditional pivot data. sync those.
                if (empty($relation_data) && count($values) == count($values, COUNT_RECURSIVE)) {
                    $relation_data = array_values($values);
                }

                $model->{$field['name']}()->sync($relation_data);
            }

            if (isset($field['morph']) && $field['morph'] && isset($input[$field['name']])) {
                $values = $input[$field['name']];
                $model->{$field['name']}()->sync($values);
            }
        }
    }

    /**
     * Create any existing one to one relations for the current model from the form data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $input  The form data.
     */
    private function createOneToOneRelations($item, $input)
    {
        $relationDetails = $this->getRelationDetailsFromInput($input);
        $this->createRelationsForItem($item, $relationDetails);
    }

    /**
     * Create any existing one to one relations for the current model from the relation data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $formattedRelations  The form data.
     * @return bool|null
     */
    private function createRelationsForItem($item, $formattedRelations)
    {
        if (! isset($formattedRelations['relations'])) {
            return false;
        }
        foreach ($formattedRelations['relations'] as $relationMethod => $relationDetails) {
            if (! isset($relationDetails['model'])) {
                continue;
            }
            $model = $relationDetails['model'];
            $relation = $item->{$relationMethod}();

            if ($relation instanceof BelongsTo) {
                $modelInstance = $model::find($relationDetails['values'])->first();
                if ($modelInstance != null) {
                    $relation->associate($modelInstance)->save();
                } else {
                    $relation->dissociate()->save();
                }
            } elseif ($relation instanceof HasOne || $relation instanceof MorphOne) {
                $modelInstance = $relation->updateOrCreate([], $relationDetails['values']);
            }

            if (isset($relationDetails['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationDetails['relations']]);
            }
        }
    }

    /**
     * Get a relation data array from the form data.
     * For each relation defined in the fields through the entity attribute, set the model, the parent model and the
     * attribute values.
     *
     * We traverse this relation array later to create the relations, for example:
     *
     * Current model HasOne Address, this Address (line_1, country_id) BelongsTo Country through country_id in Address Model.
     *
     * So when editing current model crud user have two fields address.line_1 and address.country (we infer country_id from relation)
     *
     * Those will be nested accordingly in this relation array, so address relation will have a nested relation with country.
     *
     *
     * @param  array  $input  The form input.
     * @return array The formatted relation details.
     */
    private function getRelationDetailsFromInput($input)
    {
        $relation_fields = $this->getRelationFieldsWithoutPivot();

        //remove fields that are not in the submitted form input
        $relation_fields = array_filter($relation_fields, function ($item) use ($input) {
            return Arr::has($input, $item['name']);
        });

        $relationDetails = [];
        foreach ($relation_fields as $relation_field) {
            // use the field name as a search parameter
            $attributeKey = $relation_field['name'];

            // we split the entity into relations, eg: user.accountDetails.address (user -> HasOne accountDetails -> BelongsTo address).
            // we specifically use only the relation entity because relations like HasOne and MorphOne use the attribute in the relation string
            $key = implode('.relations.', explode('.', $this->getOnlyRelationEntity($relation_field)));

            // since we can have for example 3 fields for address relation, we make sure that atleast once we set the relation details.
            $fieldDetails = Arr::get($relationDetails, 'relations.'.$key, []);

            $fieldDetails['model'] = $fieldDetails['model'] ?? $relation_field['model'];

            $fieldDetails['parent'] = $fieldDetails['parent'] ?? $this->getRelationModel($attributeKey, -1);

            // this relations have the attribute as a last parameter, use it to create the array of details, eg: address.name
            $relatedAttribute = Arr::last(explode('.', $attributeKey));

            $fieldDetails['values'][$relatedAttribute] = Arr::get($input, $attributeKey);

            Arr::set($relationDetails, 'relations.'.$key, $fieldDetails);
        }

        return $relationDetails;
    }
}
