<?php

namespace Backpack\CRUD\ModelTraits\Translatable;

use Spatie\Translatable\HasTranslations;

trait SpatieTranslatableAdaptor
{
    use HasTranslations;

    public $locale = false;

    /*
    |--------------------------------------------------------------------------
    |                 SPATIE/LARAVEL-TRANSLATABLE OVERWRITES
    |--------------------------------------------------------------------------
    */

    /**
     * Use the forced locale if present.
     *
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function getAttributeValue($key)
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::getAttributeValue($key);
        }

        $translation = $this->getTranslation($key, $this->locale ?: config('app.locale'));

        return is_array($translation) ? array_first($translation) : $translation;
    }

    /*
    |--------------------------------------------------------------------------
    |                            ELOQUENT OVERWRITES
    |--------------------------------------------------------------------------
    */

    /**
     * Create translated items as json.
     *
     * @param  array  $attributes [description]
     */
    public static function create(array $attributes = [])
    {
        $locale = $attributes['locale'] ?? \App::getLocale();
        $attributes = array_except($attributes, ['locale']);

        $model = new static();

        // do the actual saving
        foreach ($attributes as $attribute => $value) {
            if ($model->isTranslatableAttribute($attribute)) { // the attribute is translatable
                $model->setTranslation($attribute, $locale, $value);
            } else { // the attribute is NOT translatable
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

        $locale = $attributes['locale'] ? $attributes['locale'] : App::getLocale();
        $attributes = array_except($attributes, ['locale']);

        // do the actual saving
        foreach ($attributes as $attribute => $value) {
            if ($this->isTranslatableAttribute($attribute)) { // the attribute is translatable
                $this->setTranslation($attribute, $locale, $value);
            } else { // the attribute is NOT translatable
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
        $translation_locale = \Request::input('locale');
        $default_locale = \App::getLocale();

        if ($translation_locale) {
            $item = parent::findOrFail($id);
            $item->setLocale($translation_locale);

            return $item;
        }

        return parent::findOrFail($id);
    }

    /**
     * Get the database entry in the wanted locale.
     *
     * @param  [int] The id of the row in the db to fetch.
     *
     * @return [Eloquent Collection] The row in the db.
     */
    public function find($id)
    {
        $translation_locale = \Request::input('locale');
        $default_locale = \App::getLocale();

        if ($translation_locale) {
            $item = parent::find($id);
            $item->setLocale($translation_locale);

            return $item;
        }

        return parent::find($id);
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

    /**
     * Get all locales the admin is allowed to use.
     *
     * @return array
     */
    public function getAvailableLocales()
    {
        return config('backpack.crud.locales');
    }

    /**
     * Set the locale property. Used in normalizeLocale() to force the translation
     * to a different language that the one set in app()->getLocale();.
     *
     * @param string
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
}
