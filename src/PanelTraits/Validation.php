<?php

namespace Backpack\CRUD\PanelTraits;

use Illuminate\Routing\Redirector;
use Illuminate\Foundation\Http\FormRequest;

trait Validation
{  
    /**
     * Mark a FormRequest file as required for the current operation, in Settings.
     * Adds the required rules to an array for easy access.
     * 
     * @param FormRequest $formRequest
     */
    public function setValidation($formRequest)
    {
        $this->setFormRequest($formRequest);
        $this->setRequiredFields($formRequest);
    }

    /**
     * Mark a FormRequest file as required for the current operation, in Settings.
     * 
     * @param FormRequest $formRequest
     */
    public function setFormRequest($formRequest)
    {
        $this->setOperationSetting('formRequest', $formRequest);
    }

    /**
     * Get the current form request file, in any.
     * Returns null if no FormRequest is required for the current operation. 
     * 
     * @return FormRequest
     */
    public function getFormRequest()
    {
        return $this->getOperationSetting('formRequest');
    }

    public function validateRequest()
    {
        $formRequest = $this->getFormRequest();

        if ($formRequest) {
            // authorize and validate the formRequest
            $request = FormRequest::createFrom($this->request, new $formRequest)
                        ->setContainer(app())
                        ->setRedirector(app()->make(Redirector::class));
            $request->validateResolved();
        } else {
            $request = $this->request;
        }

        return $request;
    }

    /**
     * Parse a FormRequest class, figure out what inputs are required
     * and store this knowledge in the current object.
     *
     * @param string $class     Class that extends FormRequest
     */
    public function setRequiredFields($class)
    {
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

        $this->setOperationSetting('requiredFields', $requiredFields);
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
    public function isRequired($inputKey)
    {
        if (! $this->hasOperationSetting('requiredFields')) {
            return false;
        }

        return in_array($inputKey, $this->getOperationSetting('requiredFields'));
    }
}
