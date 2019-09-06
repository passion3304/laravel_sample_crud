<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class AccountDetails extends Model
{
    use CrudTrait;

    protected $table = 'account_details';
    protected $fillable = ['user_id', 'nickname', 'profile_picture'];

    /**
     * Get the user for the account details.
     */
    public function user()
    {
        return $this->belongsTo('Backpack\CRUD\Tests\Unit\Models\User');
    }

    public function addresses()
    {
        return $this->hasMany('Backpack\CRUD\Tests\Unit\Models\Address');
    }
}
