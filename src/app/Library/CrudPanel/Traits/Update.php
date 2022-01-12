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
     * @param  array  $data  All inputs to be updated.
     * @return object
     */
    public function update($id, $data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);
        $item = $this->model->findOrFail($id);

        $data = $this->changeBelongsToNamesFromRelationshipToForeignKey($data);

        $this->createRelations($item, $data);

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = Arr::pluck($this->getRelationFieldsWithPivot(), 'name');

        $data = Arr::except($data, $nn_relationships);

        $updated = $item->update($data);

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
            // set the value
            if (! isset($field['value'])) {
                if (isset($field['subfields'])) {
                    $field['value'] = [];
                    foreach ($field['subfields'] as $subfield) {
                        $field['value'][] = $entry->{$subfield['name']};
                    }
                } else {
                    $field['value'] = $this->getModelAttributeValue($entry, $field);
                }
            }
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
            foreach ($field['name'] as $key => $value) {
                $result = $model->{$value};
            }

            return $result;
        }
    }

    private function getModelAttributeValueFromRelationship($model, $field)
    {
        [$related_model, $relation_method] = $this->getModelAndMethodFromEntity($model, $field);

        if (! method_exists($related_model, $relation_method)) {
            return $related_model->{$relation_method};
        }

        $relation_type = Str::afterLast(get_class($related_model->{$relation_method}()), '\\');

        switch ($relation_type) {
            case 'MorphMany':
            case 'HasMany':
            case 'BelongsToMany':
            case 'MorphToMany':
                if (! isset($field['pivotFields'])) {
                    return $related_model->{$relation_method};
                }
                // we want to exclude the "self pivot field" since we already have it.
                $pivot_fields = Arr::where($field['pivotFields'], function ($item) use ($field) {
                    return $field['name'] != $item['name'];
                });
                $related_models = $related_model->{$relation_method};
                $result = [];
                // for any given model, we grab the attributes that belong to our pivot table.
                foreach ($related_models as $related_model) {
                    $item = [];
                    switch ($relation_type) {

                        case 'HasMany':
                        case 'MorphMany':
                            // for any given related model, we get the value from pivot fields
                            foreach ($pivot_fields as $pivot_field) {
                                $item[$pivot_field['name']] = $related_model->{$pivot_field['name']};
                            }
                            $item[$related_model->getKeyName()] = $related_model->getKey();
                            $result[] = $item;
                            break;

                        case 'BelongsToMany':
                        case 'MorphToMany':
                            // for any given related model, we get the pivot fields.
                            foreach ($pivot_fields as $pivot_field) {
                                $item[$pivot_field['name']] = $related_model->pivot->{$pivot_field['name']};
                            }
                            $item[$field['name']] = $related_model->getKey();
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

                $related_entry = $related_model->{$relation_method};

                if (! $related_entry) {
                    return;
                }

                if (Str::contains($field['entity'], '.')) {
                    return $related_entry->{Str::afterLast($field['entity'], '.')};
                }

                if ($field['fields']) {
                    $result = [];
                    foreach ($field['fields'] as $subfield) {
                        $result[$subfield['name']] = $related_entry->{$subfield['name']};
                    }

                    return [$result];
                }

                return $related_entry;

                break;
            default:
                return $related_model->{$relation_method};
        }
    }

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
}
