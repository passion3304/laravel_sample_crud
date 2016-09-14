<?php

namespace Backpack\CRUD;

use DB;
use Illuminate\Database\Eloquent\Model;

trait CrudTrait
{
    /*
    |--------------------------------------------------------------------------
    | Methods for ENUM and SELECT crud fields.
    |--------------------------------------------------------------------------
    */

    public static function getPossibleEnumValues($field_name)
    {
        $instance = new static(); // create an instance of the model to be able to get the table name
        $type = DB::select(DB::raw('SHOW COLUMNS FROM '.$instance->getTable().' WHERE Field = "'.$field_name.'"'))[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = [];
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }

        return $enum;
    }

    public static function isColumnNullable($column_name)
    {
        $instance = new static(); // create an instance of the model to be able to get the table name
        $answer = DB::select(DB::raw("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$instance->getTable()."' AND COLUMN_NAME='".$column_name."' AND table_schema='".env('DB_DATABASE')."'"))[0];

        return $answer->IS_NULLABLE === 'YES';
    }

    /*
    |--------------------------------------------------------------------------
    | Methods for Fake Fields functionality (used in PageManager).
    |--------------------------------------------------------------------------
    */

    /**
     * Add fake fields as regular attributes, even though they are stored as JSON.
     *
     * @param array $columns - the database columns that contain the JSONs
     */
    public function addFakes($columns = ['extras'])
    {
        foreach ($columns as $key => $column) {
            $column_contents = $this->{$column};

            if (! is_object($this->{$column})) {
                $column_contents = json_decode($this->{$column});
            }

            if (count($column_contents)) {
                foreach ($column_contents as $fake_field_name => $fake_field_value) {
                    $this->setAttribute($fake_field_name, $fake_field_value);
                }
            }
        }
    }

    /**
     * Return the entity with fake fields as attributes.
     *
     * @param array $columns - the database columns that contain the JSONs
     *
     * @return Model
     */
    public function withFakes($columns = [])
    {
        $model = '\\'.get_class($this);

        if (! count($columns)) {
            $columns = (property_exists($model, 'fakeColumns')) ? $this->fakeColumns : ['extras'];
        }

        $this->addFakes($columns);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Methods for storing uploaded files (used in CRUD).
    |--------------------------------------------------------------------------
    */

    /**
     * Handle file upload and DB storage for a file:
     * - on CREATE
     *     - stores the file at the destination path
     *     - generates a name
     *     - stores the full path in the DB;
     * - on UPDATE
     *     - if the value is null, deletes the file and sets null in the DB
     *     - if the value is different, stores the different file and updates DB value.
     *
     * Note: Since the generated name is the same if the file is uploaded twice,
     * the file is only deleted from disk if there is no other row using that file.
     * @param  [type] $value            Value for that column sent from the input.
     * @param  [type] $attribute_name   Model attribute name (and column in the db).
     * @param  [type] $disk             Filesystem disk used to store files.
     * @param  [type] $destination_path Path in disk where to store the files.
     */
    public function uploadFileToDisk($value, $attribute_name, $disk, $destination_path)
    {
        $request = \Request::instance();

        // if a new file is uploaded OR the file input is empty
        if (($request->hasFile($attribute_name) && $this->{$attribute_name} && $this->{$attribute_name} != null) || (is_null($value) && $this->{$attribute_name} != null)
            ) {
            // delete the file from the disk, if this entry is the only one using it
            if ($this->where($attribute_name, $this->{$attribute_name})->count() <= 1) {
                \Storage::disk($disk)->delete($this->{$attribute_name});
            }
            $this->attributes[$attribute_name] = null;
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name) && $request->file($attribute_name)->isValid()) {
            // 1. Move the new file to the correct path
            $file_path = $request->file($attribute_name)->store($destination_path, $disk);
            // 2. Save the complete path to the database
            $this->attributes[$attribute_name] = $file_path;
        }
    }
}
