<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;

trait Relationships
{

    /**
     * From the field entity we get the relation instance.
     *
     * @param string $entity
     * @return void
     */
    public function getRelationInstance($entity)
    {
        $entity_array = explode('.', $entity);
        $relation_model = $this->getRelationModel($entity);
        $relation_model = new $relation_model();
        $related_method = Arr::last($entity_array);
        $field_in_database = false;

        //if the last part of entity is in fillable for related method, it means dev wants to edit some relation
        //specific attribute like account.address.name where Account HasOne Address
        if (in_array($related_method, $relation_model->getFillable())) {
            if (count($entity_array) > 1) {
                $related_method = $entity_array[(count($entity_array) - 2)];
                $relation_model = $this->getRelationModel($entity, -2);
                $field_in_database = array_pop($entity_array);
            } else {
                $relation_model = $this->model;
            }
        } else {
            $relation_model = $this->getRelationModel($entity, -1);
        }
        if (count($entity_array) == 1 && $field_in_database !== false) {
            if (method_exists($this->model, $related_method)) {
                return $this->model->{$related_method}();
            }
        }
        $relation_model = new $relation_model();

        return $relation_model->{$related_method}();
    }
    /**
     * Grabs an relation instance and returns the class name of the related model.
     *
     * @param array $field
     * @return string
     */
    public function inferFieldModelFromRelationship($field) {
        $relation = $this->getRelationInstance($field);

        return get_class($relation->getRelated());
    }

    /**
     * Return the relation type from a given field: BelongsTo, HasOne ... etc
     *
     * @param array $field
     * @return string
     */
    public function inferRelationTypeFromRelationship($field) {
        $relation = $this->getRelationInstance($field);

        return Arr::last(explode('\\', get_class($relation)));
    }

    /**
     * Parse the field name back to the related entity after the form is submited.
     * Its called in getAllFieldNames()
     *
     * @param array $fields
     * @return array
     */
    public function parseRelationFieldNamesFromHtml($fields)
    {
        foreach ($fields as &$field) {
            if (isset($field['relation_type'])) {
                $field['name'] = Arr::first(explode('.', $field['entity']));
            }
        }

        return $fields;
    }

    /**
     * Based on relation type returns the default field type.
     *
     * @param string $relation_type
     * @return bool
     */
    public function inferFieldTypeFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasMany':
            case 'HasManyThrough':
            case 'MorphMany':
            case 'MorphToMany':
            case 'BelongsTo':
                return 'relationship';

            default:
                return 'text';
        }
    }

    /**
     * Based on relation type returns if relation allows multiple entities.
     *
     * @param string $relation_type
     * @return bool
     */
    public function guessIfFieldHasMultipleFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasMany':
            case 'HasManyThrough':
            case 'HasOneOrMany':
            case 'MorphMany':
            case 'MorphOneOrMany':
            case 'MorphToMany':
                return true;

            default:
                return false;
        }
    }
    /**
     * Based on relation type returns if relation has a pivot table.
     *
     * @param string $relation_type
     * @return bool
     */
    public function guessIfFieldHasPivotFromRelationType($relation_type) {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasMany':
            case 'HasManyThrough':
            case 'HasOneOrMany':
            case 'MorphMany':
            case 'MorphOneOrMany':
            case 'MorphToMany':
                return true;

            default:
                return false;
        }
    }
}
