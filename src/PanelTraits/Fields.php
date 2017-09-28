<?php

namespace Backpack\CRUD\PanelTraits;

trait Fields
{
    // ------------
    // FIELDS
    // ------------

    /**
     * Add a field to the create/update form or both.
     *
     * @param string   $form    The form to add the field to (create/update/both)
     */
    public function addField($field, $form = 'both')
    {
        // if the field_definition_array array is a string, it means the programmer was lazy and has only passed the name
        // set some default values, so the field will still work
        if (is_string($field)) {
            $complete_field_array['name'] = $field;
        } else {
            $complete_field_array = $field;
        }

        // if this is a relation type field and no corresponding model was specified, get it from the relation method
        // defined in the main model
        if (isset($complete_field_array['entity']) && ! isset($complete_field_array['model'])) {
            $complete_field_array['model'] = $this->getRelationModel($complete_field_array['entity']);
        }

        // if the label is missing, we should set it
        if (! isset($complete_field_array['label'])) {
            $complete_field_array['label'] = ucfirst($complete_field_array['name']);
        }

        // if the field type is missing, we should set it
        if (! isset($complete_field_array['type'])) {
            $complete_field_array['type'] = $this->getFieldTypeFromDbColumnType($complete_field_array['name']);
        }

        // if a tab was mentioned, we should enable it
        if (isset($complete_field_array['tab'])) {
            if (! $this->tabsEnabled()) {
                $this->enableTabs();
            }
        }

        // store the field information into the correct variable on the CRUD object
        switch (strtolower($form)) {
            case 'create':
                $this->create_fields[$complete_field_array['name']] = $complete_field_array;
                break;

            case 'update':
                $this->update_fields[$complete_field_array['name']] = $complete_field_array;
                break;

            default:
                $this->create_fields[$complete_field_array['name']] = $complete_field_array;
                $this->update_fields[$complete_field_array['name']] = $complete_field_array;
                break;
        }

        return $this;
    }

    public function addFields($fields, $form = 'both')
    {
        if (count($fields)) {
            foreach ($fields as $field) {
                $this->addField($field, $form);
            }
        }
    }

    /**
     * Move the most recently added field after the given target field.
     *
     * @param string $targetFieldName The target field name.
     * @param string $form The CRUD form. Can be 'create', 'update' or 'both'. Default is 'both'.
     */
    public function afterField($targetFieldName, $form = 'both')
    {
        $this->moveFieldInForm($targetFieldName, $form, false);
    }

    /**
     * Move the most recently added field before the given target field.
     *
     * @param string $targetFieldName The target field name.
     * @param string $form The CRUD form. Can be 'create', 'update' or 'both'. Default is 'both'.
     */
    public function beforeField($targetFieldName, $form = 'both')
    {
        $this->moveFieldInForm($targetFieldName, $form);
    }

    /**
     * Move the most recently added field from a given form before or after the given target field. Default is before.
     *
     * @param string $targetFieldName The target field name.
     * @param string $form The CRUD form. Can be 'create', 'update' or 'both'. Default is 'both'.
     * @param bool $before If true, the field will be moved before the target field, otherwise it will be moved after it.
     */
    protected function moveFieldInForm($targetFieldName, $form = 'both', $before = true)
    {
        switch ($form) {
            case 'create':
                $this->moveField($this->create_fields, $targetFieldName, $before);
                break;
            case 'update':
                $this->moveField($this->update_fields, $targetFieldName, $before);
                break;
            default:
                $this->moveField($this->create_fields, $targetFieldName, $before);
                $this->moveField($this->update_fields, $targetFieldName, $before);
                break;
        }
    }

    /**
     * Move the most recently added field before or after the given target field. Default is before.
     *
     * @param array $fields The form fields.
     * @param string $targetFieldName The target field name.
     * @param bool $before If true, the field will be moved before the target field, otherwise it will be moved after it.
     */
    private function moveField(&$fields, $targetFieldName, $before = true)
    {
        if (array_key_exists($targetFieldName, $fields)) {
            $targetFieldPosition = $before ? array_search($targetFieldName, array_keys($fields))
                : array_search($targetFieldName, array_keys($fields)) + 1;

            if ($targetFieldPosition >= (count($fields) - 1)) {
                // target field name is same as element
                return;
            }

            $element = array_pop($fields);
            $beginningArrayPart = array_slice($fields, 0, $targetFieldPosition, true);
            $endingArrayPart = array_slice($fields, $targetFieldPosition, null, true);

            $fields = array_merge($beginningArrayPart, [$element['name'] => $element], $endingArrayPart);
        }
    }

    /**
     * Remove a certain field from the create/update/both forms by its name.
     *
     * @param string $name Field name (as defined with the addField() procedure)
     * @param string $form update/create/both
     */
    public function removeField($name, $form = 'both')
    {
        switch (strtolower($form)) {
            case 'create':
                array_forget($this->create_fields, $name);
                break;

            case 'update':
                array_forget($this->update_fields, $name);
                break;

            default:
                array_forget($this->create_fields, $name);
                array_forget($this->update_fields, $name);
                break;
        }
    }

    /**
     * Remove many fields from the create/update/both forms by their name.
     *
     * @param array  $array_of_names A simple array of the names of the fields to be removed.
     * @param string $form           update/create/both
     */
    public function removeFields($array_of_names, $form = 'both')
    {
        if (! empty($array_of_names)) {
            foreach ($array_of_names as $name) {
                $this->removeField($name, $form);
            }
        }
    }

    /**
     * Check if field is the first of its type in the given fields array.
     * It's used in each field_type.blade.php to determine wether to push the css and js content or not (we only need to push the js and css for a field the first time it's loaded in the form, not any subsequent times).
     *
     * @param array $field        The current field being tested if it's the first of its type.
     * @param array $fields_array All the fields in that particular form.
     *
     * @return bool true/false
     */
    public function checkIfFieldIsFirstOfItsType($field, $fields_array)
    {
        $first_field = $this->getFirstOfItsTypeInArray($field['type'], $fields_array);

        if ($field['name'] == $first_field['name']) {
            return true;
        }

        return false;
    }

    /**
     * Order the fields in a certain way.
     *
     * @param [string] Column name.
     * @param [attributes and values array]
     */
    public function setFieldOrder($fields)
    {
        // TODO
    }

    // ALIAS of setFieldOrder($fields)
    public function setFieldsOrder($fields)
    {
        $this->setFieldOrder($fields);
    }

    /**
     * Decode attributes that are casted as array/object/json in the model.
     * So that they are not json_encoded twice before they are stored in the db
     * (once by Backpack in front-end, once by Laravel Attribute Casting).
     */
    public function decodeJsonCastedAttributes($data, $form, $id = false)
    {
        // get the right fields according to the form type (create/update)
        $fields = $this->getFields($form, $id);
        $casted_attributes = $this->model->getCastedAttributes();

        foreach ($fields as $field) {

            // Test the field is castable
            if (isset($field['name']) && array_key_exists($field['name'], $casted_attributes)) {

                // Handle JSON field types
                $jsonCastables = ['array', 'object', 'json'];
                $fieldCasting = $casted_attributes[$field['name']];

                if (in_array($fieldCasting, $jsonCastables) && isset($data[$field['name']]) && ! empty($data[$field['name']]) && ! is_array($data[$field['name']])) {
                    try {
                        $data[$field['name']] = json_decode($data[$field['name']]);
                    } catch (Exception $e) {
                        $data[$field['name']] = [];
                    }
                }
            }
        }

        return $data;
    }

    public function getCurrentFields()
    {
        if ($this->entry) {
            return $this->getUpdateFields($this->entry->getKey());
        }

        return $this->getCreateFields();
    }

    // ------------
    // TONE FUNCTIONS - UNDOCUMENTED, UNTESTED, SOME MAY BE USED
    // ------------
    // TODO: check them

    public function orderFields($order)
    {
        $this->setSort('fields', (array) $order);
    }
}
