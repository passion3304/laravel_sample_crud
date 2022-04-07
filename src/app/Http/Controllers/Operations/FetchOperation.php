<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if(!trait_exists(\Backpack\Pro\Http\Controllers\Operations\FetchOperation::class)) {
    trait ProFetchOperation {
        public function initializeProFetchOperation() {
            throw new BackpackProRequiredException('FetchOperation');
        }
    }
}

if(trait_exists(ProFetchOperation::class)) {
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