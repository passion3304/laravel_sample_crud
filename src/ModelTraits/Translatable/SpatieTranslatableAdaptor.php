<?php

namespace Backpack\CRUD\ModelTraits\Translatable;

trait SpatieTranslatableAdaptor
{
    /*
    |--------------------------------------------------------------------------
    |                            ELOQUENT OVERWRITES
    |--------------------------------------------------------------------------
    */

	/**
	 * Create translated items as json.
	 *
	 * @param  array  $attributes [description]
	 * @return [type]             [description]
	 */
    public static function create(array $attributes = [])
    {
        $locale = $attributes['locale']?$attributes['locale']:App::getLocale();
        $attributes = array_except($attributes, ['locale']);

    	$model = new static();
    	$translatable_attributes = $model->translatable;

    	// do the actual saving
    	foreach ($attributes as $attribute => $value) {
    		if (in_array($attribute, $translatable_attributes))
    		{ // the attribute is translatable
    			$model->setTranslation($attribute, $locale, $value);
    		}
    		else
    		{ // the attribute is NOT translatable
    			$model->{$attribute} = $value;
    		}
    	}
        $model->save();

        return $model;
    }

    /**
     * Update translated items as json.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        $locale = $attributes['locale']?$attributes['locale']:App::getLocale();
        $attributes = array_except($attributes, ['locale']);

    	$translatable_attributes = $this->translatable;

        // do the actual saving
    	foreach ($attributes as $attribute => $value) {
    		if (in_array($attribute, $translatable_attributes))
    		{ // the attribute is translatable
    			$this->setTranslation($attribute, $locale, $value);
    		}
    		else
    		{ // the attribute is NOT translatable
    			$this->{$attribute} = $value;
    		}
    	}
        $this->save($options);

        return $this;
    }

    /**
     * Get the database entry in the wanted locale.
     *
     * @param  [int] The id of the row in the db to fetch.
     *
     * @return [Eloquent Collection] The row in the db.
     */
    public function findOrFail($id)
    {
    	$locale = \Request::input('locale');

    	if ($locale) {
    		app()->setLocale($locale);
    		$item = parent::findOrFail($id);
    		app()->setLocale(config('app.locale'));
    		return $item;
    	}

    	return parent::findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    |                            CUSTOM METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if a model is translatable, by the adapter's standards.
     *
     * @return bool
     */
    public function translationEnabledForModel()
    {
    	return property_exists($this, 'translatable');
    }

    public function getAvailableLocales()
    {
    	return config('backpack.crud.locales');
    }
}