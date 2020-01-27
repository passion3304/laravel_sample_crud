<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Relationships
{
    //this is the currently supported, we should be able to add more in future.
    protected $eloquentRelationships = ['HasOne', 'BelongsTo', 'HasMany', 'BelongsToMany'];

    /**
     * Check if the given method exists in current crud model.
     *
     * @param string $methodName
     * @return ReflectionMethod|boolean
     */
    public function checkIfMethodExistsInModel($methodName)
    {
        try {
            $method = (new \ReflectionClass($this->model))->getMethod($methodName);
            return $method;
        } catch (Exception $e) {
            return false;
        }
    }
    /**
     * Get the relation from field name.
     *
     * @param string $fieldName
     * @return array|boolean
     */
    public function getRelationFromFieldName($fieldName)
    {
        $method = $this->checkIfMethodExistsInModel($fieldName);
        if ($method) {
            return $this->getRelationData($method);
        } else {
            return $this->checkIfFieldNameBelongsToAnyRelation($fieldName);
        }
    }

    /**
     * If the field name is not a relationship method e.g: article_id, we try to find if this field has a relation defined.
     *
     * @param string $fieldName
     * @return array|boolean
     */
    public function checkIfFieldNameBelongsToAnyRelation($fieldName)
    {
        $relations = $this->getAvailableRelationsInModel();
        if(!empty($relations)) {
            if(in_array($fieldName, array_column($relations,'name'))) {
                return array_filter($relations, function($arr) use ($fieldName) {
                    if (isset($arr['name'])) {
                        return $arr['name'] == $fieldName;
                    }
                    return false;
                })[0];
            }
            return false;
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
        $reflect = new \ReflectionClass($this->model);
        $relations = array();
        foreach ($reflect->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {

            if ($method->hasReturnType()) {
                $returnType = $method->getReturnType();
                    if (in_array(class_basename($returnType->getName()), $this->eloquentRelationships)) {
                    $relations[] = $this->getRelationData($method);
            }
        }
            }
            return $relations;
    }

    /**
     * Gets the relation data from the method in the model.
     *
     * @param ReflectionMethod $method
     * @return array
     */
    public function getRelationData($method)
    {
        try {
            $relation = $method->invoke($this->model);

            if ($relation instanceof Relation) {
                $relationship['entity'] = $method->getName();
                $relationship['relation_type'] = (new \ReflectionClass($relation))->getShortName();
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
            break;
            default:
            return false;
        }
    }
}
