<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;

trait Relationships
{
    /**
     * Gets the relation data from the method in the model.
     *
     * @param array $field
     * @return array
     */
    public function inferFieldAttributesFromRelationship($field)
    {
        return $this->getFieldAttributesFromRelationship($field) ?? $field;
    }

    public function getRelationInstance($field)
    {
        $entity_array = explode('.', $field['entity']);
        $relation_model = $this->getRelationModel($field['entity']);
        $relation_model = new $relation_model();
        $related_method = Arr::last($entity_array);
        $field_in_database = false;

        //if the last part of entity is in fillable for related method, it means dev wants to edit some relation
        //specific attribute like account.address.name where Account HasOne Address
        if (in_array($related_method, $relation_model->getFillable())) {
            if (count($entity_array) > 1) {
                $related_method = $entity_array[(count($entity_array) - 2)];
                $relation_model = $this->getRelationModel($field['entity'], -2);
                $field_in_database = array_pop($entity_array);
            } else {
                $relation_model = $this->model;
            }
        } else {
            $relation_model = $this->getRelationModel($field['entity'], -1);
        }
        if (count($entity_array) == 1 && $field_in_database !== false) {
            if (method_exists($this->model, $related_method)) {
                return $this->model->{$related_method}();
            }
        }
        $relation_model = new $relation_model();

        return $relation_model->{$related_method}();
    }

    public function getFieldAttributesFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        $relation_model = $relation->getRelated();

        $relationship = [];
        $relationship['relation_type'] = Arr::last(explode('\\', get_class($relation)));
        $relationship['model'] = get_class($relation_model);
        switch ($relationship['relation_type']) {
            case 'BelongsTo':
                $relationship['foreign_key'] = $relation->getForeignKeyName();
                $relationship['multiple'] = false;
                $relationship['entity'] = $field['entity'].'.'.$relation->getOwnerKeyName();
                $relationship['name'] = $this->setRelationHtmlName($relation_model, $relationship['entity']);
                $relationship['type'] = $field['type'] ?? 'relationship';

        break;
            case 'HasOne':
                $relationship['multiple'] = false;
                $relationship['pivot'] = false;
                $relationship['name'] = $this->setRelationHtmlName($relation_model, $field['entity']);
                $relationship['type'] = $field['type'] ?? 'text';

            break;
            case 'BelongsToMany':
            case 'HasManyThrough':
            case 'HasMany':
                $relationship['pivot'] = true;
                $relationship['multiple'] = true;
                $relationship['type'] = $field['type'] ?? 'relationship';

            break;
        }

        return $relationship;
    }

    public function parseRelationFieldNamesFromHtml($fields)
    {
        foreach ($fields as &$field) {
            if (isset($field['relation_type'])) {
                $field['name'] = Arr::first(explode('.', $field['entity']));
            }
        }

        return $fields;
    }

    public function setRelationHtmlName($relation_model, $entity)
    {
        $entity_array = explode('.', $entity);
        $name_string = '';

        foreach ($entity_array as $key => $array_entity) {
            $name_string .= ($key == 0) ? $array_entity : '['.$array_entity.']';
        }

        return $name_string;
    }

    public function inferFieldTypeFromRelation($relation)
    {
    }

    /**
     * Based on relation type returns if relation allows multiple entities.
     *
     * @param string $relationType
     * @return bool
     */
    public function relationAllowsMultiple($relationType)
    {
        switch ($relationType) {
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
