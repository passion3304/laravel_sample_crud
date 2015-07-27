<?php namespace Dick\CRUD;

use Illuminate\Database\Eloquent\Model;
use DB;

trait CrudTrait {

	public static function getPossibleEnumValues($field_name){
        $instance = new static; // create an instance of the model to be able to get the table name
        $type = DB::select( DB::raw('SHOW COLUMNS FROM '.$instance->getTable().' WHERE Field = "'.$field_name.'"') )[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = array();
        $exploded = explode(',', $matches[1]);
        foreach($exploded as $value){
            $v = trim( $value, "'" );
            $enum[] = $v;
        }
        return $enum;
    }

    public static function isColumnNullable($column_name) {
        $instance = new static; // create an instance of the model to be able to get the table name
        $answer = DB::select( DB::raw("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$instance->getTable()."' AND COLUMN_NAME='".$column_name."' AND table_schema='".env('DB_DATABASE')."'") )[0];

        return ($answer->IS_NULLABLE == 'YES'?true:false);
    }

}
