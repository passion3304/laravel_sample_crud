<?php

namespace Backpack\CRUD\Tests81\Unit\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class UserWithReturnTypes extends \Backpack\CRUD\Tests\Unit\Models\User
{
    public function isAnAttribute(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return false;
    }

    public function isARelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->bang();
    }
}