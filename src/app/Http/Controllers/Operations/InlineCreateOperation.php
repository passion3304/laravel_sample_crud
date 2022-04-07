<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

$loadFakeTrait = false;
if(!trait_exists(\Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation::class)) {
    $loadFakeTrait = true;
    trait ProInlineCreateOperation {
        public function initializeProInlineCreateOperation() {
            throw new BackpackProRequiredException('InlineCreateOperation');
        }
    }
}

if($loadFakeTrait) {
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
