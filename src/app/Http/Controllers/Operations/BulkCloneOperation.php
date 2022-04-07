<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if(!trait_exists(\Backpack\Pro\Http\Controllers\Operations\BulkCloneOperation::class)) {
    trait ProBulkCloneOperation {
        public function initializeProBulkCloneOperation() {
            throw new BackpackProRequiredException('BulkCloneOperation');
        }
    }
}

if(trait_exists(ProBulkCloneOperation::class)) {
    trait BulkCloneOperation
    {
        use ProBulkCloneOperation;
    }
}else{
    trait BulkCloneOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\BulkCloneOperation;
    }   
}
