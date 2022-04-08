<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if (! backpack_pro()) {
    trait ProFetchOperation
    {
        public function setupFetchOperationDefaults()
        {
            throw new BackpackProRequiredException('FetchOperation');
        }
    }
}

if (! backpack_pro()) {
    trait FetchOperation
    {
        use ProFetchOperation;
    }
} else {
    trait FetchOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\FetchOperation;
    }
}
