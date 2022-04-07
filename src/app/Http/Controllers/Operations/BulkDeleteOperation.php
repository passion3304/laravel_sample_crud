<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if(!trait_exists(\Backpack\Pro\Http\Controllers\Operations\BulkDeleteOperation::class)) {
    trait ProBulkDeleteOperation {
        public function initializeProBulkDeleteOperation() {
            throw new BackpackProRequiredException('BulkDeleteOperation');
        }
    }
}

if(trait_exists(ProBulkDeleteOperation::class)) {
    trait BulkDeleteOperation
    {
        use ProBulkDeleteOperation;
    }
}else{
    trait BulkDeleteOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\BulkDeleteOperation;
    }   
}
