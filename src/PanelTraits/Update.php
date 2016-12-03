<?php

namespace Backpack\CRUD\PanelTraits;

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
        $mutatedData = $this->mutateFieldData($data, 'update_fields');
        $item = $this->model->findOrFail($id);
        $updated = $item->update($this->compactFakeFields($mutatedData, 'update'));

        /*if ($updated) */$this->syncPivot($item, $mutatedData, 'update');

        return $item;
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  [integer] The id of the entry that is being edited.
     * @param int $id
     *
     * @return [array] The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id)
    {
        $fields = $this->update_fields;
        $entry = $this->getEntry($id);

        foreach ($fields as $k => $field) {
            // set the value
            if (! isset($fields[$k]['value'])) {
                if (isset($field['subfields'])) {
                    $fields[$k]['value'] = [];
                    foreach ($field['subfields'] as $key => $subfield) {
                        $fields[$k]['value'][] = $entry->{$subfield['name']};
                    }
                } else {
                    $fields[$k]['value'] = $entry->{$field['name']};
                }
            }
        }

        // always have a hidden input for the entry id
        $fields['id'] = [
            'name'  => $entry->getKeyName(),
            'value' => $entry->getKey(),
            'type'  => 'hidden',
        ];

        return $fields;
    }
}
