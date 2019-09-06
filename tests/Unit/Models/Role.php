<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Role extends Model
{
    use CrudTrait;

    protected $table = 'roles';
    protected $fillable = ['name'];

    /**
     * Get the user for the account details.
     */
    public function user()
    {
        return $this->belongsToMany('Backpack\CRUD\Tests\Unit\Models\User', 'user_role');
    }
}
