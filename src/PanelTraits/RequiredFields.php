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

    public function isRequired($inputName, $operation)
    {
        if (! isset($this->requiredFields[$operation])) {
            return false;
        }

        return in_array($inputName, $this->requiredFields[$operation]);
    }
}
