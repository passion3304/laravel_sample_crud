<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

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
        $field = $this->makeSureFieldHasRelationshipData($field);
        $field = $this->makeSureFieldHasModel($field);
        $field = $this->makeSureFieldHasLabel($field);
        $field = $this->makeSureFieldHasType($field);
        $field = $this->makeSureFieldEnablesTabs($field);

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

        return $field;
    }

    protected function makeSureFieldHasRelationshipData($field)
    {
        // we set this up exclusive for relationship type field
        // atm we avoid any breaking changes while developing the guessing abilities for crud panel
        if (isset($field['type']) && $field['type'] == 'relationship') {
            $relationData = $this->getRelationFromFieldName($field['name']);
            if ($relationData) {
                $field = array_merge($field, $relationData);
            } else {
                abort(500, 'Unable to process relationship field: '.$field['name']);
            }
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

    protected function makeSureFieldHasLabel($field)
    {
        // if the label is missing, we should set it
        if (! isset($field['label'])) {
            $label = is_array($field['name']) ? $field['name'][0] : $field['name'];
            $field['label'] = mb_ucfirst(str_replace('_', ' ', $label));
        }

        return $field;
    }

    protected function makeSureFieldHasType($field)
    {
        // if the field type is missing, we should set it
        if (! isset($field['type'])) {
            $field['type'] = $this->getFieldTypeFromDbColumnType($field['name']);
        }

        return $field;
    }

    protected function makeSureFieldEnablesTabs($field)
    {
        // if a tab was mentioned, we should enable it
        if (isset($field['tab'])) {
            if (! $this->tabsEnabled()) {
                $this->enableTabs();
            }
        }

        return $field;
    }

    protected function addFieldToOperationSettings($field)
    {
        $fieldKey = $this->getFieldKey($field);

        $allFields = $this->getOperationSetting('fields');
        $allFields = array_add($this->fields(), $fieldKey, $field);

        $this->setOperationSetting('fields', $allFields);
    }

    // the array key for the field should be:
    // - name (if the name is a string)
    // - name1_name2_name3 (if the name is an array)
    protected function getFieldKey($field)
    {
        if (is_array($field['name'])) {
            return implode('_', $field['name']);
        }

        return $field['name'];
    }
}
