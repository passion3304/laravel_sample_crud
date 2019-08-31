<?php

namespace Backpack\CRUD\PanelTraits;

trait Errors
{
    // -------
    // Getters
    // -------

    /**
     * @return bool
     */
    public function groupedErrorsEnabled()
    {
        return $this->get($this->getCurrentOperation().'.groupedErrors');
    }

    /**
     * @return bool
     */
    public function inlineErrorsEnabled()
    {
        return $this->get($this->getCurrentOperation().'.inlineErrors');
    }

    // -------
    // Setters
    // -------

    public function enableGroupedErrors()
    {
        return $this->set($this->getCurrentOperation().'.groupedErrors', true);
    }

    public function disableGroupedErrors()
    {
        return $this->set($this->getCurrentOperation().'.groupedErrors', false);
    }

    public function enableInlineErrors()
    {
        return $this->set($this->getCurrentOperation().'.inlineErrors', true);
    }

    public function disableInlineErrors()
    {
        return $this->set($this->getCurrentOperation().'.inlineErrors', false);
    }
}
