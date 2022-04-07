<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if(!trait_exists(\Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation::class)) {
    trait ProInlineCreateOperation {
        public function initializeProInlineCreateOperation() {
            throw new BackpackProRequiredException('InlineCreateOperation');
        }
    }
}

if(trait_exists(ProFetchOperation::class)) {
    trait InlineCreateOperation
    {
        use ProInlineCreateOperation;
    }
}else{
    trait InlineCreateOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation;
    }   
}
