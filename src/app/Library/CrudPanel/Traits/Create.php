<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

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
     * @param  array  $input  All input values to be inserted.
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($input)
    {
        [$directInputs, $relationInputs] = $this->splitInputIntoDirectAndRelations($input);
        $item = $this->model->create($directInputs);
        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * Returns the attributes with relationships stripped out from the input.
     * BelongsTo relations are ensured to have the correct foreign key set.
     * ALL other relations are stripped from the input.
     *
     * @param  array  $input  - the input array
     * @param  mixed  $model  - the model of what we want to get the attributtes for
     * @param  array  $fields  - the fields used in this relation
     * @param  mixed  $relationMethod  - the relation method
     * @return array
     */
    private function getDirectInputsFromInput($input, $model = false, $fields = [], $relationMethod = false)
    {
        $model = $model ? (is_string($model) ? app($model) : $model) : $this->model;

        $input = $this->decodeJsonCastedAttributes($input, $model);
        $input = $this->compactFakeFields($input);

        $input = $this->excludeRelationFieldsExceptBelongsTo($input, $fields, $relationMethod);
        $input = $this->changeBelongsToNamesFromRelationshipToForeignKey($input, $fields);
        
        return $input;
    }

    /**
     * Return the input without relations except BelongsTo that we are going to properly match
     * with the relation foreign_key in a later stage of the saving process.
     * 
     * @param  array  $fields
     * @param  mixed  $relationMethod
     * @return array
     */

    private function excludeRelationFieldsExceptBelongsTo($input, $fields, $relationMethod)
    {
        // when fields are empty we are in the main entity, we get the regular crud relation fields
        if (empty($fields)) {
            $fields = $this->getRelationFields();
        }

        $excludedFields = [];
        foreach ($fields as $field) {
            
            $nameToExclude =  $relationMethod ? Str::after($field['name'], $relationMethod.'.') : $field['name'];

            // when using dot notation if relationMethod is not set we are sure we want to exclude those relations.
            if ($this->getOnlyRelationEntity($field) !== $field['entity']) {
                if(!$relationMethod) {
                    $excludedFields[] = $nameToExclude;
                }
                continue;
            }

            if(isset($field['relation_type']) && $field['relation_type'] !== 'BelongsTo') {
                $excludedFields[] = $nameToExclude;
                continue;
            }
        }

        return Arr::where($input, function ($item, $key) use ($excludedFields) {
            return ! in_array($key, $excludedFields);
        });
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
     * @param  array  $fields
     * @return array The fields with model key set.
     */
    public function getRelationFields($fields = [])
    {
        if (empty($fields)) {
            $fields = $this->getCleanStateFields();
        }

        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model']) && $field['model'] !== false) {
                array_push($relationFields, $field);
            }

            // if a field has an array name AND subfields
            // then take those fields into account (check if they have relationships);
            // this is done in particular for the checklist_dependency field,
            // but other fields could use it too, in the future;
            if (is_array($field['name']) &&
                isset($field['subfields']) &&
                is_array($field['subfields']) &&
                count($field['subfields'])) {
                foreach ($field['subfields'] as $subfield) {
                    if (isset($subfield['model']) && $subfield['model'] !== false) {
                        array_push($relationFields, $subfield);
                    }
                }
            }
        }

        return $relationFields;
    }

    /**
     * Create relations for the provided model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $formattedRelations  The form data.
     * @return bool|null
     */
    private function createRelationsForItem($item, $formattedRelations)
    {
        // no relations to create
        if (empty($formattedRelations)) {
            return false;
        }

        foreach ($formattedRelations as $relationMethod => $relationDetails) {
            $relation = $item->{$relationMethod}();
            $relationType = $relationDetails['relation_type'];

            switch ($relationType) {
                case 'HasOne':
                case 'MorphOne':
                        $this->createUpdateOrDeleteOneToOneRelation($relation, $relationMethod, $relationDetails);
                    break;
                case 'HasMany':
                case 'MorphMany':
                    $relationValues = $relationDetails['values'][$relationMethod];
                    // if relation values are null we can only attach, also we check if we sent
                    // - a single dimensional array: [1,2,3]
                    // - an array of arrays: [[1][2][3]]
                    // if is as single dimensional array we can only attach.
                    if ($relationValues === null || ! is_multidimensional_array($relationValues)) {
                        $this->attachManyRelation($item, $relation, $relationDetails, $relationValues);
                    } else {
                        $this->createManyEntries($item, $relation, $relationMethod, $relationDetails);
                    }
                    break;
                case 'BelongsToMany':
                case 'MorphToMany':
                    $values = $relationDetails['values'][$relationMethod] ?? [];
                    $values = is_string($values) ? json_decode($values, true) : $values;
                    $relationValues = [];

                    if (is_array($values) && is_multidimensional_array($values)) {
                        foreach ($values as $value) {
                            if(isset($value[$relationMethod])) {
                                $relationValues[$value[$relationMethod]] = Arr::except($value, $relationMethod);
                            }
                        }
                    }

                    // if there is no relation data, and the values array is single dimensional we have
                    // an array of keys with no aditional pivot data. sync those.
                    if (empty($relationValues)) {
                        $relationValues = array_values($values);
                    }

                    $item->{$relationMethod}()->sync($relationValues);
                    break;
            }
        }
    }

    /**
     * Save the attributes of a given HasOne or MorphOne relationship on the
     * related entry, create or delete it, depending on what was sent in the form.
     *
     * For HasOne and MorphOne relationships, the dev might want to a few different things:
     * (A) save an attribute on the related entry (eg. passport.number)
     * (B) set an attribute on the related entry to NULL (eg. slug.slug)
     * (C) save an entire related entry (eg. passport)
     * (D) delete the entire related entry (eg. passport)
     *
     * @param  \Illuminate\Database\Eloquent\Relations\HasOne|\Illuminate\Database\Eloquent\Relations\MorphOne  $relation
     * @param  string  $relationMethod  The name of the relationship method on the main Model.
     * @param  array  $relationDetails  Details about that relationship. For example:
     *                                  [
     *                                  'model' => 'App\Models\Passport',
     *                                  'parent' => 'App\Models\Pet',
     *                                  'entity' => 'passport',
     *                                  'attribute' => 'passport',
     *                                  'values' => **THE TRICKY BIT**,
     *                                  ]
     * @return Model|null
     */
    private function createUpdateOrDeleteOneToOneRelation($relation, $relationMethod, $relationDetails)
    {
        // Let's see which scenario we're treating, depending on the contents of $relationDetails:
        //      - (A) ['number' => 1315, 'name' => 'Something'] (if passed using a text/number/etc field)
        //      - (B) ['slug' => null] (if the 'slug' attribute on the 'slug' related entry needs to be cleared)
        //      - (C) ['passport' => [['number' => 1314, 'name' => 'Something']]] (if passed using a repeatable field)
        //      - (D) ['passport' => null] (if deleted from the repeatable field)

        // Scenario C or D
        if (array_key_exists($relationMethod, $relationDetails['values'])) {
            $relationMethodValue = $relationDetails['values'][$relationMethod];

            // Scenario D
            if (is_null($relationMethodValue) && $relationDetails['relationEntity'] === $relationMethod) {
                $relation->delete();

                return null;
            }

            // Scenario C (when it's an array inside an array, because it's been added as one item inside a repeatable field)
            if (gettype($relationMethodValue) == 'array' && is_multidimensional_array($relationMethodValue)) {
                $relationMethodValue = $relationMethodValue[0];
            }
        }
        // saving process
        $input = $relationMethodValue ?? $relationDetails['values'];
        [$directInputs, $relationInputs] = $this->splitInputIntoDirectAndRelations($input, $relationDetails, $relationMethod);

        $item = $relation->updateOrCreate([], $directInputs);

        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * Returns the direct inputs parsed for model and relationship creation.
     *
     * @param  array  $inputs
     * @param  null|array  $relationDetails
     * @param  bool|string  $relationMethod
     * @return array
     */
    private function splitInputIntoDirectAndRelations($inputs, $relationDetails = null, $relationMethod = false)
    {
        $crudFields = $relationDetails['crudFields'] ?? [];
        $model = $relationDetails['model'] ?? false;

        $directInputs = $this->getDirectInputsFromInput($inputs, $model, $crudFields, $relationMethod);
        $relationInputs = $this->getRelationDetailsFromInput($inputs, $crudFields, $relationMethod);

        return [$directInputs, $relationInputs];
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
     * @return mixed
     */
    private function attachManyRelation($item, $relation, $relationDetails, $relationValues)
    {
        $modelInstance = $relation->getRelated();
        $relationForeignKey = $relation->getForeignKeyName();
        $relationLocalKey = $relation->getLocalKeyName();

        if ($relationValues === null) {
            // the developer cleared the selection
            // we gonna clear all related values by setting up the value to the fallback id, to null or delete.
            $removedEntries = $modelInstance->where($relationForeignKey, $item->{$relationLocalKey});

            return $this->handleManyRelationItemRemoval($modelInstance, $removedEntries, $relationDetails, $relationForeignKey);
        }
        // we add the new values into the relation, if it is HasMany we only update the foreign_key,
        // otherwise (it's a MorphMany) we need to update the morphs keys too
        $toUpdate[$relationForeignKey] = $item->{$relationLocalKey};

        if ($relationDetails['relation_type'] === 'MorphMany') {
            $toUpdate[$relation->getQualifiedMorphType()] = $relation->getMorphClass();
        }

        $modelInstance->whereIn($modelInstance->getKeyName(), $relationValues)
            ->update($toUpdate);

        // we clear up any values that were removed from model relation.
        // if developer provided a fallback id, we use it
        // if column is nullable we set it to null if developer didn't specify `force_delete => true`
        // if none of the above we delete the model from database
        $removedEntries = $modelInstance->whereNotIn($modelInstance->getKeyName(), $relationValues)
                            ->where($relationForeignKey, $item->{$relationLocalKey});

        // if relation is MorphMany we also match by morph type.
        if ($relationDetails['relation_type'] === 'MorphMany') {
            $removedEntries->where($relation->getQualifiedMorphType(), $relation->getMorphClass());
        }

        return $this->handleManyRelationItemRemoval($modelInstance, $removedEntries, $relationDetails, $relationForeignKey);
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
     * @param $entry - eg: story
     * @param $relation - eg  story HasMany monsters
     * @param $relationMethod - eg: monsters
     * @param $relationDetails - eg: info about relation including submited values
     * @return void
     */
    private function createManyEntries($entry, $relation, $relationMethod, $relationDetails)
    {
        $items = $relationDetails['values'][$relationMethod];

        $relation_local_key = $relation->getLocalKeyName();

        $relatedItemsSent = [];

        foreach ($items as $item) {
            [$directInputs, $relationInputs] = $this->splitInputIntoDirectAndRelations($item, $relationDetails, $relationMethod);
            // for each item we get the inputs to create and the relations of it.
            $relation_local_key_value = $item[$relation_local_key] ?? null;

            // we either find the matched entry by local_key (usually `id`)
            // and update the values from the input
            // or create a new item from input
            $item = $relation->updateOrCreate([$relation_local_key => $relation_local_key_value], $directInputs);

            // we store the item local key do we can match them with database and check if any item was deleted
            $relatedItemsSent[] = $item->{$relation_local_key};

            // create the item relations if any
            $this->createRelationsForItem($item, $relationInputs);
        }

        // use the collection of sent ids to match agains database ids, delete the ones not found in the submitted ids.
        if (! empty($relatedItemsSent)) {
            // we perform the cleanup of removed database items
            $entry->{$relationMethod}()->whereNotIn($relation_local_key, $relatedItemsSent)->delete();
        }
    }

    /**
     * Get a relation data array from the form data. For each relation defined in the fields
     * through the entity attribute, and set some relation details.
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
     * @param  array  $crudFields  - present when getting the relation details for other relations.
     * @param  mixed  $relationMethod
     * @return array The formatted relation details.
     */
    private function getRelationDetailsFromInput($input, $crudFields = [], $relationMethod = false)
    {
        // main entity
        if (empty($crudFields)) {
            $relationFields = $this->getRelationFields();
        } else {
            // relations sends the fields that represent them so we can parse the input accordingly.
            $relationFields = $crudFields;

            foreach ($crudFields as $key => $crudField) {
                if (isset($crudField['subfields'])) {
                    foreach ($crudField['subfields'] as $crudSubField) {
                        if (isset($crudSubField['relation_type'])) {
                            $relationFields[] = $crudSubField;
                        }
                    }
                }
            }
        }

        //remove fields that are not in the submitted form input
        $relationFields = array_filter($relationFields, function ($field) use ($input) {
            return Arr::has($input, $field['name']) || isset($input[$field['name']]) || Arr::has($input, Str::afterLast($field['name'], '.'));
        });

        $relationDetails = [];

        foreach ($relationFields as $field) {
            // if relationMethod is set we strip it out of the fieldName that we use to create the relations array
            $fieldName = $relationMethod ? Str::after($field['name'], $relationMethod.'.') : $field['name'];

            $key = Str::before($this->getOnlyRelationEntity(['entity' => $fieldName]), '.');

            // if the field entity contains the attribute we want to add that attribute in the correct relation key.
            // eg: adress.street, we want to add `street` as an attribute in `address` relation, `street` is not
            // a relation of `address`
            if ($this->getOnlyRelationEntity($field) !== $field['entity']) {
                if (Str::before($field['entity'], '.') === $relationMethod) {
                    $key = Str::before($this->getOnlyRelationEntity($field), '.');
                }
            }

            $attributeName = (string) Str::of($field['name'])->afterLast('.');

            switch ($field['relation_type']) {
                case 'BelongsTo':
                    // when it's a nested belongsTo relation we want to make sure
                    // the key used to store the values is the main relation key
                    $key = Str::beforeLast($this->getOnlyRelationEntity($field), '.');

                break;
            }

            // we don't need to re-setup this relation method values, we just want the relations
            if ($key === $relationMethod) {
                continue;
            }

            $fieldDetails = Arr::get($relationDetails, $key, []);

            $fieldDetails['values'][$attributeName] = Arr::get($input, $fieldName);
            $fieldDetails['model'] = $fieldDetails['model'] ?? $field['model'];
            $fieldDetails['relation_type'] = $fieldDetails['relation_type'] ?? $field['relation_type'];
            $fieldDetails['crudFields'][] = $field;
            $fieldDetails['relationEntity'] = $this->getOnlyRelationEntity($field);
            

            if (isset($field['fallback_id'])) {
                $fieldDetails['fallback_id'] = $field['fallback_id'];
            }
            if (isset($field['force_delete'])) {
                $fieldDetails['force_delete'] = $field['force_delete'];
            }

            Arr::set($relationDetails, $key, $fieldDetails);
        }
        return $relationDetails;
    }
}
