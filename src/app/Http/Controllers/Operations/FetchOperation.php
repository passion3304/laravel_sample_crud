<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

$loadFakeTrait = false;
if(!trait_exists(\Backpack\Pro\Http\Controllers\Operations\FetchOperation::class)) {
    $loadFakeTrait = true;
    trait ProFetchOperation {
        public function initializeProFetchOperation() {
            throw new BackpackProRequiredException('FetchOperation');
        }
    }
}

if($loadFakeTrait) {
    trait FetchOperation
    {
        use ProFetchOperation;
    }
}else{
    trait FetchOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\FetchOperation;
    }   
}