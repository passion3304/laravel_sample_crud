<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\ViewNamespaces;

trait HasViewNamespaces
{
    public function addViewNamespacesFor(string $domain, array $viewNamespaces)
    {
        ViewNamespaces::addFor($domain, $viewNamespaces);
    }

    public function addViewNamespaceFor(string $domain, string $viewNamespace)
    {
        ViewNamespaces::addFor($domain, $viewNamespace);
    }
}