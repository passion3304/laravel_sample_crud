<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if(!trait_exists(\Backpack\Pro\Http\Controllers\Operations\CloneOperation::class)) {
    trait ProCloneOperation {
        public function initializeProCloneOperation() {
            throw new BackpackProRequiredException('CloneOperation');
        }
    }
}

if(trait_exists(ProCloneOperation::class)) {
    trait CloneOperation
    {
        use ProCloneOperation;
    }
}else{
    trait CloneOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\CloneOperation;
    }   
}