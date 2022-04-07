<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

$loadFakeTrait = false;
if (! trait_exists(\Backpack\Pro\Http\Controllers\Operations\CloneOperation::class)) {
    $loadFakeTrait = true;
    trait ProCloneOperation
    {
        public function initializeProCloneOperation()
        {
            throw new BackpackProRequiredException('CloneOperation');
        }
    }
}

if ($loadFakeTrait) {
    trait CloneOperation
    {
        use ProCloneOperation;
    }
} else {
    trait CloneOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\CloneOperation;
    }
}
