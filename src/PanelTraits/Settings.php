<?php

namespace Backpack\CRUD\PanelTraits;

/**
 * Key-value store for operations.
 */
trait Settings
{
    protected $settings = [];

    /**
     * Getter and setter for the settings key-value store.
     * @param  string   $key   Usually operation.name (ex: list.exportButtons)
     * @param  mixed    $value The value you want to store.
     * @return mixed           Setting value for setter. True/false for getter.
     */
    public function setting(string $key, $value = null)
    {
        if ($value === null) {
            return $this->get($key);
        }

        return $this->set($key);
    }

    /**
     * Getter for the settings key-value store.
     * @param  string   $key   Usually operation.name (ex: list.exportButtons)
     * @return mixed      [description]
     */
    public function get(string $key)
    {
        return $this->settings[$key] ?? null;
    }

    /**
     * Setter for the settings key-value store.
     * @param string $key    Usually operation.name (ex: reorder.max_level)
     * @param bool $value True/false depending on success.
     */
    public function set(string $key, $value)
    {
        return $this->settings[$key] = $value;
    }

    /**
     * Check if the settings key is used (has a value).
     * @param  string  $key Usually operation.name (ex: reorder.max_level)
     * @return bool
     */
    public function has(string $key)
    {
        if (isset($this->settings[$key])) {
            return true;
        }

        return false;
    }

    /**
     * Convenience method for getting or setting a key on the current operation.
     * 
     * @param  string   $key   Has no operation prepended. (ex: exportButtons)
     * @param  mixed    $value The value you want to store.
     * @return mixed           Setting value for setter. True/false for getter.
     */
    public function operationSetting(string $key, $value = null, $operation = null)
    {
        $operation = $operation ?? $this->getCurrentOperation();

        return $this->setting($operation.'.'.$key, $value);
    }

    /**
     * Getter for the settings key-value store on a certain operation.
     * Defaults to the current operation.
     * 
     * @param  string   $key   Has no operation prepended. (ex: exportButtons)
     * @return mixed      [description]
     */
    public function getOperationSetting(string $key, $operation = null)
    {
        $operation = $operation ?? $this->getCurrentOperation();

        return $this->get($operation.'.'.$key);
    }

    /**
     * Setter for the settings key-value store for a certain operation.
     * Defaults to the current operation.
     * 
     * @param string $key   Has no operation prepended. (ex: max_level)
     * @param bool $value True/false depending on success.
     */
    public function setOperationSetting(string $key, $value, $operation = null)
    {
        $operation = $operation ?? $this->getCurrentOperation();

        return $this->set($operation.'.'.$key, $value);
    }
}
