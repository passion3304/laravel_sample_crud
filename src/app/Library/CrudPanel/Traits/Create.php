<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
            } elseif ($relation instanceof HasMany || $relation instanceof MorphMany) {
                $relation_values = $relationDetails['values'][$relationMethod];
                // if relation values are null we can only attach, also we check if we sent a single dimensional array [1,2,3], or an array of arrays: [[1][2][3]]
                // if is as single dimensional array we can only attach.
                if ($relation_values === null || count($relation_values) == count($relation_values, COUNT_RECURSIVE)) {
                    $this->attachManyRelation($item, $relation, $relationDetails, $relation_values);
                } else {
                    $this->createManyEntries($item, $relation, $relationMethod, $relationDetails);
                }
            }
            if (isset($relationDetails['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationDetails['relations']]);
            }
        }
    }

    /**
     * When using the HasMany/MorphMany relations as selectable elements we use this function to "mimic-sync" in those relations.
     * Since HasMany/MorphMany does not have the `sync` method, we manually re-create it.
     * Here we add the entries that developer added and remove the ones that are not in the list.
     * This removal process happens with the following rules:
     * - by default Backpack will behave like a `sync` from M-M relations: it deletes previous entries and add only the current ones.
     * - `force_delete` is configurable in the field, it's `true` by default. When false, if connecting column is nullable instead of deleting the row we set the column to null.
     * - `fallback_id` could be provided. In this case instead of deleting we set the connecting key to whatever developer gives us.
     *
     * @return void
     */
    private function attachManyRelation($item, $relation, $relationDetails, $relation_values)
    {
        $model_instance = $relation->getRelated();
        $relation_foreign_key = $relation->getForeignKeyName();
        $relation_local_key = $relation->getLocalKeyName();

        if ($relation_values !== null) {
            // we add the new values into the relation
            $model_instance->whereIn($model_instance->getKeyName(), $relation_values)
                ->update([$relation_foreign_key => $item->{$relation_local_key}]);

            // we clear up any values that were removed from model relation.
            // if developer provided a fallback id, we use it
            // if column is nullable we set it to null if developer didn't specify `force_delete => true`
            // if none of the above we delete the model from database
            $removed_entries = $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                                ->where($relation_foreign_key, $item->{$relation_local_key});

            $this->handleManyRelationItemRemoval($model_instance, $removed_entries, $relationDetails, $relation_foreign_key);
        } else {
            // the developer cleared the selection
            // we gonna clear all related values by setting up the value to the fallback id, to null or delete.
            $removed_entries = $model_instance->where($relation_foreign_key, $item->{$relation_local_key});
            $this->handleManyRelationItemRemoval($model_instance, $removed_entries, $relationDetails, $relation_foreign_key);
        }
    }

    private function handleManyRelationItemRemoval($model_instance, $removed_entries, $relationDetails, $relation_foreign_key)
    {
        $relation_column_is_nullable = $model_instance->isColumnNullable($relation_foreign_key);
        $force_delete = $relationDetails['force_delete'] ?? false;
        $fallback_id = $relationDetails['fallback_id'] ?? false;

        if ($fallback_id) {
            return $removed_entries->update([$relation_foreign_key => $fallback_id]);
        }

        if ($force_delete) {
            return $removed_entries->delete();
        }

        if (! $relation_column_is_nullable && $model_instance->dbColumnHasDefault($relation_foreign_key)) {
            return $removed_entries->update([$relation_foreign_key => $model_instance->getDbColumnDefault($relation_foreign_key)]);
        }

        return $removed_entries->update([$relation_foreign_key => null]);
    }

    /**
     * Handle HasMany/MorphMany relations when used as creatable entries in the crud.
     * By using repeatable field, developer can allow the creation of such entries
     * in the crud forms.
     *
     * @return void
     */
    private function createManyEntries($entry, $relation, $relationMethod, $relationDetails)
    {
        $items = $relationDetails['values'][$relationMethod];

        $relation_local_key = $relation->getLocalKeyName();

        $created_ids = [];

        foreach ($items as $item) {
            if (isset($item[$relation_local_key]) && ! empty($item[$relation_local_key])) {
                $entry->{$relationMethod}()->updateOrCreate([$relation_local_key => $item[$relation_local_key]], Arr::except($item,$relation_local_key));
            } else {
                $created_ids[] = $entry->{$relationMethod}()->create($item)->{$relation_local_key};
            }
        }

        // get from $items the sent ids, and merge the ones created.
        $relatedItemsSent = array_merge(array_filter(Arr::pluck($items, $relation_local_key)), $created_ids);

        if (! empty($relatedItemsSent)) {
            // we perform the cleanup of removed database items
            $entry->{$relationMethod}()->whereNotIn($relation_local_key, $relatedItemsSent)->delete();
        }
    }

    /**
     * Get a relation data array from the form data. For each relation defined in the fields
     * through the entity attribute, set the model, parent model and attribute values.
     *
     * We traverse this relation array later to create the relations, for example:
     * - Current model HasOne Address
     * - Address (line_1, country_id) BelongsTo Country through country_id in Address Model
     *
     * So when editing current model crud user have two fields
     * - address.line_1
     * - address.country
     * (we infer country_id from relation)
     *
     * Those will be nested accordingly in this relation array, so address relation
     * will have a nested relation with country.
     *
     * @param  array  $input  The form input.
     * @return array The formatted relation details.
     */
    private function getRelationDetailsFromInput($input)
    {
        $relationFields = $this->getRelationFieldsWithoutPivot();

        //remove fields that are not in the submitted form input
        $relationFields = array_filter($relationFields, function ($item) use ($input) {
            return Arr::has($input, $item['name']);
        });

        $relationDetails = [];
        foreach ($relationFields as $field) {
            // we split the entity into relations, eg: user.accountDetails.address
            // (user -> HasOne accountDetails -> BelongsTo address)
            // we specifically use only the relation entity because relations like
            // HasOne and MorphOne use the attribute in the relation string
            $key = implode('.relations.', explode('.', $this->getOnlyRelationEntity($field)));
            $attributeName = (string) Str::of($field['name'])->afterLast('.');

            // since we can have for example 3 fields for address relation,
            // we make sure that at least once we set the relation details
            $fieldDetails = Arr::get($relationDetails, 'relations.'.$key, []);
            $fieldDetails['model'] = $fieldDetails['model'] ?? $field['model'];
            $fieldDetails['parent'] = $fieldDetails['parent'] ?? $this->getRelationModel($field['name'], -1);
            $fieldDetails['values'][$attributeName] = Arr::get($input, $field['name']);

            if (isset($field['fallback_id'])) {
                $fieldDetails['fallback_id'] = $field['fallback_id'];
            }
            if (isset($field['force_delete'])) {
                $fieldDetails['force_delete'] = $field['force_delete'];
            }

            Arr::set($relationDetails, 'relations.'.$key, $fieldDetails);
        }

        return $relationDetails;
    }
}
