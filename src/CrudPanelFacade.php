<?php

namespace Backpack\CRUD;

use Illuminate\Support\Facades\Facade;

class CrudPanelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crud';
    }
}
