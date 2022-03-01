<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Str;

trait Validation
{
    /**
     * Adds the required rules from an array and allows validation of that array.
     *
     * @param  array  $requiredFields
     */
    public function setValidationFromArray(array $rules, array $messages = [])
    {
        $this->setRequiredFields($rules);
        $this->setOperationSetting('validationRules', $rules);
        $this->setOperationSetting('validationMessages', $messages);
    }

    /**
     * Take the rules defined on fields and create a validation
     * array from them.
     */
    public function setValidationFromFields()
    {
        $fields = $this->getOperationSetting('fields');

        // construct the validation rules array
        // (eg. ['name' => 'required|min:2'])
        $rules = collect($fields)
                    ->filter(function ($value, $key) {
                        // only keep fields where 'validationRules' attribute is defined
                        return array_key_exists('validationRules', $value);
                    })->map(function ($item, $key) {
                        // only keep the rules, not the entire field definition
                        return $item['validationRules'];
                    })->toArray();

        // construct the validation messages array
        // (eg. ['title.required' => 'You gotta write smth man.'])
        $messages = [];
        collect($fields)
                    ->filter(function ($value, $key) {
                        // only keep fields where 'validationMessages' attribute is defined
                        return array_key_exists('validationMessages', $value);
                    })->each(function ($item, $key) use (&$messages) {
                        foreach ($item['validationMessages'] as $rule => $message) {
                            $messages[$key.'.'.$rule] = $message;
                        }
                    });

        $this->setValidationFromArray($rules, $messages);
    }

    /**
     * Mark a FormRequest file as required for the current operation, in Settings.
     * Adds the required rules to an array for easy access.
     *
     * @param  string  $class  Class that extends FormRequest
     */
    public function setValidationFromRequest($class)
    {
        $this->setFormRequest($class);
        $this->setRequiredFields($class);
    }

    /**
     * Mark a FormRequest file as required for the current operation, in Settings.
     * Adds the required rules to an array for easy access.
     *
     * @param  string|array  $classOrRulesArray  Class that extends FormRequest or array of validation rules
     * @param  array  $messages  Array of validation messages.
     */
    public function setValidation($classOrRulesArray = false, $messages = [])
    {
        if (! $classOrRulesArray) {
            $this->setValidationFromFields();
        } elseif (is_array($classOrRulesArray)) {
            $this->setValidationFromArray($classOrRulesArray, $messages);
        } elseif (is_string($classOrRulesArray) || is_class($classOrRulesArray)) {
            $this->setValidationFromRequest($classOrRulesArray);
        } else {
            abort(500, 'Please pass setValidation() nothing, a rules array or a FormRequest class.');
        }
    }

    /**
     * Remove the current FormRequest from configuration, so it will no longer be validated.
     */
    public function unsetValidation()
    {
        $this->setOperationSetting('formRequest', false);
    }

    /**
     * Remove the current FormRequest from configuration, so it will no longer be validated.
     */
    public function disableValidation()
    {
        $this->unsetValidation();
    }

    /**
     * Mark a FormRequest file as required for the current operation, in Settings.
     *
     * @param  string  $class  Class that extends FormRequest
     */
    public function setFormRequest($class)
    {
        $this->setOperationSetting('formRequest', $class);
    }

    /**
     * Get the current form request file, in any.
     * Returns null if no FormRequest is required for the current operation.
     *
     * @return string Class that extends FormRequest
     */
    public function getFormRequest()
    {
        return $this->getOperationSetting('formRequest');
    }

    /**
     * Run the authorization and validation the currently set FormRequest.
     *
     * @return \Illuminate\Http\Request
     */
    public function validateRequest()
    {
        $formRequest = $this->getFormRequest();

        if ($formRequest) {
            // authorize and validate the formRequest
            // this is done automatically by Laravel's FormRequestServiceProvider
            // because form requests implement ValidatesWhenResolved
            $request = app($formRequest);
        } else {
            $request = $this->getRequest();

            if ($this->hasOperationSetting('validationRules')) {
                $rules = $this->getOperationSetting('validationRules');
                $messages = $this->getOperationSetting('validationMessages') ?? [];
                $request->validate($rules, $messages);
            }
        }

        return $request;
    }

    /**
     * Parse a FormRequest class, figure out what inputs are required
     * and store this knowledge in the current object.
     *
     * @param  string|array  $classOrRulesArray  Class that extends FormRequest or rules array
     */
    public function setRequiredFields($classOrRulesArray)
    {
        $requiredFields = [];

        if (is_array($classOrRulesArray)) {
            $rules = $classOrRulesArray;
        } else {
            $formRequest = new $classOrRulesArray();
            $rules = $formRequest->rules();
        }

        if (count($rules)) {
            foreach ($rules as $key => $rule) {
                if (
                    (is_string($rule) && strpos($rule, 'required') !== false && strpos($rule, 'required_') === false) ||
                    (is_array($rule) && array_search('required', $rule) !== false && array_search('required_', $rule) === false)
                ) {
                    if (strpos($key, '.') !== false) {
                        // Convert dot to array notation
                        $entity_array = explode('.', $key);
                        $name_string = '';

                        foreach ($entity_array as $arr_key => $array_entity) {
                            if ($array_entity === '*') {
                                continue;
                            }
                            $name_string .= ($arr_key === 0) ? $array_entity : '['.$array_entity.']';
                        }

                        $key = $name_string;
                    }

                    $requiredFields[] = $key;
                }
            }
        }

        // merge any previous required fields with current ones
        $requiredFields = array_merge($this->getOperationSetting('requiredFields') ?? [], $requiredFields);

        // since this COULD BE called twice (to support the previous syntax where developers needed to call `setValidation` after the field definition)
        // and to make this change non-breaking, we are going to return an unique array. There is NO WARM returning repeated names, but there is also
        // no sense in doing it, so array_unique() it is.
        $requiredFields = array_unique($requiredFields);

        $this->setOperationSetting('requiredFields', $requiredFields);
    }

    /**
     * Check the current object to see if an input is required
     * for the given operation.
     *
     * @param  string  $inputKey  Field or input name.
     * @param  string  $operation  create / update
     * @return bool
     */
    public function isRequired($inputKey)
    {
        if (! $this->hasOperationSetting('requiredFields')) {
            return false;
        }

        if (Str::contains($inputKey, '.')) {
            $entity_array = explode('.', $inputKey);
            $name_string = '';
            foreach ($entity_array as $arr_key => $array_entity) {
                $name_string .= ($arr_key === 0) ? $array_entity : '['.$array_entity.']';
            }
            $inputKey = $name_string;
        }

        return in_array($inputKey, $this->getOperationSetting('requiredFields'));
    }
}
