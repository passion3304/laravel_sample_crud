<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Relationships
{
    /**
     * From the field entity we get the relation instance.
     *
     * @param  array  $entity
     * @return object
     */
    public function getRelationInstance($field)
    {
        $entity = $this->getOnlyRelationEntity($field);
        $possible_method = Str::before($entity, '.');
        $model = isset($field['baseModel']) ? app($field['baseModel']) : $this->model;

        if (method_exists($model, $possible_method)) {
            $parts = explode('.', $entity);
            // here we are going to iterate through all relation parts to check
            foreach ($parts as $i => $part) {
                $relation = $model->$part();
                $model = $relation->getRelated();
            }

            return $relation;
        }

        abort(500, 'Looks like field <code>'.$field['name'].'</code> is not properly defined. The <code>'.$field['entity'].'()</code> relationship doesn\'t seem to exist on the <code>'.get_class($model).'</code> model.');
    }

    /**
     * Grabs an relation instance and returns the class name of the related model.
     *
     * @param  array  $field
     * @return string
     */
    public function inferFieldModelFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        return get_class($relation->getRelated());
    }

    /**
     * Return the relation type from a given field: BelongsTo, HasOne ... etc.
     *
     * @param  array  $field
     * @return string
     */
    public function inferRelationTypeFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        return Arr::last(explode('\\', get_class($relation)));
    }

    public function getOnlyRelationEntity($field)
    {
        $entity = isset($field['baseEntity']) ? $field['baseEntity'].'.'.$field['entity'] : $field['entity'];
        $model = $this->getRelationModel($entity, -1);
        $lastSegmentAfterDot = Str::of($field['entity'])->afterLast('.');

        if (! method_exists($model, $lastSegmentAfterDot)) {
            return (string) Str::of($field['entity'])->beforeLast('.');
        }

        return $field['entity'];
    }

    /**
     * Get the fields for relationships, according to the relation type. It looks only for direct
     * relations - it will NOT look through relationships of relationships.
     *
     * @param  string|array  $relation_types  Eloquent relation class or array of Eloquent relation classes. Eg: BelongsTo
     * @param  bool  $nested  Should nested fields be included
     * @return array The fields with corresponding relation types.
     */
    public function getFieldsWithRelationType($relation_types, $nested = false): array
    {
        $relation_types = (array) $relation_types;

        return collect($this->getCleanStateFields())
            ->whereIn('relation_type', $relation_types)
            ->filter(function ($item) use ($nested) {
                if ($nested) {
                    return true;
                }

                return Str::contains($item['entity'], '.') ? false : true;
            })
            ->toArray();
    }

    /**
     * Parse the field name back to the related entity after the form is submited.
     * Its called in getAllFieldNames().
     *
     * @param  array  $fields
     * @return array
     */
    public function parseRelationFieldNamesFromHtml($fields)
    {
        foreach ($fields as &$field) {
            //we only want to parse fields that has a relation type and their name contains [ ] used in html.
            if (isset($field['relation_type']) && preg_match('/[\[\]]/', $field['name']) !== 0) {
                $chunks = explode('[', $field['name']);

                foreach ($chunks as &$chunk) {
                    if (strpos($chunk, ']')) {
                        $chunk = str_replace(']', '', $chunk);
                    }
                }
                $field['name'] = implode('.', $chunks);
            }
        }

        return $fields;
    }

    /**
     * Gets the relation fields that DON'T contain the provided relations.
     *
     * @param  string|array  $relations  - the relations to exclude
     * @param  array  $fields
     */
    private function getRelationFieldsWithoutRelationType($relations, $fields = [])
    {
        if (! is_array($relations)) {
            $relations = [$relations];
        }

        if (empty($fields)) {
            $fields = $this->getRelationFields();
        }

        foreach ($relations as $relation) {
            $fields = array_filter($fields, function ($field) use ($relation) {
                if (! isset($field['relation_type'])) {
                    return false;
                }

                return $field['relation_type'] !== $relation;
            });
        }

        return $fields;
    }

    /**
     * Changes the input names to use the foreign_key, instead of the relation name,
     * for BelongsTo relations (eg. "user_id" instead of "user").
     *
     * When $fields are provided, we will use those fields to determine the correct
     * foreign key. Otherwise, we will use the main CRUD fields.
     *
     * eg: user -> user_id
     *
     * @param  array  $input
     * @param  array  $belongsToFields
     * @return array
     */
    private function changeBelongsToNamesFromRelationshipToForeignKey($input, $fields = [])
    {
        if (empty($fields)) {
            $fields = $this->getFieldsWithRelationType('BelongsTo');
        } else {
            foreach ($fields as $field) {
                if (isset($field['subfields'])) {
                    $fields = array_merge($field['subfields'], $fields);
                }
            }
            $fields = array_filter($fields, function ($field) {
                return isset($field['relation_type']) && $field['relation_type'] === 'BelongsTo';
            });
        }

        foreach ($fields as $field) {
            $foreignKey = $this->getOverwrittenNameForBelongsTo($field);
            $lastFieldNameSegment = Str::afterLast($field['name'], '.');

            if (Arr::has($input, $lastFieldNameSegment) && $lastFieldNameSegment !== $foreignKey) {
                Arr::set($input, $foreignKey, Arr::get($input, $lastFieldNameSegment));
                Arr::forget($input, $lastFieldNameSegment);
            }
        }

        return $input;
    }

    /**
     * MorphTo inputs (_type and _id) are used as subfields to represent the relation.
     * Here we add them to the direct input as we don't need to process any further.
     *
     * @param  array  $input
     * @param  array  $fields
     * @return array
     */
    private function addMorphToInputsFromRelationship($input)
    {
        $fields = $this->getFieldsWithRelationType('MorphTo');

        foreach ($fields as $field) {
            [$morphTypeField, $morphIdField] = $field['subfields'];
            Arr::set($input, $morphTypeField['name'], Arr::get($input, $field['name'].'.'.$morphTypeField['name']));
            Arr::set($input, $morphIdField['name'], Arr::get($input, $field['name'].'.'.$morphIdField['name']));
        }

        return $input;
    }

    /**
     * Based on relation type returns if relation allows multiple entities.
     *
     * @param  string  $relation_type
     * @return bool
     */
    public function guessIfFieldHasMultipleFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasMany':
            case 'HasManyThrough':
            case 'HasOneOrMany':
            case 'MorphMany':
            case 'MorphOneOrMany':
            case 'MorphToMany':
                return true;

            default:
                return false;
        }
    }

    /**
     * Based on relation type returns if relation has a pivot table.
     *
     * @param  string  $relation_type
     * @return bool
     */
    public function guessIfFieldHasPivotFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasManyThrough':
            case 'MorphToMany':
                return true;
            break;
            default:
                return false;
        }
    }

    /**
     * Get all relation fields that don't have pivot set.
     *
     * @return array The fields with model key set.
     */
    public function getRelationFieldsWithoutPivot()
    {
        $all_relation_fields = $this->getRelationFields();

        return Arr::where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && ! $value['pivot'];
        });
    }

    /**
     * Get all fields with n-n relation set (pivot table is true).
     *
     * @return array The fields with n-n relationships.
     */
    public function getRelationFieldsWithPivot()
    {
        $all_relation_fields = $this->getRelationFields();

        return Arr::where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && $value['pivot'];
        });
    }

    /**
     * This function created the MorphTo relation fields in the CrudPanel.
     *
     * @param  array  $field
     * @return void
     */
    private function createMorphToRelationFields(array $field, $morphTypeFieldName, $morphIdFieldName)
    {
        $morphTypeField = static::getMorphTypeFieldStructure($field['name'], $morphTypeFieldName);
        $morphIdField = static::getMorphIdFieldStructure($field['name'], $morphIdFieldName, $morphTypeFieldName);
        $morphIdField['morphMap'] = $morphTypeField['morphMap'] = (new $this->model)->{$field['name']}()->morphMap();
        $field['subfields'] = [$morphTypeField, $morphIdField];

        return $field;
    }

    /**
     * return the relation field names for a morphTo field.
     *
     * @param  string  $relationName  the morphto relation name
     * @return array
     */
    public function getMorphToFieldNames(string $relationName)
    {
        $relation = (new $this->model)->{$relationName}();

        return [$relation->getMorphType(), $relation->getForeignKeyName()];
    }

    /**
     * Return the name for the BelongTo relation making sure it always has the
     * foreign_key instead of relationName (eg. "user_id", not "user").
     *
     * @param  array  $field  The field we want to get the name from
     * @return string
     */
    private function getOverwrittenNameForBelongsTo($field)
    {
        $relation = $this->getRelationInstance($field);

        if (Str::afterLast($field['name'], '.') === $relation->getRelationName()) {
            return $relation->getForeignKeyName();
        }

        return $field['name'];
    }

    /**
     * Returns the pivot definition for BelongsToMany/MorphToMany relation provided in $field.
     *
     * @param  array  $field
     * @return array
     */
    private static function getPivotFieldStructure($field)
    {
        $pivotSelectorField['name'] = $field['name'];
        $pivotSelectorField['type'] = 'relationship';
        $pivotSelectorField['is_pivot_select'] = true;
        $pivotSelectorField['multiple'] = false;
        $pivotSelectorField['entity'] = $field['name'];
        $pivotSelectorField['relation_type'] = $field['relation_type'];
        $pivotSelectorField['model'] = $field['model'];
        $pivotSelectorField['minimum_input_length'] = 2;
        $pivotSelectorField['delay'] = 500;
        $pivotSelectorField['placeholder'] = trans('backpack::crud.select_entry');
        $pivotSelectorField['label'] = \Str::of($field['name'])->singular()->ucfirst();
        $pivotSelectorField['validationRules'] = 'required';
        $pivotSelectorField['validationMessages'] = [
            'required' => trans('backpack::crud.pivot_selector_required_validation_message'),
        ];

        if (isset($field['baseModel'])) {
            $pivotSelectorField['baseModel'] = $field['baseModel'];
        }
        if (isset($field['baseEntity'])) {
            $pivotSelectorField['baseEntity'] = $field['baseEntity'];
        }

        return $pivotSelectorField;
    }

    /**
     * Checks the properties of the provided method to better verify if it could be a relation.
     * Case the method is not public, is not a relation.
     * Case the return type is Attribute, or extends Attribute is not a relation method.
     * If the return type extends the Relation class is for sure a relation
     * Otherwise we just assume it's a relation.
     *
     * DEV NOTE: In future versions we will return `false` when no return type is set and make the return type mandatory for relationships.
     *           This function should be refactored to only check if $returnType is a subclass of Illuminate\Database\Eloquent\Relations\Relation.
     *
     * @param $model
     * @param $method
     * @return bool|string
     */
    private function modelMethodIsRelationship($model, $method)
    {
        $methodReflection = new \ReflectionMethod($model, $method);

        // relationship methods function does not have parameters
        if ($methodReflection->getNumberOfParameters() > 0) {
            return false;
        }

        // relationships are always public methods.
        if (! $methodReflection->isPublic()) {
            return false;
        }

        $returnType = $methodReflection->getReturnType();

        if ($returnType) {
            $returnType = $returnType->getName();

            if (is_a($returnType, 'Illuminate\Database\Eloquent\Casts\Attribute', true)) {
                return false;
            }

            if (is_a($returnType, 'Illuminate\Database\Eloquent\Relations\Relation', true)) {
                return $method;
            }
        }

        return $method;
    }

    /**
     * This function is responsible for setting up the morph fields structure.
     * Developer can define the morph structure as follows:
     *  'morphOptions => [
     *       ['nameOnAMorphMap', 'label', [options]],
     *       ['App\Models\Model'], // display the name of the model
     *       ['App\Models\Model', 'label', ['data_source' => backpack_url('smt')]
     *  ]
     * OR
     * ->addMorphOption('App\Models\Model', 'label', ['data_source' => backpack_url('smt')]).
     *
     * @param  string|array  $fieldName
     * @param  string  $key
     * @param  string|null  $label
     * @param  array  $options
     * @return void|array
     */
    public function addMorphOption($fieldName, string $key, $label = null, array $options)
    {
        $morphField = is_array($fieldName) ? $fieldName : $this->fields()[$fieldName];

        $fieldName = $morphField['name'];

        [$morphTypeFieldName, $morphIdFieldName] = $this->getMorphToFieldNames($fieldName);

        if (! in_array($morphTypeFieldName, array_column($morphField['subfields'], 'name')) ||
            ! in_array($morphIdFieldName, array_column($morphField['subfields'], 'name'))) {
            throw new \Exception('Trying to add morphOptions to a non morph field. Check if field and relation name matches.');
        }

        [$morphTypeField, $morphIdField] = $morphField['subfields'];

        $morphMap = $morphTypeField['morphMap'];

        if (array_key_exists($key, $morphTypeField['options'] ?? [])) {
            throw new \Exception('Duplicate entry for «'.$key.'» in addMorphOption().');
        }

        if (is_a($key, 'Illuminate\Database\Eloquent\Model', true)) {
            if (in_array($key, $morphMap)) {
                $key = $morphMap[array_search($key, $morphMap)];

                if (array_key_exists($key, $morphTypeField['options'])) {
                    throw new \Exception('Duplicate entry for «'.$key.'» in morphOptions');
                }
            }
            $morphTypeField['options'][$key] = $label ?? Str::afterLast($key, '\\');
        } else {
            if (! array_key_exists($key, $morphMap)) {
                throw new \Exception('Unknown morph type «'.$key.'», either the class doesnt exists, or the name was not found in the morphMap');
            }

            if (array_key_exists($key, $morphTypeField['options'])) {
                throw new \Exception('Duplicate entry for «'.$key.'» in morphOptions');
            }

            $morphTypeField['options'][$key] = $label ?? ucfirst($key);
        }

        $morphIdField['morphOptions'][$key] = $options;

        $morphTypeField = isset($morphField['morphTypeField']) ? array_merge($morphTypeField, $morphField['morphTypeField']) : $morphTypeField;
        $morphIdField = isset($morphField['morphIdField']) ? array_merge($morphIdField, $morphField['morphIdField']) : $morphIdField;

        $morphField['subfields'] = [$morphTypeField, $morphIdField];

        if ($this->fields()[$fieldName] ?? false) {
            $this->modifyField($fieldName, $morphField);
        } else {
            return $morphField;
        }
    }

    /**
     * Returns the morphable_id field structure for morphTo relations.
     *
     * @param  string  $relationName
     * @param  string  $morphIdFieldName
     * @return array
     */
    private static function getMorphidFieldStructure($relationName, $morphIdFieldName, $morphTypeFieldName)
    {
        return [
            'name' => $morphIdFieldName,
            'type' => 'relationship.morphTo_select',
            'entity' => false,
            'placeholder' => 'Select the '.$relationName,
            'allows_null' => true,
            'allow_multiple' => false,
            'morphTypeFieldName' => $morphTypeFieldName,
            'attributes' => [
                'data-morph-select' => $relationName.'-morph-select',
            ],
            'wrapper' => ['class' => 'form-group col-md-9'],
        ];
    }

    /**
     * Returns the morphable_type field structure for morphTo relations.
     *
     * @param  string  $relationName
     * @param  string  $morphTypeFieldName
     * @return array
     */
    private static function getMorphTypeFieldStructure($relationName, $morphTypeFieldName)
    {
        return [
            'name' => $morphTypeFieldName,
            'type' => 'relationship.morphTo_type_select',
            'placeholder' => 'Select the '.$relationName,
            'attributes' => [
                $relationName.'-morph-select' => true,
            ],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ];
    }

    /**
     * return the array with defaults for a morphOption structure.
     *
     * @param  array  $morphOption
     * @return array
     */
    private function getMorphOptionStructured(array $morphOption)
    {
        return [$morphOption[0] ?? null, $morphOption[1] ?? null, $morphOption[2] ?? []];
    }
}
