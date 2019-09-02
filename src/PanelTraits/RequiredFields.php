<?php

namespace Backpack\CRUD\PanelTraits;

trait RequiredFields
{
    /*
    |--------------------------------------------------------------------------
    |                             REQUIRED FIELDS
    |--------------------------------------------------------------------------
    */

    /**
     * Parse a FormRequest class, figure out what inputs are required
     * and store this knowledge in the current object.
     *
     * @param string $class     Class that extends FormRequest
     * @param string $operation create/update
     */
    public function setRequiredFields($class, $operation = null)
    {
        $operation = $operation ?? $this->getCurrentOperation();

        $formRequest = new $class;
        $rules = $formRequest->rules();
        $requiredFields = [];

        if (count($rules)) {
            foreach ($rules as $key => $rule) {
                if (
                    (is_string($rule) && strpos($rule, 'required') !== false && strpos($rule, 'required_') === false) ||
                    (is_array($rule) && array_search('required', $rule) !== false && array_search('required_', $rule) === false)
                ) {
                    $requiredFields[] = $key;
                }
            }
        }

        $this->set($operation.'.requiredFields', $requiredFields);
    }

    /**
     * Check the current object to see if an input is required
     * for the given operation.
     *
     * @param string $inputKey Field or input name.
     * @param string $operation create / update
     *
     * @return bool
     */
    public function isRequired($inputKey, $operation = null)
    {
        $operation = $operation ?? $this->getCurrentOperation();

        if (! $this->has($operation.'.requiredFields')) {
            return false;
        }

        return in_array($inputKey, $this->get($operation.'.requiredFields'));
    }
}
