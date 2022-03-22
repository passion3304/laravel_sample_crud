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
        $this->setOperationSetting('validationRules', array_merge($this->getOperationSetting('validationRules') ?? [], $rules));
        $this->setOperationSetting('validationMessages', array_merge($this->getOperationSetting('validationMessages') ?? [], $messages));
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
        $rules = $this->getRulesFromFieldsAndSubfields($fields);

        // construct the validation messages array
        // (eg. ['title.required' => 'You gotta write smth man.'])
        $messages = $this->getMessagesFromFieldsAndSubfields($fields);

        $this->setValidationFromArray($rules, $messages);
    }

    /**
     * Return the rules for the fields and subfields in the current crud panel.
     *
     * @param  array  $fields
     * @return array
     */
    private function getRulesFromFieldsAndSubfields($fields)
    {
        $rules = collect($fields)
            ->filter(function ($value, $key) {
                // only keep fields where 'validationRules' OR there are subfields
                return array_key_exists('validationRules', $value) || array_key_exists('subfields', $value);
            })->map(function ($item, $key) {
                $validationRules = [];
                // only keep the rules, not the entire field definition
                if (isset($item['validationRules'])) {
                    $validationRules[$key] = $item['validationRules'];
                }
                // add validation rules for subfields
                if (array_key_exists('subfields', $item)) {
                    $subfieldsWithValidation = array_filter($item['subfields'], function ($subfield) {
                        return array_key_exists('validationRules', $subfield);
                    });

                    foreach ($subfieldsWithValidation as $subfield) {
                        $validationRules[$item['name'].'.*.'.$subfield['name']] = $subfield['validationRules'];
                    }
                }

                return $validationRules;
            })->toArray();

        return array_merge(...array_values($rules));
    }

    /**
     * Return the messages for the fields and subfields in the current crud panel.
     *
     * @param  array  $fields
     * @return array
     */
    private function getMessagesFromFieldsAndSubfields($fields)
    {
        $messages = [];
        collect($fields)
            ->filter(function ($value, $key) {
                // only keep fields where 'validationMessages' OR there are subfields
                return array_key_exists('validationMessages', $value) || array_key_exists('subfields', $value);
            })->each(function ($item, $key) use (&$messages) {
                if (isset($item['validationMessages'])) {
                    foreach ($item['validationMessages'] as $rule => $message) {
                        $messages[$key.'.'.$rule] = $message;
                    }
                }
                // add messages from subfields
                if (array_key_exists('subfields', $item)) {
                    $subfieldsWithValidationMessages = array_filter($item['subfields'], function ($subfield) {
                        return array_key_exists('validationRules', $subfield);
                    });

                    foreach ($subfieldsWithValidationMessages as $subfield) {
                        foreach ($subfield['validationMessages'] as $rule => $message) {
                            $messages[$item['name'].'.*.'.$subfield['name'].'.'.$rule] = $message;
                        }
                    }
                }
            })->toArray();

        return $messages;
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
     * Run the authorization and validation for the current crud panel.
     * That authorization is gathered from 3 places:
     * - the FormRequest when provided.
     * - the rules added in the controller.
     * - the rules defined in the fields itself.
     *
     * @return \Illuminate\Http\Request
     */
    public function validateRequest()
    {
        $formRequest = $this->getFormRequest();

        $_rules = $this->getOperationSetting('validationRules') ?? [];
        $_messages = $this->getOperationSetting('validationMessages') ?? [];

        if ($formRequest) {
            // when there is no validation in the fields, just validate the form request.
            if (empty($_rules)) {
                return app($formRequest);
            }

            // create an alias of the provided FormRequest so we can create a new class that extends it.
            // we can't use $variables to extend classes.
            class_alias(get_class(new $formRequest), 'DeveloperProvidedRequest');

            // create a new anonymous class that will extend the provided developer FormRequest
            // in this class we will merge the FormRequest rules() and messages() with the ones provided by developer in fields.
            $extendedRequest = new class($_rules, $_messages) extends \DeveloperProvidedRequest
            {
                private $_rules;
                private $_messages;

                public function __construct($_rules, $_messages)
                {
                    parent::__construct();
                    $this->_rules = $_rules;
                    $this->_messages = $_messages;
                }

                public function rules()
                {
                    return array_merge(parent::rules(), $this->_rules);
                }

                public function messages()
                {
                    return array_merge(parent::messages(), $this->_messages);
                }
            };

            // validate the complete request with FormRequest + controller validation + field validation (our anonymous class)
            return app(get_class($extendedRequest), ['_rules' => $_rules, '_messages' => $_messages]);
        }

        return $this->getRequest()->validate($_rules, $_messages);
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

    /**
     * Add the validation setup by developer in field `validationRules` to the crud validation.
     *
     * @param  array  $field  - the field we want to get the validation from.
     * @param  bool|string  $parent  - the parent name when setting up validation for subfields.
     */
    private function setupFieldValidation($field, $parent = false)
    {
        [$rules, $messages] = $this->getValidationRulesAndMessagesFromField($field, $parent);

        if (! empty($rules)) {
            $this->setValidation($rules, $messages);
        }
    }

    /**
     * Return the array of rules and messages with the validation key accordingly set
     * to match the field or the subfield accordingly.
     *
     * @param  array  $field  - the field we want to get the rules and messages from.
     * @param  bool|string  $parent  - the parent name when setting up validation for subfields.
     */
    private function getValidationRulesAndMessagesFromField($field, $parent = false)
    {
        $fieldValidationName = $field['name'];
        $rules = [];
        $messages = [];

        if ($parent) {
            $fieldValidationName = $parent.'.*.'.$field['name'];
        }

        if (isset($field['validationRules'])) {
            $rules[$fieldValidationName] = $field['validationRules'];
        }
        if (isset($field['validationMessages'])) {
            foreach ($field['validationMessages'] as $validator => $message) {
                $fieldValidationName = $fieldValidationName.'.'.$validator;
                $messages[$fieldValidationName] = $message;
            }
        }

        return [$rules, $messages];
    }
}
