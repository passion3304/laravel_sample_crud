<?php

namespace Backpack\CRUD\PanelTraits;

trait RequiredFields
{
    /*
    |--------------------------------------------------------------------------
    |                             REQUIRED FIELDS
    |--------------------------------------------------------------------------
    */
    public $requiredFields = [];

    /**
     * Parse a FormRequest class, figure out what inputs are required
     * and store this knowledge in the current object.
     *
     * @param [type] $class     Class that extends FormRequest
     * @param [type] $operation create / update
     */
    public function setRequired($class, $operation)
    {
        $formRequest = new $class;

        $rules = $formRequest->rules();

        if (count($rules)) {
            foreach ($rules as $key => $rule) {
                if (strpos($rule, 'required') !== false) {
                    $this->requiredFields[$operation][] = $key;
                }
            }
        }
    }

    /**
     * Check the current object to see if an input is required
     * for the given operation.
     * @param  [type]  $inputName Field or input name.
     * @param  [type]  $operation create / update
     * @return boolean
     */
    public function isRequired($inputName, $operation)
    {
        if (!isset($this->requiredFields[$operation])) {
            return false;
        }

        return in_array($inputName, $this->requiredFields[$operation]);
    }
}
