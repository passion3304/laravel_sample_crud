<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait MorphRelationships
{
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
     * Return the relation field names for a morphTo field.
     *
     * @param  string  $relationName  the morphto relation name
     * @return array
     */
    private function getMorphToFieldNames(string $relationName)
    {
        $relation = (new $this->model)->{$relationName}();

        return [$relation->getMorphType(), $relation->getForeignKeyName()];
    }

    /**
     * Make sure morph fields have the correct structure.
     *
     * @param  array  $field
     * @return array
     */
    private function makeSureMorphSubfieldsAreDefined(array $field)
    {
        if (isset($field['relation_type']) && $field['relation_type'] === 'MorphTo') {
            [$morphTypeFieldName, $morphIdFieldName] = $this->getMorphToFieldNames($field['name']);
            if (! $this->hasFieldWhere('name', $morphTypeFieldName) || ! $this->hasFieldWhere('name', $morphIdFieldName)) {
                // create the morph fields in the crud panel
                $field = $this->createMorphToRelationFields($field, $morphTypeFieldName, $morphIdFieldName);
                foreach ($field['morphOptions'] ?? [] as $morphOption) {
                    [$key, $label, $options] = $this->getMorphOptionStructured($morphOption);
                    $field = $this->addMorphOption($field, $key, $label, $options);
                }
            }
        }

        return $field;
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
    public function addMorphOption($fieldName, string $key, $label = null, array $options = [])
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
            'placeholder' => 'Select an entry',
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
            'placeholder' => 'Select an entry',
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
