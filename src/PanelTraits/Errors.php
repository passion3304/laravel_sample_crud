<?php

namespace Backpack\CRUD\PanelTraits;

trait Errors
{
    protected $errorsGrouped = true;
    protected $errorsInline = false;

    public function setErrorDefaults()
    {
        $this->errorsGrouped = config('backpack.crud.errors_grouped', true);
        $this->errorsInline = config('backpack.crud.errors_inline', false);
    }

    public function enableGroupedErrors()
    {
        $this->errorsGrouped = true;

        return $this->errorsGrouped;
    }

    public function disableGroupedErrors()
    {
        $this->errorsGrouped = false;

        return $this->errorsGrouped;
    }

    public function isGroupedErrorsEnabled()
    {
        return $this->errorsGrouped;
    }

    public function enableInlineErrors()
    {
        $this->errorsInline = true;

        return $this->errorsInline;
    }

    public function disableInlineErrors()
    {
        $this->errorsInline = false;

        return $this->errorsInline;
    }

    public function isInlineErrorsEnabled()
    {
        return $this->errorsInline;
    }
}
