<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class ColumnType extends Model
{
    use CrudTrait;

    protected $table = 'column_types';
}
