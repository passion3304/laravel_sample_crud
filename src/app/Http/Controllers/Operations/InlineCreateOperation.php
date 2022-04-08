<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if (! backpack_pro()) {
    trait ProInlineCreateOperation
    {
        public function setupInlineCreateOperationDefaults()
        {
            throw new BackpackProRequiredException('InlineCreateOperation');
        }
    }
}

if (! backpack_pro()) {
    trait InlineCreateOperation
    {
        use ProInlineCreateOperation;
    }
} else {
    trait InlineCreateOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation;
    }
}
