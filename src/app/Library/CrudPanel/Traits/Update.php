<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Update
{
    /*
    |--------------------------------------------------------------------------
    |                                   UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Update a row in the database.
     *
     * @param  int  $id  The entity's id
     * @param  array  $input  All inputs to be updated.
     * @return object
     */
    public function update($id, $input)
    {
        $item = $this->model->findOrFail($id);

        [$directInputs, $relationInputs] = $this->getParsedInputs($input);

        $updated = $item->update($directInputs);

        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  int  $id  The id of the entry that is being edited.
     * @return array The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id = false)
    {
        $fields = $this->fields();
        $entry = ($id != false) ? $this->getEntry($id) : $this->getCurrentEntry();

        foreach ($fields as &$field) {
            $field['value'] = $field['value'] ?? $this->getModelAttributeValue($entry, $field);
        }

        // always have a hidden input for the entry id
        if (! array_key_exists('id', $fields)) {
            $fields['id'] = [
                'name'  => $entry->getKeyName(),
                'value' => $entry->getKey(),
                'type'  => 'hidden',
            ];
        }

        return $fields;
    }

    /**
     * Get the value of the 'name' attribute from the declared relation model in the given field.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  The current CRUD model.
     * @param  array  $field  The CRUD field array.
     * @return mixed The value of the 'name' attribute from the relation model.
     */
    private function getModelAttributeValue($model, $field)
    {
        if (isset($field['entity']) && $field['entity'] !== false) {
            return $this->getModelAttributeValueFromRelationship($model, $field);
        }

        if (is_string($field['name'])) {
            return $model->{$field['name']};
        }

        if (is_array($field['name'])) {
            $result = [];
            foreach ($field['name'] as $name) {
                $result[] = $model->{$name};
            }

            return $result;
        }
    }

    /**
     * Returns the value of the given attribute in the relationship.
     * It takes into account nested relationships.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $field
     * @return mixed
     */
    private function getModelAttributeValueFromRelationship($model, $field)
    {
        [$related_model, $relation_method] = $this->getModelAndMethodFromEntity($model, $field);

        if (! method_exists($related_model, $relation_method)) {
            return $related_model->{$relation_method};
        }

        $relation = $related_model->{$relation_method}();
        $relation_type = Str::afterLast(get_class($relation), '\\');

        switch ($relation_type) {
            case 'MorphMany':
            case 'HasMany':
            case 'BelongsToMany':
            case 'MorphToMany':
                // use subfields aka. pivotFields
                if (! isset($field['subfields'])) {
                    return $related_model->{$relation_method};
                }

                $related_models = $related_model->{$relation_method};
                $result = [];

                foreach ($related_models as $related_model) {
                    $item = [];
                    switch ($relation_type) {
                        case 'HasMany':
                        case 'MorphMany':
                            // we will get model direct attributes and merge with subfields values.
                            $directAttributes = $related_model->withFakes()->getAttributes();
                            $result[] = array_merge($directAttributes, $this->getSubfieldsValues($field['subfields'], $related_model));
                            break;

                        case 'BelongsToMany':
                        case 'MorphToMany':
                            // for any given model, we grab the attributes that belong to our pivot table.
                            $item = $related_model->pivot->getAttributes();
                            $item[$relation_method] = $related_model->getKey();
                            $result[] = $item;
                            break;
                    }
                }

                return $result;
                break;
            case 'HasOne':
            case 'MorphOne':
                if (! method_exists($related_model, $relation_method)) {
                    return;
                }

                $related_entry = $related_model->{$relation_method}->withFakes();

                if (! $related_entry) {
                    return;
                }

                // if `entity` contains a dot here it means developer added a main HasOne/MorphOne relation with dot notation
                if (Str::contains($field['entity'], '.')) {
                    return $related_entry->{Str::afterLast($field['entity'], '.')};
                }

                // when subfields exists developer used the repeatable interface to manage this relation
                if ($field['subfields']) {
                    return [$this->getSubfieldsValues($field['subfields'], $related_entry)];
                }

                return $related_entry->withFakes();

                break;
            default:
                return $related_model->{$relation_method};
        }
    }

    /**
     * Returns the model and the method from the relation string starting from the provided model.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  array  $field
     * @return array
     */
    private function getModelAndMethodFromEntity($model, $field)
    {
        // HasOne and MorphOne relations contains the field in the relation string. We want only the relation part.
        $relational_entity = $this->getOnlyRelationEntity($field);

        $relation_array = explode('.', $relational_entity);

        $related_model = array_reduce(array_splice($relation_array, 0, -1), function ($obj, $method) {
            $method = Str::endsWith($method, '_id') ? Str::replaceLast('_id', '', $method) : $method;

            return $obj->{$method} ? $obj->{$method} : $obj;
        }, $model);

        $relation_method = Str::afterLast($relational_entity, '.');

        return [$related_model, $relation_method];
    }

    /**
     * Return the subfields values from the related model.
     *
     * @param  array  $subfields
     * @param  \Illuminate\Database\Eloquent\Model  $relatedModel
     */
    private function getSubfieldsValues($subfields, $relatedModel)
    {
        $result = [];
        foreach ($subfields as $subfield) {
            $name = is_string($subfield) ? $subfield : $subfield['name'];
            // if the subfield name does not contain a dot we just need to check
            // if it has subfields and return the result accordingly.
            if (! Str::contains($name, '.')) {
                // when subfields are present, $relatedModel->{$name} returns a model instance
                // otherwise returns the model attribute.
                if ($relatedModel->{$name}) {
                    if (isset($subfield['subfields'])) {
                        $result[$name] = [$relatedModel->{$name}->only(array_column($subfield['subfields'], 'name'))];
                    } else {
                        $result[$name] = $relatedModel->{$name};
                    }
                }
            } else {
                // if the subfield name contains a dot, we are going to iterate through
                // those parts to get the last connected part and parse it for returning.
                // we get either a string (the attribute in model, eg: street) or a model instance (eg: AddressModel)
                $iterator = $relatedModel;
                foreach (explode('.', $name) as $part) {
                    $iterator = $iterator->$part;
                }

                Arr::set($result, $name, (! is_string($iterator) ? $iterator->withFakes()->getAttributes() : $iterator));
            }
        }

        return $result;
    }
}
