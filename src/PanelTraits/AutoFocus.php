<?php

namespace Backpack\CRUD\PanelTraits;

trait AutoFocus
{
    /**
     * @return bool
     */
    public function getAutoFocusOnFirstField()
    {
        return $this->get($this->getCurrentOperation().'.autoFocusOnFirstField');
    }

    public function setAutoFocusOnFirstField($value)
    {
        return $this->set($this->getCurrentOperation().'autoFocusOnFirstField', (bool) $value);
    }

    public function enableAutoFocus()
    {
        return $this->setAutoFocusOnFirstField(true);
    }

    public function disableAutoFocus()
    {
        return $this->setAutoFocusOnFirstField(false);
    }
}
