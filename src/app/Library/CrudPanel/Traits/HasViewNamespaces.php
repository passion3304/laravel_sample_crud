<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\ViewNamespaces;

trait HasViewNamespaces
{
    /**
     * This file is only needed because we messed up version constrains from
     * 1.2 up to 1.2.6 of PRO version and any user that the license ended
     * in the middle of those versions was not able to update
     * Backpack/CRUD up from 5.3.6.
     *
     * This should be removed in the next major version.
     */
    public function addViewNamespacesFor(string $domain, array $viewNamespaces)
    {
        ViewNamespaces::addFor($domain, $viewNamespaces);
    }

    public function addViewNamespaceFor(string $domain, string $viewNamespace)
    {
        ViewNamespaces::addFor($domain, $viewNamespace);
    }
}
