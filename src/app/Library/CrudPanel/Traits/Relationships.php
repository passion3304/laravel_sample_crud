<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Relationships
{
    /**
     * From the field entity we get the relation instance.
     *
     * @param  array  $entity
     * @return object
     */
    public function getRelationInstance($field)
    {
        $entity = $this->getOnlyRelationEntity($field);
        $possible_method = Str::before($entity, '.');
        $model = isset($field['baseModel']) ? app($field['baseModel']) : $this->model;

        if (method_exists($model, $possible_method)) {
            $parts = explode('.', $entity);
            // here we are going to iterate through all relation parts to check
            foreach ($parts as $i => $part) {
                $relation = $model->$part();
                $model = $relation->getRelated();
            }

            return $relation;
        }

        abort(500, 'Did not find a matching relationship. Are you sure that '.get_class($model)." has the {$field['entity']}() relationship on it?");
    }

    /**
     * Grabs an relation instance and returns the class name of the related model.
     *
     * @param  array  $field
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
     * @param  array  $field
     * @return string
     */
    public function inferRelationTypeFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        return Arr::last(explode('\\', get_class($relation)));
    }

    public function getOnlyRelationEntity($field)
    {
        $model = $this->getRelationModel($field['entity'], -1);
        $lastSegmentAfterDot = Str::of($field['entity'])->afterLast('.');

        if (! method_exists($model, $lastSegmentAfterDot)) {
            return (string) Str::of($field['entity'])->beforeLast('.');
        }

        return $field['entity'];
    }

    /**
     * Get the fields for relationships, according to the relation type. It looks only for direct
     * relations - it will NOT look through relationships of relationships.
     *
     * @param  string|array  $relation_types  Eloquent relation class or array of Eloquent relation classes. Eg: BelongsTo
     * @param  bool  $nested  Should nested fields be included
     * @return array The fields with corresponding relation types.
     */
    public function getFieldsWithRelationType($relation_types, $nested = false): array
    {
        $relation_types = (array) $relation_types;

        return collect($this->getCleanStateFields())
            ->whereIn('relation_type', $relation_types)
            ->filter(function ($item) use ($nested) {
                if ($nested) {
                    return true;
                }

                return Str::contains($item['entity'], '.') ? false : true;
            })
            ->toArray();
    }

    /**
     * Parse the field name back to the related entity after the form is submited.
     * Its called in getAllFieldNames().
     *
     * @param  array  $fields
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
     * Gets the relation fields that DON'T contain the provided relations.
     *
     * @param  string|array  $relations  - the relations to exclude
     * @param  bool  $include_nested  - if the nested relations of the same relations should be excluded too.
     */
    private function getRelationFieldsWithoutRelationType($relations, $include_nested = false)
    {
        if (! is_array($relations)) {
            $relations = [$relations];
        }

        $fields = $this->getRelationFields();

        foreach ($relations as $relation) {
            $fields = array_filter($fields, function ($field) use ($relation, $include_nested) {
                if ($include_nested) {
                    return $field['relation_type'] !== $relation || ($field['relation_type'] === $relation && Str::contains($field['name'], '.'));
                }

                return $field['relation_type'] !== $relation;
            });
        }

        return $fields;
    }

    /**
     * Changes the BelongsTo names in the input from request to allways
     * have the foreign_key instead of the relation name.
     * It only changes main relations not nested.
     *
     * eg: user -> user_id
     */
    private function changeBelongsToNamesFromRelationshipToForeignKey($input)
    {
        $belongs_to_fields = $this->getFieldsWithRelationType('BelongsTo');
        foreach ($belongs_to_fields as $relation_field) {
            $name_for_sub = $this->getOverwrittenNameForBelongsTo($relation_field);

            if (Arr::has($input, $relation_field['name']) && $relation_field['name'] !== $name_for_sub) {
                Arr::set($input, $name_for_sub, Arr::get($input, $relation_field['name']));
                Arr::forget($input, $relation_field['name']);
            }
        }

        return $input;
    }

    /**
     * Based on relation type returns if relation allows multiple entities.
     *
     * @param  string  $relation_type
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
     * @param  string  $relation_type
     * @return bool
     */
    public function guessIfFieldHasPivotFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasManyThrough':
            case 'MorphToMany':
                return true;
            break;
            default:
                return false;
        }
    }

    /**
     * Get all relation fields that don't have pivot set.
     *
     * @return array The fields with model key set.
     */
    public function getRelationFieldsWithoutPivot()
    {
        $all_relation_fields = $this->getRelationFields();

        return Arr::where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && ! $value['pivot'];
        });
    }

    /**
     * Get all fields with n-n relation set (pivot table is true).
     *
     * @return array The fields with n-n relationships.
     */
    public function getRelationFieldsWithPivot()
    {
        $all_relation_fields = $this->getRelationFields();

        return Arr::where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && $value['pivot'];
        });
    }

    /**
     * Return the name for the BelongTo relation making sure it always has the foreign_key instead of relationName
     * eg: user - user_id OR address.country - address.country_id.
     *
     * @param  array  $field  The field we want to get the name from
     */
    private function getOverwrittenNameForBelongsTo($field)
    {
        $relation = $this->getRelationInstance($field);
        if (Str::contains($field['name'], '.')) {
            return Str::beforeLast($field['name'], '.').'.'.$relation->getForeignKeyName();
        }

        return $relation->getForeignKeyName();
    }
}
