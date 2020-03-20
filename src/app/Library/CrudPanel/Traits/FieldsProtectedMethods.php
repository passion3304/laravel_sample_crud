<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait FieldsProtectedMethods
{
    /**
     * The only REALLY MANDATORY attribute when defining a field is the 'name'.
     * Everything else Backpack can probably guess. This method makes sure  the
     * field definition array is complete, by guessing missing attributes.
     *
     * @param  string|array $field  The definition of a field (string or array).
     * @return array                The correct definition of that field.
     */
    protected function makeSureFieldHasNecessaryAttributes($field)
    {
        $field = $this->makeSureFieldHasName($field);
        $field = $this->makeSureFieldHasModel($field);
        $field = $this->makeSureFieldHasLabel($field);
        $field = $this->makeSureFieldHasEntity($field);
        $field = $this->makeSureFieldHasAttribute($field);
        $field = $this->makeSureFieldHasRelationshipData($field);
        $field = $this->makeSureFieldHasType($field);

        return $field;
    }

    /**
     * If the field_definition_array array is a string, it means the programmer was lazy
     * and has only passed the name of the field. Turn that into a proper array.
     *
     * @param  string|array $field The field definition array (or string).
     * @return array
     */
    protected function makeSureFieldHasName($field)
    {
        if (is_string($field)) {
            return ['name' => $field];
        }

        if (is_array($field) && ! isset($field['name'])) {
            abort(500, 'All fields must have their name defined');
        }

        return $field;
    }

    /**
     * If entity is not present, but it looks like the field SHOULD be a relationship field,
     * try to determine the method on the model that defines the relationship, and pass it to
     * the field as 'entity'.
     *
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    protected function makeSureFieldHasEntity($field)
    {
        if (isset($field['entity'])) {
            return $field;
        }

        // if the name is an array it's definitely not a relationship
        if (is_array($field['name'])) {
            return $field;
        }

        // if there's a method on the model with this name
        if (method_exists($this->model, $field['name'])) {
            $field['entity'] = $field['name'];

            return $field;
        } // TODO: also check if that method is a relationship (returns Relation)

        // if the name ends with _id and that method exists,
        // we can probably use it as an entity
        if (Str::endsWith($field['name'], '_id')) {
            $possibleMethodName = Str::replaceLast('_id', '', $field['name']);

            if (method_exists($this->model, $possibleMethodName)) {
                $field['entity'] = $possibleMethodName;

                return $field;
            } // TODO: also check if that method is a relationship (returns Relation)
        }

        // if there's a column in the db for this field name
        // most likely it doesn't need 'entity', UNLESS it's a foreign key
        // TODO: make this work
        // if ($this->checkIfFieldNameBelongsToAnyRelation($field['name'])) {
        //     $field['entity'] = RELATIONSHIP_METHOD;
        // }

        return $field;
    }

    protected function makeSureFieldHasRelationshipData($field)
    {
        // only do this if "entity" is defined on the field
        if (! isset($field['entity'])) {
            return $field;
        }

        $extraFieldAttributes = $this->inferFieldAttributesFromRelationship($field['entity']);

        if ($extraFieldAttributes !== false) {
            $field = array_merge($extraFieldAttributes, $field);
        } else {
            abort(500, 'Unable to process relationship data: '.$field['name']);
        }

        return $field;
    }

    protected function makeSureFieldHasModel($field)
    {
        // if this is a relation type field and no corresponding model was specified,
        // get it from the relation method defined in the main model
        if (isset($field['entity']) && ! isset($field['model'])) {
            $field['model'] = $this->getRelationModel($field['entity']);
        }

        return $field;
    }

    protected function makeSureFieldHasAttribute($field)
    {
        // if there's a model defined, but no attribute
        // guess an attribute using the indentifiableAttribute functionality in CrudTrait
        if (isset($field['model']) && ! isset($field['attribute'])) {
            $field['attribute'] = call_user_func([(new $field['model']), 'identifiableAttribute']);
        }

        return $field;
    }

    /**
     * Set the label of a field, if it's missing, by capitalizing the name and replacing
     * underscores with spaces.
     *
     * @param  array $field Field definition array.
     * @return array        Field definition array that contains label too.
     */
    protected function makeSureFieldHasLabel($field)
    {
        if (! isset($field['label'])) {
            $name = is_array($field['name']) ? $field['name'][0] : $field['name'];
            $name = str_replace('_id', '', $name);
            $field['label'] = mb_ucfirst(str_replace('_', ' ', $name));
        }

        return $field;
    }

    /**
     * Set the type of a field, if it's missing, by inferring it from the
     * db column type.
     *
     * @param  array $field Field definition array.
     * @return array        Field definition array that contains type too.
     */
    protected function makeSureFieldHasType($field)
    {
        if (! isset($field['type'])) {
            $field['type'] = $this->inferFieldTypeFromDbColumnType($field['name']);
        }

        return $field;
    }

    /**
     * Enable the tabs functionality, if a field has a tab defined.
     *
     * @param  array $field Field definition array.
     * @return void
     */
    protected function enableTabsIfFieldUsesThem($field)
    {
        // if a tab was mentioned, we should enable it
        if (isset($field['tab'])) {
            if (! $this->tabsEnabled()) {
                $this->enableTabs();
            }
        }
    }

    /**
     * Add a field to the current operation, using the Settings API.
     *
     * @param  array $field Field definition array.
     */
    protected function addFieldToOperationSettings($field)
    {
        $fieldKey = $this->getFieldKey($field);

        $allFields = $this->getOperationSetting('fields');
        $allFields = Arr::add($this->fields(), $fieldKey, $field);

        $this->setOperationSetting('fields', $allFields);
    }

    /**
     * Get the string that should be used as an array key, for the attributive array
     * where the fields are stored for the current operation.
     *
     * The array key for the field should be:
     * - name (if the name is a string)
     * - name1_name2_name3 (if the name is an array)
     *
     * @param  array $field Field definition array.
     * @return string       The string that should be used as array key.
     */
    protected function getFieldKey($field)
    {
        if (is_array($field['name'])) {
            return implode('_', $field['name']);
        }

        return $field['name'];
    }
}
