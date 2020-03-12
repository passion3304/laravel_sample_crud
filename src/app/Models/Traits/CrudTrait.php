<?php

namespace Backpack\CRUD\app\Models\Traits;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Traversable;

trait CrudTrait
{
    use HasIdentifiableAttribute;
    use HasEnumFields;
    use HasRelationshipFields;
    use HasUploadFields;
    use HasFakeFields;
    use HasTranslatableFields;

    public static function hasCrudTrait()
    {
        return true;
    }
}
