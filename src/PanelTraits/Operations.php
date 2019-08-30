<?php

namespace Backpack\CRUD\PanelTraits;

trait Operations
{
    /*
    |--------------------------------------------------------------------------
    |                               OPERATIONS
    |--------------------------------------------------------------------------
    | Helps developers set and get the current CRUD operation, as defined by
    | the contoller method being run.
    */
    protected $currentOperation;
    protected $configuredOperations = [];

    /**
     * Get the current CRUD operation being performed.
     *
     * @return string Operation being performed in string form.
     */
    public function getOperation()
    {
        return $this->getCurrentOperation();
    }

    /**
     * Set the CRUD operation being performed in string form.
     *
     * @param string $operation_name Ex: create / update / revision / delete
     */
    public function setOperation($operation_name)
    {
        return $this->setCurrentOperation($operation_name);
    }

    /**
     * Get the current CRUD operation being performed.
     *
     * @return string Operation being performed in string form.
     */
    public function getCurrentOperation()
    {
        return $this->currentOperation;
    }

    /**
     * Set the CRUD operation being performed in string form.
     *
     * @param string $operation_name Ex: create / update / revision / delete
     */
    public function setCurrentOperation($operation_name)
    {
        $this->currentOperation = $operation_name;
    }

    /**
     * Get the name of the CRUD operation currently being configured (aka set up).
     *
     * @return string Operation being performed in string form.
     */
    public function getConfiguredOperations()
    {
        return $this->configuredOperations;
    }

    /**
     * Set the CRUD operation currently being configured (aka set up).
     *
     * @param string $operation_name Ex: create / update / revision / delete
     */
    public function setConfiguredOperations($operation_name)
    {
        $this->configuredOperations = $operation_name;
    }

    /**
     * Convenience method to make sure all calls are made to a particular operation.
     * And all settings are put inside that operation's namespace.
     *
     * @param  string           $operation      Operation name in string form
     * @param  bool|\Closure    $closure        Code that calls CrudPanel methods.
     * @return void
     */
    public function operation($operations, $closure = false)
    {
        $this->setConfiguredOperations((array) $operations);

        if (is_callable($closure)) {
            // apply the closure
            ($closure)();
        }

        return $this;
    }
}
