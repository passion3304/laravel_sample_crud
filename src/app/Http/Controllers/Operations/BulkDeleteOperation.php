<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

$loadFakeTrait = false;
if (! trait_exists(\Backpack\Pro\Http\Controllers\Operations\BulkDeleteOperation::class)) {
    $loadFakeTrait = true;
    trait ProBulkDeleteOperation
    {
        public function initializeProBulkDeleteOperation()
        {
            throw new BackpackProRequiredException('BulkDeleteOperation');
        }
    }
}

if ($loadFakeTrait) {
    trait BulkDeleteOperation
    {
        use ProBulkDeleteOperation;
    }
} else {
    trait BulkDeleteOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\BulkDeleteOperation;
    }
}
