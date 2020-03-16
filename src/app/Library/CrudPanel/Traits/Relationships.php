<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;

trait Relationships
{
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
     * Only returns methods that have their return type explicitly specified. For example:
     * public function article() : BelongsTo { return $this->belongsTo(...); }
     * public function tags() : HasMany {}.
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
                        $relations[] = $this->inferFieldAttributesFromRelationship($method);
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
    public function inferFieldAttributesFromRelationship($method)
    {
        // get the parent of the last relation if using dot notation
        // eg: user.account.address -> Return model for account and the relation address in account model.
        $relationModel = $this->getRelationModel($method, -1);
        $relatedMethod = Arr::last(explode('.', $method));

        if ($relationModel != get_class($this->model)) {
            $relationModel = new $relationModel();
            if (method_exists($relationModel, $relatedMethod)) {
                return $this->getFieldAttributesFromRelationship($relationModel, $relatedMethod);
            }
        }
        if (method_exists($this->model, $relatedMethod)) {
            return $this->getFieldAttributesFromRelationship($this->model, $relatedMethod);
        }

        return false;
    }

    public function getFieldAttributesFromRelationship($model, $method)
    {
        try {
            $method = (new \ReflectionClass($model))->getMethod($method);

            $relation = $method->invoke($model);

            if ($relation instanceof Relation) {
                $relationship['type'] = 'relationship';
                $relationship['entity'] = $method->getName();
                $relationship['relation_type'] = (new \ReflectionClass($relation))->getShortName();
                $relationship['multiple'] = $this->relationAllowsMultiple($relationship['relation_type']);
                $relationship['model'] = get_class($relation->getRelated());

                if ($relationship['relation_type'] == 'BelongsTo' || $relationship['relation_type'] == 'HasOne') {
                    $relationship['name'] = $relation->getForeignKeyName();
                }

                if ($relationship['relation_type'] == 'hasManyThrough' || $relationship['relation_type'] == 'BelongsToMany' || $relationship['relation_type'] == 'morphMany') {
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
