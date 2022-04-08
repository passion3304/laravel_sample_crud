<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if (! backpack_pro()) {
    trait ProCloneOperation
    {
        public function setupCloneOperationDefaults()
        {
            throw new BackpackProRequiredException('CloneOperation');
        }
    }
}

if (! backpack_pro()) {
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
