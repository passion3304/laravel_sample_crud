<?php

namespace Backpack\CRUD\PanelTraits;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

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
     * @param  [Int] The entity's id
     * @param  [Request] All inputs to be updated.
     *
     * @return [Eloquent Collection]
     */
    public function update($id, $data)
    {
        $data = $this->decodeJsonCastedAttributes($data, 'update', $id);
        $data = $this->compactFakeFields($data, 'update', $id);

        $item = $this->model->findOrFail($id);

        $this->createRelations($item, $data, 'update');

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = array_pluck($this->getRelationFieldsWithPivot('update'), 'name');
        $data = array_except($data, $nn_relationships);
        $updated = $item->update($data);

        return $item;
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param int $id The id of the entry that is being edited.
     *
     * @return array The fields with attributes, fake attributes and values
     */
    public function getUpdateFields($id)
    {
        $fields = $this->update_fields;
        $entry = $this->getEntry($id);

        foreach ($fields as &$field) {
            // set the value
            if (! isset($field['value'])) {
                if (isset($field['subfields'])) {
                    $field['value'] = [];
                    foreach ($field['subfields'] as $subfield) {
                        $field['value'][] = $entry->{$subfield['name']};
                    }
                } else {
                    $field['value'] = $this->getEntryValue($entry, $field);
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

    private function getEntryValue($entry, $field)
    {
        if (isset($field['entity'])) {
            $relationArray = explode('.', $field['entity']);
            $relatedModel = array_reduce(array_splice($relationArray, 0, -1), function ($obj, $method) {
                return $obj->{$method} ? $obj->{$method} : $obj;
            }, $entry);

            if ($relatedModel->{end($relationArray)} && $relatedModel->{end($relationArray)}() instanceof HasOneOrMany) {
                return $relatedModel->{end($relationArray)}->{$field['name']};
            } else {
                return $relatedModel->{$field['name']};
            }
        }

        return $entry->{$field['name']};
    }
}
