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
        [$directInputs, $relationInputs] = $this->getParsedInputs($input);
        $item = $this->model->create($directInputs);
        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * Returns the attributes with relationships stripped out from the input
     * 
     * @param array $input
     * @param mixed $model
     * 
     * @return array
     */
    public function getDirectParsedInput($input, $model = false) {
        $input = $this->decodeJsonCastedAttributes($input, $model);
        $input = $this->compactFakeFields($input);
        $input = $this->changeBelongsToNamesFromRelationshipToForeignKey($input);
        

        $field_names_to_exclude = $this->getFieldsNamesToExclude($fields);
                                
        return Arr::except($input, $field_names_to_exclude);
    }

    /**
     * Returns the field names that should be excluded from entry saving
     * Means: exclude all relations, except BelongsTo that were set with correct key.
     * 
     * @param array $fields
     * 
     * @return array
     */
    private function getFieldsNamesToExclude($fields) {
        $fields = empty($fields) ? $this->getRelationFields() : $this->getRelationFields($fields);
        $excludedFields = [];
        foreach($fields as $field) {
            if($field['relation_type'] === 'BelongsTo') {
                $name_for_sub = $this->getOverwrittenNameForBelongsTo($field);
                $belongsToKey = Str::afterLast($field['name'], '.');
                if ($belongsToKey !== $name_for_sub) {
                    $excludedFields[] = $field;
                }
            }
        }
        return array_column($excludedFields, 'name');
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
     * @param array $fields
     * 
     * @return array The fields with model key set.
     */
    public function getRelationFields($fields = [])
    {
        if(empty($fields)) {
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
        if (! isset($formattedRelations['relations'])) {
            return false;
        }
        foreach ($formattedRelations['relations'] as $relationMethod => $relationDetails) {
            if (! isset($relationDetails['model'])) {
                continue;
            }
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

                    if (is_multidimensional_array($values)) {
                        foreach ($values as $value) {
                            $relationValues[$value[$relationMethod]] = Arr::except($value, $relationMethod);
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
            if (is_null($relationMethodValue) && $relationDetails['entity'] === $relationMethod) {
                $relation->delete();

                return null;
            }
            
            // Scenario C (when it's an array inside an array, because it's been added as one item inside a repeatable field)
            if (gettype($relationMethodValue) == 'array' && is_multidimensional_array($relationMethodValue)) {
                $relationMethodValue = $relationMethodValue[0];          
            }
        }
        // saving process       
        [$directInputs, $relationInputs] = $this->getParsedInputs($relationMethodValue ?? $relationDetails['values'], $relationDetails['model'], $relationDetails['crudFields']);
        
        $item = $relation->updateOrCreate([], $directInputs);
        
        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * Returns the direct inputs parsed for model and relationship creation
     * 
     * @param array $inputs
     * 
     * @return array
     */
    private function getParsedInputs($inputs, $model = false, $crudFields = []) {
        return [$this->getDirectParsedInput($inputs, $model, $crudFields), $this->getRelationDetailsFromInput($inputs, $crudFields)];
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
    private function attachManyRelation($item, $relation, $relationDetails, $relation_values)
    {
        $model_instance = $relation->getRelated();
        $relation_foreign_key = $relation->getForeignKeyName();
        $relation_local_key = $relation->getLocalKeyName();

        if ($relation_values === null) {
            // the developer cleared the selection
            // we gonna clear all related values by setting up the value to the fallback id, to null or delete.
            $removed_entries = $model_instance->where($relation_foreign_key, $item->{$relation_local_key});

            return $this->handleManyRelationItemRemoval($model_instance, $removed_entries, $relationDetails, $relation_foreign_key);
        }
        // we add the new values into the relation
        $model_instance->whereIn($model_instance->getKeyName(), $relation_values)
            ->update([$relation_foreign_key => $item->{$relation_local_key}]);

        // we clear up any values that were removed from model relation.
        // if developer provided a fallback id, we use it
        // if column is nullable we set it to null if developer didn't specify `force_delete => true`
        // if none of the above we delete the model from database
        $removed_entries = $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                            ->where($relation_foreign_key, $item->{$relation_local_key});

        return $this->handleManyRelationItemRemoval($model_instance, $removed_entries, $relationDetails, $relation_foreign_key);
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
     * 
     * @return void
     */
    private function createManyEntries($entry, $relation, $relationMethod, $relationDetails) 
    {
        $items = $relationDetails['values'][$relationMethod]; 

        $relation_local_key = $relation->getLocalKeyName();

        $relation_local_key_value = $item[$relation_local_key] ?? false;

        $relatedItemsSent = [];

        foreach ($items as $item) {
            // for each item we get the inputs to create and the relations of it.
            [$directInputs, $relationInputs] = $this->getParsedInputs($item, $relationDetails['model'], $relationDetails['crudFields']);

            // we either find the matched entry by local_key (usually `id`)
            // and update the values from the input
            // or create a new item from input
            $item = $relation->firstOrCreate([$relation_local_key => $relation_local_key_value], $directInputs);

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
        $relationFields = $this->getRelationFields();

        // exclude the already attached belongs to relations in the main entry but include nested belongs to.
        $relationFields = Arr::where($relationFields, function ($field, $key) {
            return $field['relation_type'] !== 'BelongsTo' || ($field['relation_type'] === 'BelongsTo' && Str::contains($field['name'], '.'));
        });

        //remove fields that are not in the submitted form input
        $relationFields = array_filter($relationFields, function ($field) use ($input) {
            return Arr::has($input, $field['name']);
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
            $fieldDetails['entity'] = $fieldDetails['entity'] ?? $field['entity'];
            $fieldDetails['attribute'] = $fieldDetails['attribute'] ?? $field['attribute'];
            $fieldDetails['relation_type'] = $fieldDetails['relation_type'] ?? $field['relation_type'];
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

    /**
     * Returns an array of field names, after we keep only what's before the dots.
     * Field names that use dot notation are considered as being "grouped fields"
     * eg: address.city, address.postal_code
     * And for all those fields, this function will only return one field name (what is before the dot).
     *
     * @param  array  $fields  - the fields from where the name would be returned.
     * @return array
     */
    private function getFieldNamesBeforeFirstDot($fields)
    {
        $field_names_array = [];

        foreach ($fields as $field) {
            $field_names_array[] = Str::before($field['name'], '.');
        }

        return array_unique($field_names_array);
    }
}
