<?php

namespace Backpack\CRUD\Tests81\Unit\Models;

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
