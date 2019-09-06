<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Address extends Model
{
    use CrudTrait;

    protected $table = 'addresses';
    protected $fillable = ['city', 'street', 'number'];

    /**
     * Get the author for the article.
     */
    public function accountDetails()
    {
        return $this->belongsTo('Backpack\CRUD\Tests\Unit\Models\AccountDetails');
    }
}
