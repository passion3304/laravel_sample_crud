<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;

trait Relationships
{
    /**
     * From the field entity we get the relation instance.
     *
     * @param array $entity
     * @return void
     */
    public function getRelationInstance($field)
    {
        $entity = $this->getOnlyRelationEntity($field);
        $entity_array = explode('.', $entity);
        $relation_model = $this->getRelationModel($entity);

        $related_method = Arr::last($entity_array);
        if (count(explode('.', $entity)) == count(explode('.', $field['entity']))) {
            $relation_model = $this->getRelationModel($entity, -1);
        }
        $relation_model = new $relation_model();

        //if counts are diferent means that last element of entity is the field in relation.
        if (count(explode('.', $entity)) != count(explode('.', $field['entity']))) {
            if (in_array($related_method, $relation_model->getFillable())) {
                if (count($entity_array) > 1) {
                    $related_method = $entity_array[(count($entity_array) - 2)];
                    $relation_model = $this->getRelationModel($entity, -2);
                } else {
                    $relation_model = $this->model;
                }
            }
        }
        if (count($entity_array) == 1) {
            if (method_exists($this->model, $related_method)) {
                return $this->model->{$related_method}();
            }
        }

        return $relation_model->{$related_method}();
    }

    /**
     * Grabs an relation instance and returns the class name of the related model.
     *
     * @param array $field
     * @return string
     */
    public function inferFieldModelFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        return get_class($relation->getRelated());
    }

    /**
     * Return the relation type from a given field: BelongsTo, HasOne ... etc.
     *
     * @param array $field
     * @return string
     */
    public function inferRelationTypeFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        return Arr::last(explode('\\', get_class($relation)));
    }

    /**
     * Parse the field name back to the related entity after the form is submited.
     * Its called in getAllFieldNames().
     *
     * @param array $fields
     * @return array
     */
    public function parseRelationFieldNamesFromHtml($fields)
    {
        foreach ($fields as &$field) {
            //we only want to parse fields that has a relation type and their name contains [ ] used in html.
            if (isset($field['relation_type']) && preg_match('/[\[\]]/', $field['name']) !== 0) {
                $chunks = explode('[', $field['name']);

                foreach ($chunks as &$chunk) {
                    if (strpos($chunk, ']')) {
                        $chunk = str_replace(']', '', $chunk);
                    }
                }
                $field['name'] = implode('.', $chunks);
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
    public function inferFieldTypeFromFieldRelation($field)
    {
        switch ($field['relation_type']) {
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
    public function guessIfFieldHasPivotFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasManyThrough':
            case 'MorphMany':
            case 'MorphOneOrMany':
            case 'MorphToMany':
                return true;
            default:
                return false;
        }
    }
}
