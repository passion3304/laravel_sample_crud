<?php

namespace Backpack\CRUD\PanelTraits;

/**
 * Key-value store for operations.
 */
trait Settings
{
    private $settings = [];

    /**
     * Getter and setter for the settings key-value store.
     * @param  string   $key   Usually operation.name (ex: list.exportButtons)
     * @param  mixed    $value The value you want to store.
     * @return mixed           Setting value for setter. True/false for getter.
     */
    public function setting(String $key, $value = null)
    {
        if ($value === null) {
            return $this->get($key);
        }

        return ($this->set($key));
    }

    /**
     * Getter for the settings key-value store.
     * @param  string   $key   Usually operation.name (ex: list.exportButtons)
     * @return mixed      [description]
     */
    public function get(String $key) 
    {
        return $this->settings[$key] ?? null;
    }

    /**
     * Setter for the settings key-value store.
     * @param string $key    Usually operation.name (ex: reorder.max_level)
     * @param boolean $value True/false depending on success.
     */
    public function set(String $key, $value) 
    {
        return ($this->settings[$key] = $value);
    }

    /**
     * Check if the settings key is used (has a value).
     * @param  String  $key Usually operation.name (ex: reorder.max_level)
     * @return boolean
     */
    public function has(String $key)
    {
        if (isset($this->settings[$key])) {
            return true;
        }

        return false;
    }
}
