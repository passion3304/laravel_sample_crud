<?php

namespace Backpack\CRUD\ModelTraits\Translatable;

trait SpatieTranslatableAdaptor
{
    /**
     * Overwrite Eloquent's create method.
     *
     * @param  array  $attributes [description]
     * @return [type]             [description]
     */
    public static function create(array $attributes = [])
    {
        $locale = $attributes['locale'];
        $attributes = array_except($attributes, ['locale']);

        $model = new static();
        $translatable_attributes = $model->translatable;

        // do the actual saving
        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, $translatable_attributes)) { // the attribute is translatable
                $model->setTranslation($attribute, $locale, $value);
            } else { // the attribute is NOT translatable
                $model->{$attribute} = $value;
            }
        }
        $model->save();

        return $model;
    }

    /**
     * Check if a model is translatable, by the adapter's standards.
     *
     * @return bool
     */
    public function translationEnabledForModel()
    {
        return property_exists($this, 'translatable');
    }
}
