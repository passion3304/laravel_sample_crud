<?php namespace Dick\CRUD;

use Illuminate\Database\Eloquent\Model;
use DB;
use Lang;

trait CrudTrait {

    /*
    |--------------------------------------------------------------------------
    | Methods for ENUM and SELECT crud fields.
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Methods for Fake Fields functionality (used in PageManager).
    |--------------------------------------------------------------------------
    */

    /**
     * Extras Accessor
     *
     * Instead of viewing it as a JSON array, always turn it to a PHP array.
     *
     * @param  string  $value
     * @return string
     */
    public function getExtrasAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Add fake fields as regular attributes, even though they are stored as JSON.
     *
     * @param  array  $columns - the database columns that contain the JSONs
     * @return -
     */
    public function addFakes($columns = ['extras']) {
        foreach ($columns as $key => $column) {
            if (count($this->{$column}))
            {
                foreach ($this->{$column} as $fake_field_name => $fake_field_value) {
                    $this->setAttribute($fake_field_name, $fake_field_value);
                }
            }
        }
    }

    public function withFakes($columns = [])
    {
        $model = '\\'.get_class($this);

        if (!count($columns)) {
            if (property_exists($model, 'fakeColumns')) {
                $columns = $this->fakeColumns;
            }
            else
            {
                $columns = ['extras'];
            }
        }

        $this->addFakes($columns);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Translation Methods
    |--------------------------------------------------------------------------
    */

    public function translations()
    {
        $model = '\\'.get_class($this);

        if (isset($this->translatable))
        {
            return $model::where('translation_of', $this->id)->get();
        }

        return collect();
    }

    public function translation($translation_lang = false)
    {
        if ($translation_lang==false) {
            $translation_lang = Lang::locale();
        }

        $model = '\\'.get_class($this);
        if (isset($this->translatable))
        {
            return $model::where('translation_of', $this->id)->where('translation_lang', $translation_lang)->first();
        }

        return false;
    }

    public function translationLanguages()
    {
        $model = '\\'.get_class($this);
        $translations = $this->translations();

        $translated_in = [];

        if ($translations->count())
        {
            foreach ($translations as $key => $translation) {
                $translated_in[] = $translation->language;
            }
        }

        return collect($translated_in);
    }

    public function language()
    {
        return $this->belongsTo('\Dick\TranslationManager\Models\Language', 'translation_lang', 'abbr');
    }

    /**
     * Overwriting the Eloquent save() method, to set a default translation language, if necessary.
     */
    public function save(array $options = [])
    {
        if (isset($this->translatable))
        {
            // set a default language (the one the user is currently using)
            if (!(isset($this->translation_lang)) || $this->translation_lang == '')
            {
                $this->translation_lang = \Lang::locale();
            }

            // TODO: if some untranslatable attributes are empty, but its parent's are filled, copy them
        }

        parent::save();
    }

}
