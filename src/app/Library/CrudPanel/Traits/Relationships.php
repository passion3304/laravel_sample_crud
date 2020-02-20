<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Relationships
{
    /**
     * Provided a field name we try to figure if the name is a relation name or it's a database table field that points to any relation.
     *
     * @param string $fieldName
     * @return array|bool
     */
    public function getRelationFromFieldName($fieldName)
    { 
        // if the "name" is the same as a relationship on the model
        if (method_exists($this->model, $fieldName)) {
            return $this->getRelationData($fieldName);
        }

        return $this->checkIfFieldNameBelongsToAnyRelation($fieldName);
    }

    /**
     * If the field name is not a relationship method e.g: article_id, 
     * we try to find if this field has a relation defined.
     *
     * @param string $fieldName
     * @return array|bool
     */
    public function checkIfFieldNameBelongsToAnyRelation($fieldName)
    {
        $relations = $this->getAvailableRelationsInModel();

        if (empty($relations)) {
            return false;
        }

        if (in_array($fieldName, array_column($relations, 'name'))) {
            return array_filter($relations, function ($arr) use ($fieldName) {
                if (isset($arr['name'])) {
                    return $arr['name'] == $fieldName;
                }

                return false;
            })[0];
        }

        return false;
    }

    /**
     * Get the user defined methods in model that return any type of relation.
     *
     * @return array
     */
    public function getAvailableRelationsInModel()
    {
        //this is the currently supported, we should be able to add more in future.
        $eloquentRelationships = ['HasOne', 'BelongsTo', 'HasMany', 'BelongsToMany'];

        try {
            $reflect = new \ReflectionClass($this->model);
            $relations = [];

            foreach ($reflect->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->hasReturnType()) {
                    $returnType = $method->getReturnType();
                    if (in_array(class_basename($returnType->getName()), $eloquentRelationships)) {
                        $relations[] = $this->getRelationData($method);
                    }
                }
            }

            return $relations;
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Gets the relation data from the method in the model.
     *
     * @param ReflectionMethod $method
     * @return array
     */
    public function getRelationData($method)
    {
        if (!method_exists($this->model, $method)) {
            return false;
        }

        try {
            $method = (new \ReflectionClass($this->model))->getMethod($method);

            $relation = $method->invoke($this->model);

            if ($relation instanceof Relation) {
                $relationship['type'] = 'relationship';
                $relationship['entity'] = $method->getName();
                $relationship['relation_type'] = (new \ReflectionClass($relation))->getShortName();
                $relationship['multiple'] = $this->relationAllowsMultiple($relationship['relation_type']);
                $relationship['model'] = get_class($relation->getRelated());

                if ($relationship['relation_type'] == 'BelongsTo' || $relationship['relation_type'] == 'HasOne') {
                    $relationship['name'] = $relation->getForeignKeyName();
                }

                if ($relationship['relation_type'] == 'HasMany' || $relationship['relation_type'] == 'BelongsToMany') {
                    $relationship['pivot'] = true;
                }

                return $relationship;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
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
            case 'HasMany':
            case 'BelongsToMany':
            case 'HasManyThrough':
            case 'MorphMany':
                return true;

            default:
                return false;
        }
    }
}
