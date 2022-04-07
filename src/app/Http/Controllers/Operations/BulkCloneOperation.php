<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

$loadFakeTrait = false;
if (! trait_exists(\Backpack\Pro\Http\Controllers\Operations\BulkCloneOperation::class)) {
    $loadFakeTrait = true;
    trait ProBulkCloneOperation
    {
        public function initializeProBulkCloneOperation()
        {
            throw new BackpackProRequiredException('BulkCloneOperation');
        }
    }
}

if ($loadFakeTrait) {
    trait BulkCloneOperation
    {
        use ProBulkCloneOperation;
    }
} else {
    trait BulkCloneOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\BulkCloneOperation;
    }
}
