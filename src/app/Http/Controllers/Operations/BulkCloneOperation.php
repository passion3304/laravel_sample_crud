<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if (! backpack_pro()) {
    trait ProBulkCloneOperation
    {
        public function setupBulkCloneOperationDefaults()
        {
            throw new BackpackProRequiredException('BulkCloneOperation');
        }
    }
}

if (! backpack_pro()) {
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
