<?php

namespace Backpack\CRUD;

use Illuminate\Support\Facades\Facade;

class DatabaseSchemaFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'DatabaseSchema';
    }
}
